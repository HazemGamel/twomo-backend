<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function requestOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'name' => 'sometimes|nullable|string|max:100',
        ]);

        $phone = trim($request->phone);
        $name = trim($request->name ?? '');

        // Find or create user
        $user = User::where('phone', $phone)->first();
        if (!$user) {
            $user = User::create([
                'phone' => $phone,
                'name' => !empty($name) ? $name : ('مستخدم تومو ' . substr($phone, -4)),
                'email' => 'user_' . Str::random(6) . '@twomo.app',
                'password' => Hash::make(Str::random(16)),
                'role' => 'traveler',
                'is_subscribed' => true,
                'border_points' => 0,
            ]);
        } else {
            if (!empty($name)) {
                $user->update(['name' => $name]);
            }
        }

        // In local development, return OTP directly for instant verification
        $otp = '5821';

        return response()->json([
            'success' => true,
            'message' => 'تم إرسال رمز التحقق بنجاح',
            'phone' => $phone,
            'otp' => $otp,
            'is_existing_user' => $user->wasRecentlyCreated ? false : true,
            'user' => $user->fresh(),
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|string',
            'name' => 'sometimes|nullable|string|max:100',
        ]);

        $phone = trim($request->phone);
        $user = User::where('phone', $phone)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'المستخدم غير موجود',
            ], 404);
        }

        if (!empty($request->name)) {
            $user->update(['name' => trim($request->name)]);
        }

        // Accept demo OTP '5821' or any 4 digits in local test mode
        if ($request->otp !== '5821' && strlen($request->otp) !== 4) {
            return response()->json([
                'success' => false,
                'message' => 'رمز التحقق غير صحيح',
            ], 422);
        }

        // Generate Sanctum access token
        $token = $user->createToken('twomo_auth_token')->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'تم تسجيل الدخول بنجاح',
            'token' => $token,
            'user' => $user->fresh(),
        ]);
    }

    public function getProfile(Request $request)
    {
        return response()->json([
            'success' => true,
            'user' => $request->user(),
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:100',
            'role' => 'sometimes|string|in:traveler,owner',
            'gender' => 'sometimes|string|in:male,female',
            'nationality' => 'sometimes|string|max:50',
            'id_number' => 'sometimes|nullable|string|max:30',
            'car_model' => 'sometimes|nullable|string|max:100',
            'car_plate' => 'sometimes|nullable|string|max:50',
            'fcm_token' => 'sometimes|nullable|string',
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث الملف الشخصي بنجاح',
            'user' => $user->fresh(),
        ]);
    }

    public function switchRole(Request $request)
    {
        $user = $request->user();
        $newRole = $user->role === 'owner' ? 'traveler' : 'owner';
        $user->update(['role' => $newRole]);

        return response()->json([
            'success' => true,
            'message' => 'تم تغيير الدور بنجاح',
            'role' => $newRole,
            'user' => $user,
        ]);
    }
}
