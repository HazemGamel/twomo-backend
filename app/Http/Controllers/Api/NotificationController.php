<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $notifications = Notification::where('user_id', $user->id)
            ->latest()
            ->paginate(30);

        return response()->json([
            'success' => true,
            'data' => $notifications,
        ]);
    }

    public function unreadCount(Request $request)
    {
        $user = $request->user();
        $count = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return response()->json([
            'success' => true,
            'unread_count' => $count,
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        $user = $request->user();
        $notification = Notification::where('user_id', $user->id)->findOrFail($id);
        $notification->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث حالة الإشعار',
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        Notification::where('user_id', $user->id)->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'تم تعيين كافة الإشعارات كمقروءة',
        ]);
    }
}
