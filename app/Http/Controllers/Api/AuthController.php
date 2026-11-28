<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Normalize international phone numbers for WhatsApp API (Noti-Fire)
     */
    private function formatPhoneForWhatsApp(string $phone): string
    {
        // Extract digits only
        $digits = preg_replace('/\D/', '', $phone);

        // Strip leading 00
        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        // Saudi local format (05xxxxxxxx) -> 9665xxxxxxxx
        if (preg_match('/^05(\d{8})$/', $digits, $m)) {
            return '9665' . $m[1];
        }

        // Jordan local format (07xxxxxxxx) -> 9627xxxxxxxx
        if (preg_match('/^07(\d{8})$/', $digits, $m)) {
            return '9627' . $m[1];
        }

        // Egypt local format (01xxxxxxxxx) -> 201xxxxxxxxx
        if (preg_match('/^01(\d{9})$/', $digits, $m)) {
            return '201' . $m[1];
        }

        // Fix if country code has an extra 0: 96605... -> 9665...
        if (preg_match('/^9660(5\d{8})$/', $digits, $m)) {
            return '966' . $m[1];
        }

        // Fix Jordan: 96207... -> 9627...
        if (preg_match('/^9620(7\d{8})$/', $digits, $m)) {
            return '962' . $m[1];
        }

        // Fix Egypt: 2001... -> 201...
        if (preg_match('/^200(1\d{9})$/', $digits, $m)) {
            return '20' . $m[1];
        }

        return $digits;
    }

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
                'name' => !empty($name) ? $name : ('مستخدم تومو ' . substr(preg_replace('/\D/', '', $phone), -4)),
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

        // Generate 4-digit OTP
        $otp = (string) rand(1000, 9999);

        // Clean phone for caching
        $cleanPhone = preg_replace('/\D/', '', $phone);
        $whatsAppPhone = $this->formatPhoneForWhatsApp($phone);

        // Store in Cache for 10 minutes
        Cache::put('twomo_otp_' . $cleanPhone, $otp, now()->addMinutes(10));
        Cache::put('twomo_otp_' . $whatsAppPhone, $otp, now()->addMinutes(10));

        // Send via Noti-Fire WhatsApp API
        $deviceId = env('NOTIFIRE_DEVICE_ID', 'cee55a3e-658a-4cd8-80e8-1bc6a13b0f9c');
        $sentViaWhatsApp = false;
        $apiMessage = null;

        try {
            $messageBody = "رمز التحقق لمنصة تومو (TWOMO) هو: {$otp}\n\nصالح لمدة 10 دقائق.\nيرجى عدم مشاركة هذا الرمز مع أي شخص.";

            $response = Http::timeout(12)->post('https://www.noti-fire.com/api/send/message', [
                'device_id' => $deviceId,
                'to' => $whatsAppPhone,
                'message' => $messageBody,
            ]);

            if ($response->successful()) {
                $resData = $response->json();
                if (isset($resData['success']) && $resData['success'] === true) {
                    $sentViaWhatsApp = true;
                } else {
                    $apiMessage = $resData['message'] ?? 'لم تكتمل عملية الإرسال من مزود الواتساب';
                    Log::warning('Noti-Fire WhatsApp API response: ' . json_encode($resData));
                }
            } else {
                $apiMessage = 'خطأ خادم الواتساب كود: ' . $response->status();
                Log::warning('Noti-Fire HTTP error: ' . $response->body());
            }
        } catch (\Throwable $e) {
            $apiMessage = $e->getMessage();
            Log::error('Noti-Fire Exception: ' . $e->getMessage());
        }

        return response()->json([
            'success' => true,
            'message' => $sentViaWhatsApp
                ? 'تم إرسال رمز التحقق إلى رقم الواتساب الخاص بك بنجاح'
                : 'تم إنشاء رمز التحقق' . ($apiMessage ? " ($apiMessage)" : ''),
            'phone' => $phone,
            'whatsapp_phone' => $whatsAppPhone,
            'sent_via_whatsapp' => $sentViaWhatsApp,
            'whatsapp_error' => $apiMessage,
            'otp' => $otp, // Keep for dev fallback so user is never locked out
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

        $cleanPhone = preg_replace('/\D/', '', $phone);
        $whatsAppPhone = $this->formatPhoneForWhatsApp($phone);

        $cachedOtp1 = Cache::get('twomo_otp_' . $cleanPhone);
        $cachedOtp2 = Cache::get('twomo_otp_' . $whatsAppPhone);

        $enteredOtp = trim($request->otp);

        $isValid = ($enteredOtp === $cachedOtp1)
            || ($enteredOtp === $cachedOtp2)
            || ($enteredOtp === '5821'); // Fallback master code for testing

        if (!$isValid) {
            return response()->json([
                'success' => false,
                'message' => 'رمز التحقق غير صحيح أو انتهت صلاحيته',
            ], 422);
        }

        // Clean up cache
        Cache::forget('twomo_otp_' . $cleanPhone);
        Cache::forget('twomo_otp_' . $whatsAppPhone);

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
