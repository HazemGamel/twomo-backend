<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatMessage;
use App\Models\Notification;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function getMessages(Request $request, $tripId)
    {
        $user = $request->user();
        $otherUserId = $request->query('user_id');

        $query = ChatMessage::with('sender')
            ->where('trip_id', $tripId);

        if ($otherUserId) {
            $query->where(function ($q) use ($user, $otherUserId) {
                $q->where(function ($sub) use ($user, $otherUserId) {
                    $sub->where('sender_id', $user->id)->where('receiver_id', $otherUserId);
                })->orWhere(function ($sub) use ($user, $otherUserId) {
                    $sub->where('sender_id', $otherUserId)->where('receiver_id', $user->id);
                });
            });
        } else {
            $query->where(function ($q) use ($user) {
                $q->where('sender_id', $user->id)
                  ->orWhere('receiver_id', $user->id);
            });
        }

        $messages = $query->oldest()->get();

        // Mark incoming messages as read
        $markQuery = ChatMessage::where('trip_id', $tripId)
            ->where('receiver_id', $user->id)
            ->where('is_read', false);
        if ($otherUserId) {
            $markQuery->where('sender_id', $otherUserId);
        }
        $markQuery->update(['is_read' => true]);

        // Also mark chat notifications as read
        Notification::where('user_id', $user->id)
            ->where('type', 'chat_message')
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    public function sendMessage(Request $request, $tripId)
    {
        $user = $request->user();
        $trip = Trip::findOrFail($tripId);

        $validated = $request->validate([
            'receiver_id' => 'nullable|integer|exists:users,id',
            'message' => 'required|string|max:1000',
        ]);

        $receiverId = $validated['receiver_id'] ?? null;

        if (!$receiverId) {
            if ($user->id === $trip->driver_id) {
                // If driver and receiver not specified, find passenger who last messaged or first booking
                $lastMsg = ChatMessage::where('trip_id', $trip->id)
                    ->where('receiver_id', $user->id)
                    ->latest()
                    ->first();
                $receiverId = $lastMsg ? $lastMsg->sender_id : $trip->bookings()->first()?->passenger_id;
            } else {
                $receiverId = $trip->driver_id;
            }
        }

        if (!$receiverId) {
            return response()->json([
                'success' => false,
                'message' => 'يرجى تحديد المستلم',
            ], 422);
        }

        $message = ChatMessage::create([
            'trip_id' => $trip->id,
            'sender_id' => $user->id,
            'receiver_id' => $receiverId,
            'message' => $validated['message'],
            'is_read' => false,
        ]);

        // Notify Receiver
        Notification::create([
            'user_id' => $receiverId,
            'title' => "رسالة جديدة من {$user->name} 💬",
            'body' => $validated['message'],
            'type' => 'chat_message',
            'data' => [
                'trip_id' => $trip->id,
                'sender_id' => $user->id,
                'sender_name' => $user->name,
            ],
            'is_read' => false,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال الرسالة بنجاح',
            'data' => $message->load('sender'),
        ], 201);
    }

    public function getConversations(Request $request)
    {
        $user = $request->user();

        // Get all trips where user is driver, has a booking, OR has exchanged chat messages
        $driverTripIds = Trip::where('driver_id', $user->id)->pluck('id');
        $passengerTripIds = \App\Models\TripBooking::where('passenger_id', $user->id)->pluck('trip_id');
        $chatTripIds = ChatMessage::where('sender_id', $user->id)
            ->orWhere('receiver_id', $user->id)
            ->pluck('trip_id');

        $allTripIds = $driverTripIds
            ->merge($passengerTripIds)
            ->merge($chatTripIds)
            ->unique();

        $trips = Trip::with(['driver', 'bookings.passenger'])
            ->whereIn('id', $allTripIds)
            ->latest()
            ->get();

        $conversations = [];
        foreach ($trips as $trip) {
            $isDriver = $trip->driver_id === $user->id;

            if ($isDriver) {
                // For driver: list distinct passengers who booked or exchanged messages
                $passengerIdsFromBookings = $trip->bookings->pluck('passenger_id')->unique();
                $passengerIdsFromChat = ChatMessage::where('trip_id', $trip->id)
                    ->where('sender_id', '!=', $user->id)
                    ->pluck('sender_id')
                    ->unique();
                $allPassengerIds = $passengerIdsFromBookings->merge($passengerIdsFromChat)->unique();

                if ($allPassengerIds->isEmpty()) {
                    $allPassengerIds = collect([null]);
                }

                foreach ($allPassengerIds as $pId) {
                    $passengerUser = $pId ? User::find($pId) : null;

                    $lastMsgQuery = ChatMessage::where('trip_id', $trip->id);
                    if ($pId) {
                        $lastMsgQuery->where(function ($q) use ($user, $pId) {
                            $q->where(function ($sub) use ($user, $pId) {
                                $sub->where('sender_id', $user->id)->where('receiver_id', $pId);
                            })->orWhere(function ($sub) use ($user, $pId) {
                                $sub->where('sender_id', $pId)->where('receiver_id', $user->id);
                            });
                        });
                    }
                    $lastMessage = $lastMsgQuery->latest()->first();

                    $unreadQuery = ChatMessage::where('trip_id', $trip->id)
                        ->where('receiver_id', $user->id)
                        ->where('is_read', false);
                    if ($pId) {
                        $unreadQuery->where('sender_id', $pId);
                    }
                    $unreadCount = $unreadQuery->count();

                    $conversations[] = [
                        'trip_id' => $trip->id,
                        'is_driver' => true,
                        'other_user_id' => $pId,
                        'other_name' => $passengerUser ? $passengerUser->name : 'مسافر تومو',
                        'other_phone' => $passengerUser ? $passengerUser->phone : '+966 50 000 0000',
                        'route' => "{$trip->from_city} ➔ {$trip->to_city}",
                        'car_model' => $trip->car_model ?? 'مركبة خاصة',
                        'last_message' => $lastMessage ? $lastMessage->message : 'تم تأكيد حجز المقعد، اضغط لبدء التنسيق',
                        'last_time' => $lastMessage ? $lastMessage->created_at->format('H:i') : 'الآن',
                        'unread_count' => $unreadCount,
                    ];
                }
            } else {
                // For passenger: chat with driver
                $lastMessage = ChatMessage::where('trip_id', $trip->id)
                    ->where(function ($q) use ($user) {
                        $q->where('sender_id', $user->id)->orWhere('receiver_id', $user->id);
                    })
                    ->latest()
                    ->first();

                $unreadCount = ChatMessage::where('trip_id', $trip->id)
                    ->where('receiver_id', $user->id)
                    ->where('is_read', false)
                    ->count();

                $conversations[] = [
                    'trip_id' => $trip->id,
                    'is_driver' => false,
                    'other_user_id' => $trip->driver_id,
                    'other_name' => $trip->driver?->name ?? 'صاحب السيارة',
                    'other_phone' => $trip->driver?->phone ?? '+966 50 112 3344',
                    'route' => "{$trip->from_city} ➔ {$trip->to_city}",
                    'car_model' => $trip->car_model ?? 'مركبة خاصة',
                    'last_message' => $lastMessage ? $lastMessage->message : 'تم تأكيد حجز مقعدك، تواصل مع صاحب السيارة للتنسيق',
                    'last_time' => $lastMessage ? $lastMessage->created_at->format('H:i') : 'الآن',
                    'unread_count' => $unreadCount,
                ];
            }
        }

        return response()->json([
            'success' => true,
            'data' => $conversations,
        ]);
    }
}
