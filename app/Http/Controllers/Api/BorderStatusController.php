<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BorderStatus;
use App\Models\Notification;
use Illuminate\Http\Request;

class BorderStatusController extends Controller
{
    public function index()
    {
        $borders = BorderStatus::all();

        return response()->json([
            'success' => true,
            'data' => $borders,
        ]);
    }

    public function report(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'border_id' => 'required|exists:border_statuses,id',
            'status_ar' => 'required|string',
            'status_en' => 'sometimes|string',
            'color_hex' => 'sometimes|string',
            'comment' => 'nullable|string',
        ]);

        $border = BorderStatus::findOrFail($validated['border_id']);
        $border->increment('reports_count');
        $border->update([
            'status_ar' => $validated['status_ar'],
            'status_en' => $validated['status_en'] ?? $border->status_en,
            'color_hex' => $validated['color_hex'] ?? $border->color_hex,
        ]);

        // User points reward logic
        $user->increment('border_points');
        $earnedFreeMonth = false;

        if ($user->border_points >= 20) {
            $user->update([
                'is_subscribed' => true,
                'border_points' => 0,
            ]);
            $earnedFreeMonth = true;

            Notification::create([
                'user_id' => $user->id,
                'title' => 'مبارك! حصلت على شهر مجاني 🎁',
                'body' => 'شكراً لمساهمتك الفعالة في تحديث حالة المنافذ البرية! تم تفعيل اشتراكك الشهري المجاني.',
                'type' => 'system',
                'is_read' => false,
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'شكراً لإبلاغك! تم تحديث حالة المنفذ واحتساب نقطة مكافأة لحسابك.',
            'current_points' => $user->border_points,
            'earned_free_month' => $earnedFreeMonth,
            'border' => $border->fresh(),
        ]);
    }
}
