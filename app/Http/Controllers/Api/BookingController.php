<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\TripBooking;
use App\Models\Notification;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function bookSeat(Request $request, $tripId)
    {
        $user = $request->user();
        $trip = Trip::with('driver')->findOrFail($tripId);

        if ($trip->driver_id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك حجز مقعد في رحلتك الخاصة كصاحب سيارة',
            ], 422);
        }

        $validated = $request->validate([
            'seats_booked' => 'sometimes|integer|min:1|max:4',
            'pickup_location' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $seatsBooked = $validated['seats_booked'] ?? 1;

        if ($trip->available_seats < $seatsBooked) {
            return response()->json([
                'success' => false,
                'message' => 'عذراً، عدد المقاعد الشاغرة غير كافٍ',
            ], 422);
        }

        // Check if user already booked this trip
        $existingBooking = TripBooking::where('trip_id', $trip->id)
            ->where('passenger_id', $user->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->first();

        if ($existingBooking) {
            return response()->json([
                'success' => false,
                'message' => 'لديك طلب حجز قائم بالفعل لهذه الرحلة',
            ], 422);
        }

        $booking = TripBooking::create([
            'trip_id' => $trip->id,
            'passenger_id' => $user->id,
            'seats_booked' => $seatsBooked,
            'pickup_location' => $validated['pickup_location'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending', // Pending captain approval
        ]);

        // Decrement available seats (reserved while pending)
        $trip->decrement('available_seats', $seatsBooked);

        // Notify the Captain!
        Notification::create([
            'user_id' => $trip->driver_id,
            'title' => 'طلب حجز مقعد جديد 🚗',
            'body' => "طلب الراكب {$user->name} حجز عدد {$seatsBooked} مقعد في رحلتك المتجهة من {$trip->from_city} إلى {$trip->to_city}.",
            'type' => 'booking_request',
            'data' => [
                'trip_id' => $trip->id,
                'booking_id' => $booking->id,
                'passenger_name' => $user->name,
                'passenger_phone' => $user->phone ?? '+966 50 000 0000',
                'seats' => $seatsBooked,
                'from_city' => $trip->from_city,
                'to_city' => $trip->to_city,
                'pickup_location' => $validated['pickup_location'] ?? 'نقطة التجمع',
                'notes' => $validated['notes'] ?? '',
            ],
            'is_read' => false,
        ]);

        // Also notify the Passenger!
        Notification::create([
            'user_id' => $user->id,
            'title' => 'تم إرسال طلب الحجز ⏳',
            'body' => "طلب حجز {$seatsBooked} مقعد مع صاحب السيارة {$trip->driver->name} قيد انتظار الموافقة.",
            'type' => 'booking_pending',
            'data' => [
                'trip_id' => $trip->id,
                'booking_id' => $booking->id,
                'captain_name' => $trip->driver->name,
            ],
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال طلب الحجز للصاحب سيارة بنجاح وفي انتظار الموافقة!',
            'data' => $booking->load(['trip.driver', 'passenger']),
        ], 201);
    }

    public function updateStatus(Request $request, $bookingId)
    {
        $user = $request->user();
        $booking = TripBooking::with(['trip.driver', 'passenger'])->findOrFail($bookingId);

        // Only the captain can approve or reject
        if ($booking->trip->driver_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'غير مصرح لك بتحديث حالة هذا الحجز',
            ], 403);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:confirmed,rejected,cancelled',
        ]);

        $oldStatus = $booking->status;
        $newStatus = $validated['status'];

        $booking->update(['status' => $newStatus]);

        if ($newStatus === 'rejected' && in_array($oldStatus, ['confirmed', 'pending'])) {
            $booking->trip->increment('available_seats', $booking->seats_booked);
        }

        // Send Notification to Passenger
        Notification::create([
            'user_id' => $booking->passenger_id,
            'title' => $newStatus === 'confirmed' ? 'تم قبول حجزك بنجاح! 🎉' : 'اعتذار عن طلب الحجز ❌',
            'body' => $newStatus === 'confirmed'
                ? "وافق صاحب السيارة {$user->name} على طلب حجز مقعدك لرحلة {$booking->trip->to_city}. اضغط هنا للاطلاع على تفاصيل الرحلة والتواصل مع صاحب السيارة."
                : "نعتذر، لم يتمكن صاحب السيارة {$user->name} من قبول حجزك لرحلة {$booking->trip->to_city}. يمكنك البحث عن رحلة أخرى.",
            'type' => $newStatus === 'confirmed' ? 'booking_accepted' : 'booking_rejected',
            'data' => [
                'trip_id' => $booking->trip_id,
                'booking_id' => $booking->id,
                'driver_name' => $user->name,
                'driver_phone' => $user->phone ?? '+966 50 112 3344',
                'car_model' => $booking->trip->car_model ?? 'مركبة خاصة',
                'car_plate' => $booking->trip->car_plate ?? '',
                'from_city' => $booking->trip->from_city,
                'to_city' => $booking->trip->to_city,
                'border_name' => $booking->trip->border_name,
                'date' => $booking->trip->departure_date ? substr($booking->trip->departure_date, 0, 10) : 'اليوم',
                'price' => $booking->trip->price_per_seat,
            ],
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة الحجز بنجاح',
            'data' => $booking,
        ]);
    }

    public function driverBookings(Request $request)
    {
        $user = $request->user();
        $bookings = TripBooking::with(['trip', 'passenger'])
            ->whereHas('trip', function ($q) use ($user) {
                $q->where('driver_id', $user->id);
            })
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bookings,
        ]);
    }

    public function myBookings(Request $request)
    {
        $user = $request->user();
        $bookings = TripBooking::with(['trip.driver'])
            ->where('passenger_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bookings,
        ]);
    }
}
