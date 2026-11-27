<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Shipment;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ShipmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Shipment::with(['sender', 'traveler'])->latest();

        if ($request->filled('from_country')) {
            $query->where('from_country', $request->from_country);
        }

        if ($request->filled('to_country')) {
            $query->where('to_country', $request->to_country);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $shipments = $query->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $shipments,
        ]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'trip_id' => 'sometimes|nullable|integer',
            'driver_id' => 'sometimes|nullable|integer',
            'from_country' => 'required|string|size:2',
            'from_city' => 'required|string',
            'to_country' => 'required|string|size:2',
            'to_city' => 'required|string',
            'border_name' => 'required|string',
            'recipient_name' => 'required|string',
            'recipient_phone' => 'required|string',
            'category' => 'sometimes|string',
            'details' => 'required|string',
            'reward_fee' => 'sometimes|numeric|min:0',
        ]);

        // Generate unique tracking code e.g. TW-SAJO-8921
        $code = 'TW-' . strtoupper($validated['from_country'] . $validated['to_country']) . '-' . rand(1000, 9999);

        $driverId = $validated['driver_id'] ?? null;
        $tripId = $validated['trip_id'] ?? null;

        $validated['sender_id'] = $user->id;
        $validated['traveler_id'] = $driverId;
        $validated['tracking_code'] = $code;
        $validated['status'] = 'pending';

        $shipment = Shipment::create($validated);
        $shipment->load('sender');

        // If requested for a specific driver / captain, send notification to him
        if ($driverId && $driverId !== $user->id) {
            Notification::create([
                'user_id' => $driverId,
                'title' => 'طلب توصيل طرد / أمانة جديد 📦',
                'body' => "طلب منك العميل {$user->name} توصيل ({$shipment->details}) من {$shipment->from_city} إلى {$shipment->to_city}.",
                'type' => 'shipment_request',
                'data' => [
                    'shipment_id' => $shipment->id,
                    'trip_id' => $tripId,
                    'tracking_code' => $shipment->tracking_code,
                    'sender_name' => $user->name,
                    'sender_phone' => $user->phone ?? '',
                    'sender_id' => $user->id,
                    'details' => $shipment->details,
                    'recipient_phone' => $shipment->recipient_phone,
                    'from_city' => $shipment->from_city,
                    'to_city' => $shipment->to_city,
                ],
                'is_read' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل طلب الشحن والأمانة بنجاح!',
            'tracking_code' => $code,
            'data' => $shipment,
        ], 201);
    }

    public function track($code)
    {
        $shipment = Shipment::with(['sender', 'traveler'])
            ->where('tracking_code', strtoupper(trim($code)))
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $shipment,
        ]);
    }

    public function assign(Request $request, $id)
    {
        $user = $request->user();
        $shipment = Shipment::with('sender')->findOrFail($id);

        if ($shipment->sender_id === $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'لا يمكنك استلام شحنتك بنفسك كمسافر',
            ], 422);
        }

        $shipment->update([
            'traveler_id' => $user->id,
            'status' => 'assigned',
        ]);

        // Notify Sender
        Notification::create([
            'user_id' => $shipment->sender_id,
            'title' => 'تم قبول توصيل أمانتك 📦',
            'body' => "وافق صاحب السيارة {$user->name} على توصيل طلبك ({$shipment->details}) من {$shipment->from_city} إلى {$shipment->to_city}.",
            'type' => 'shipment_accepted',
            'data' => [
                'shipment_id' => $shipment->id,
                'tracking_code' => $shipment->tracking_code,
                'captain_name' => $user->name,
                'captain_phone' => $user->phone ?? '',
                'driver_id' => $user->id,
                'details' => $shipment->details,
                'from_city' => $shipment->from_city,
                'to_city' => $shipment->to_city,
            ],
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم قبول توصيل الأمانة بنجاح، وتم إشعار العميل!',
            'data' => $shipment,
        ]);
    }

    public function reject(Request $request, $id)
    {
        $user = $request->user();
        $shipment = Shipment::with('sender')->findOrFail($id);

        $shipment->update([
            'status' => 'cancelled',
        ]);

        // Notify Sender
        Notification::create([
            'user_id' => $shipment->sender_id,
            'title' => 'اعتذار عن توصيل الطلب ❌',
            'body' => "نعتذر، لم يتمكن صاحب السيارة {$user->name} من قبول توصيل طلبك ({$shipment->details}). يمكنك اختيار صاحب سيارة آخر.",
            'type' => 'shipment_rejected',
            'data' => [
                'shipment_id' => $shipment->id,
                'captain_name' => $user->name,
            ],
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم الاعتذار عن توصيل الطلب بنجاح',
        ]);
    }
}
