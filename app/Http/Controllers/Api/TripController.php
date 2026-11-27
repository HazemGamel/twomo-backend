<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Trip;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;

class TripController extends Controller
{
    public function index(Request $request)
    {
        $query = Trip::with('driver')->latest();

        // Exclude the current user's own trips (can't book your own trip)
        $user = $request->user();
        if ($user) {
            $query->where('driver_id', '!=', $user->id);
        }

        if ($request->filled('from_city')) {
            $query->where('from_city', $request->from_city);
        }

        if ($request->filled('to_city')) {
            $query->where('to_city', $request->to_city);
        }

        if ($request->filled('border_name')) {
            $query->where('border_name', 'like', '%' . $request->border_name . '%');
        }

        if ($request->filled('date')) {
            $query->whereDate('departure_date', $request->date);
        }

        // Only show scheduled or active trips by default
        if (!$request->filled('status')) {
            $query->whereIn('status', ['scheduled', 'in_progress']);
        } else {
            $query->where('status', $request->status);
        }

        $trips = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $trips,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'from_country' => 'required|string|size:2',
            'from_city' => 'required|string',
            'to_country' => 'required|string|size:2',
            'to_city' => 'required|string',
            'border_name' => 'required|string',
            'departure_date' => 'required|date',
            'available_seats' => 'required|integer|min:1|max:8',
            'total_seats' => 'sometimes|integer|min:1|max:8',
            'price_per_seat' => 'sometimes|numeric|min:0',
            'notes' => 'nullable|string',
            'car_model' => 'sometimes|nullable|string',
            'car_plate' => 'sometimes|nullable|string',
        ]);

        // If driver provided car details, update their profile
        if (!empty($validated['car_model']) || !empty($validated['car_plate'])) {
            $user->update([
                'car_model' => $validated['car_model'] ?? $user->car_model,
                'car_plate' => $validated['car_plate'] ?? $user->car_plate,
                'role' => 'owner',
            ]);
        }

        $validated['driver_id'] = $user->id;
        $validated['total_seats'] = $validated['total_seats'] ?? $validated['available_seats'];
        $validated['price_per_seat'] = $validated['price_per_seat'] ?? 50.00;
        $validated['status'] = 'scheduled';

        $trip = Trip::create($validated);
        $trip->load('driver');

        // Broadcast / Generate notification for registered travelers
        $travelers = User::where('id', '!=', $user->id)->get();
        foreach ($travelers as $traveler) {
            Notification::create([
                'user_id' => $traveler->id,
                'title' => 'رحلة جديدة معلنة 🚗',
                'body' => "أعلن صاحب السيارة {$user->name} عن رحلة من {$trip->from_city} إلى {$trip->to_city} عبر {$trip->border_name} ({$trip->available_seats} مقاعد متاحة).",
                'type' => 'new_trip',
                'data' => [
                    'trip_id' => $trip->id,
                    'from_city' => $trip->from_city,
                    'to_city' => $trip->to_city,
                    'driver_name' => $user->name,
                ],
                'is_read' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم نشر الرحلة بنجاح وتم إشعار المسافرين المسجلين!',
            'data' => $trip,
        ], 201);
    }

    public function show($id)
    {
        $trip = Trip::with(['driver', 'bookings.passenger', 'messages.sender'])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $trip,
        ]);
    }

    public function myTrips(Request $request)
    {
        $user = $request->user();
        $trips = Trip::with(['bookings.passenger'])
            ->where('driver_id', $user->id)
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $trips,
        ]);
    }
}
