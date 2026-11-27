<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BorderStatus;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Only seed official borders if table is empty
        if (BorderStatus::count() == 0) {
            BorderStatus::create([
                'name_ar' => 'منفذ الحديثة (السعودية) ⇋ منفذ العمري (الأردن)',
                'name_en' => 'Al-Haditha (KSA) ⇋ Al-Omari (Jordan)',
                'location_ar' => 'محافظة القريات - الطريق الدولي الشمالي',
                'location_en' => 'Al-Qurayyat - Northern International Hwy',
                'status_ar' => 'سالك وطبيعي',
                'status_en' => 'Smooth & Normal',
                'color_hex' => '#00C48C',
                'reports_count' => 0,
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
                'reports_count' => 0,
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
                'reports_count' => 0,
                'tip_ar' => 'انسيابية تامة في الجمارك والجوازات.',
                'tip_en' => 'Seamless clearance at customs and passport control.',
            ]);
        }
    }
}
