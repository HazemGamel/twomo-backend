<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Trip;
use App\Models\TripBooking;
use App\Models\Shipment;
use App\Models\BorderStatus;
use App\Models\Notification;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create Core Users
        $driver1 = User::create([
            'name' => 'محمد عبدالله',
            'phone' => '+966 500 000 000',
            'email' => 'm.abdullah@twomo.app',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'gender' => 'male',
            'nationality' => 'سعودي',
            'id_number' => '1098765432',
            'car_model' => 'تويوتا كامري 2024',
            'car_plate' => 'أ ب ج 1234',
            'rating' => 4.9,
            'wallet_balance' => 185.00,
            'is_subscribed' => true,
            'border_points' => 7,
        ]);

        $driver2 = User::create([
            'name' => 'أحمد العمري',
            'phone' => '+962 790 000 001',
            'email' => 'a.omari@twomo.app',
            'password' => Hash::make('password123'),
            'role' => 'owner',
            'gender' => 'male',
            'nationality' => 'أردني',
            'id_number' => '9901020304',
            'car_model' => 'هيونداي سوناتا 2023',
            'car_plate' => 'ع م 5678',
            'rating' => 4.8,
            'wallet_balance' => 95.00,
            'is_subscribed' => true,
            'border_points' => 12,
        ]);

        $passenger1 = User::create([
            'name' => 'خالد المجالي',
            'phone' => '+962 790 000 002',
            'email' => 'k.majali@twomo.app',
            'password' => Hash::make('password123'),
            'role' => 'traveler',
            'gender' => 'male',
            'nationality' => 'أردني',
            'id_number' => '9922030405',
            'rating' => 5.0,
            'wallet_balance' => 50.00,
            'is_subscribed' => true,
            'border_points' => 4,
        ]);

        $passenger2 = User::create([
            'name' => 'سعد القحطاني',
            'phone' => '+966 550 000 003',
            'email' => 's.qahtani@twomo.app',
            'password' => Hash::make('password123'),
            'role' => 'traveler',
            'gender' => 'male',
            'nationality' => 'سعودي',
            'id_number' => '1088776655',
            'rating' => 4.9,
            'wallet_balance' => 0.00,
            'is_subscribed' => true,
            'border_points' => 15,
        ]);

        // 2. Create Border Statuses
        BorderStatus::create([
            'name_ar' => 'منفذ الحديثة (السعودية) ⇋ منفذ العمري (الأردن)',
            'name_en' => 'Al-Haditha (KSA) ⇋ Al-Omari (Jordan)',
            'location_ar' => 'محافظة القريات - الطريق الدولي الشمالي',
            'location_en' => 'Al-Qurayyat - Northern International Hwy',
            'status_ar' => 'سالك وطبيعي',
            'status_en' => 'Smooth & Normal',
            'color_hex' => '#00C48C',
            'reports_count' => 24,
            'tip_ar' => 'حركة السير ميسرة، وقت مثالي للعبور باطمئنان.',
            'tip_en' => 'Smooth traffic, ideal time to cross comfortably.',
        ]);

        BorderStatus::create([
            'name_ar' => 'منفذ حالة عمار (السعودية) ⇋ منفذ المدورة (الأردن)',
            'name_en' => 'Halat Ammar (KSA) ⇋ Al-Mudawwara (Jordan)',
            'location_ar' => 'منطقة تبوك - طريق معان الدولي',
            'location_en' => 'Tabuk Region - Ma\'an International Road',
            'status_ar' => 'متوسط الحركة',
            'status_en' => 'Moderate Flow',
            'color_hex' => '#F5A623',
            'reports_count' => 16,
            'tip_ar' => 'تدفق معتدل للمركبات، الإجراءات تسير بشكل اعتيادي.',
            'tip_en' => 'Moderate vehicle flow, procedures running normally.',
        ]);

        BorderStatus::create([
            'name_ar' => 'منفذ الدرة (السعودية) ⇋ منفذ العقبة (الأردن)',
            'name_en' => 'Ad-Durra (KSA) ⇋ Aqaba (Jordan)',
            'location_ar' => 'محافظة حقل - خليج العقبة',
            'location_en' => 'Haql Governorate - Gulf of Aqaba',
            'status_ar' => 'سالك وطبيعي',
            'status_en' => 'Smooth & Clear',
            'color_hex' => '#00C48C',
            'reports_count' => 9,
            'tip_ar' => 'انسيابية تامة في الجمارك والجوازات.',
            'tip_en' => 'Seamless clearance at customs and passport control.',
        ]);

        // 3. Create Sample Initial Trips
        $trip1 = Trip::create([
            'driver_id' => $driver1->id,
            'from_country' => 'SA',
            'from_city' => 'تبوك',
            'to_country' => 'JO',
            'to_city' => 'عمان',
            'border_name' => 'منفذ حالة عمار (السعودية) ⇋ منفذ المدورة (الأردن)',
            'departure_date' => now()->addDays(1)->setTime(9, 0),
            'available_seats' => 2,
            'total_seats' => 3,
            'price_per_seat' => 70.00,
            'notes' => 'الانطلاق من محطة ساسكو طريق الأردن، مكيف وتوقف سريع للاستراحة.',
            'status' => 'scheduled',
        ]);

        $trip2 = Trip::create([
            'driver_id' => $driver2->id,
            'from_country' => 'JO',
            'from_city' => 'عمان',
            'to_country' => 'SA',
            'to_city' => 'تبوك',
            'border_name' => 'منفذ حالة عمار (السعودية) ⇋ منفذ المدورة (الأردن)',
            'departure_date' => now()->addDays(2)->setTime(14, 30),
            'available_seats' => 3,
            'total_seats' => 4,
            'price_per_seat' => 65.00,
            'notes' => 'رحلة مسائية مباشرة، متسع للحقائب الشخصية.',
            'status' => 'scheduled',
        ]);

        // 4. Create A Booking for Trip 1
        TripBooking::create([
            'trip_id' => $trip1->id,
            'passenger_id' => $passenger1->id,
            'seats_booked' => 1,
            'pickup_location' => 'دوار السنابل، تبوك',
            'notes' => 'حقيبة ظهر شخصية واحدة فقط',
            'status' => 'confirmed',
        ]);

        // 5. Create Initial Shipments (Matching amanat_data.dart)
        Shipment::create([
            'sender_id' => $passenger1->id,
            'traveler_id' => $driver1->id,
            'tracking_code' => 'TW-JOSA-8821',
            'from_country' => 'JO',
            'from_city' => 'عمان',
            'to_country' => 'SA',
            'to_city' => 'تبوك',
            'border_name' => 'منفذ حالة عمار ⇋ المدورة',
            'recipient_name' => 'فيصل الحربي',
            'recipient_phone' => '+966501234567',
            'category' => 'مستندات وأوراق رسمية',
            'details' => 'مظروف كرتوني مختوم يحتوي على أوراق وشهادات جامعية أصلية مصدقة.',
            'reward_fee' => 45.00,
            'status' => 'in_transit',
        ]);

        Shipment::create([
            'sender_id' => $passenger2->id,
            'traveler_id' => null,
            'tracking_code' => 'TW-SAJO-5542',
            'from_country' => 'SA',
            'from_city' => 'الرياض',
            'to_country' => 'JO',
            'to_city' => 'الزرقاء',
            'border_name' => 'منفذ الحديثة ⇋ العمري',
            'recipient_name' => 'طارق الزعبي',
            'recipient_phone' => '+962799887766',
            'category' => 'أغراض شخصية',
            'details' => 'صندوق تمور سكري فاخر مع هدايا عائلية وملابس مغلفة بإحكام.',
            'reward_fee' => 60.00,
            'status' => 'pending',
        ]);

        // 6. Create Initial Notifications
        Notification::create([
            'user_id' => $driver1->id,
            'title' => 'طلب حجز مقعد جديد 🚗',
            'body' => "حجز الراكب {$passenger1->name} عدد 1 مقعد في رحلتك المتجهة من تبوك إلى عمان.",
            'type' => 'booking_request',
            'data' => [
                'trip_id' => $trip1->id,
                'passenger_name' => $passenger1->name,
                'passenger_phone' => $passenger1->phone,
            ],
            'is_read' => false,
        ]);

        Notification::create([
            'user_id' => $passenger1->id,
            'title' => 'تأكيد حجز المقعد ✅',
            'body' => "تم حجز 1 مقعد بنجاح مع الكابتن {$driver1->name} متجهاً إلى عمان.",
            'type' => 'booking_confirmed',
            'data' => [
                'trip_id' => $trip1->id,
                'captain_name' => $driver1->name,
                'captain_phone' => $driver1->phone,
            ],
            'is_read' => true,
        ]);
    }
}
