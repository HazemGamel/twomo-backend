@extends('admin.layout')

@section('title', 'الرئيسية والإحصائيات | لوحة تحكم تومو')

@section('content')
<div class="space-y-8">
    <!-- Header Title -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-white">نظرة عامة على التطبيق والإحصائيات</h1>
            <p class="text-sm text-gray-400 mt-1">متابعة حية وفورية للمستخدمين، الرحلات، وشحنات وصّيني عبر الحدود</p>
        </div>
        <div class="flex gap-3">
            <form action="{{ route('admin.clean.demo') }}" method="POST" onsubmit="return confirm('هل تريد مسح جميع الحسابات والبيانات الوهمية التجريبية السابقة؟')">
                @csrf
                <button type="submit" class="px-4 py-2.5 rounded-xl bg-red-500/10 text-red-400 border border-red-500/30 font-bold text-xs hover:bg-red-500/20 transition flex items-center gap-2">
                    <i class="fa-solid fa-broom"></i> تنظيف الحسابات الوهمية
                </button>
            </form>
            <a href="{{ route('admin.banners') }}" class="px-4 py-2.5 rounded-xl bg-brand-green text-slate-950 font-bold text-sm hover:bg-emerald-400 transition flex items-center gap-2 shadow-lg shadow-brand-green/20">
                <i class="fa-solid fa-plus"></i> إضافة إعلان جديد
            </a>
        </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6">
        <!-- Stat 1: Users -->
        <div class="bg-brand-card p-5 rounded-3xl border border-brand-border">
            <div class="flex justify-between items-center text-gray-400 mb-3">
                <span class="text-xs font-bold">إجمالي المستخدمين</span>
                <span class="p-2.5 rounded-xl bg-blue-500/10 text-blue-400 text-sm"><i class="fa-solid fa-users"></i></span>
            </div>
            <div class="text-3xl font-black text-white">{{ $usersCount }}</div>
            <div class="mt-2 text-xs text-gray-400 flex items-center gap-2">
                <span class="text-brand-green font-bold">{{ $ownersCount }} صاحب سيارة</span>
                <span>•</span>
                <span class="text-brand-gold font-bold">{{ $travelersCount }} مسافر</span>
            </div>
        </div>

        <!-- Stat 2: Active Trips -->
        <div class="bg-brand-card p-5 rounded-3xl border border-brand-border">
            <div class="flex justify-between items-center text-gray-400 mb-3">
                <span class="text-xs font-bold">الرحلات النشطة</span>
                <span class="p-2.5 rounded-xl bg-brand-green/10 text-brand-green text-sm"><i class="fa-solid fa-car-side"></i></span>
            </div>
            <div class="text-3xl font-black text-white">{{ $activeTripsCount }}</div>
            <div class="mt-2 text-xs text-gray-400">من إجمالي {{ $tripsCount }} رحلة مسجلة</div>
        </div>

        <!-- Stat 3: Shipments (Wassini) -->
        <div class="bg-brand-card p-5 rounded-3xl border border-brand-border">
            <div class="flex justify-between items-center text-gray-400 mb-3">
                <span class="text-xs font-bold">شحنات (وصّيني)</span>
                <span class="p-2.5 rounded-xl bg-brand-gold/10 text-brand-gold text-sm"><i class="fa-solid fa-box-open"></i></span>
            </div>
            <div class="text-3xl font-black text-white">{{ $shipmentsCount }}</div>
            <div class="mt-2 text-xs text-gray-400">طلبات نقل أمانات وبضائع</div>
        </div>

        <!-- Stat 4: Active Banners -->
        <div class="bg-brand-card p-5 rounded-3xl border border-brand-border">
            <div class="flex justify-between items-center text-gray-400 mb-3">
                <span class="text-xs font-bold">الإعلانات والبانرات</span>
                <span class="p-2.5 rounded-xl bg-purple-500/10 text-purple-400 text-sm"><i class="fa-solid fa-rectangle-ad"></i></span>
            </div>
            <div class="text-3xl font-black text-white">{{ $bannersCount }}</div>
            <div class="mt-2 text-xs text-purple-400 font-semibold">تظهر في الشاشة الرئيسية</div>
        </div>
    </div>

    <!-- Live Border Status Control Cards -->
    <div class="bg-brand-card rounded-3xl border border-brand-border p-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-lg font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-traffic-light text-brand-gold"></i> التحكم المباشر بحالة المنافذ البرية
                </h2>
                <p class="text-xs text-gray-400 mt-1">تعديل الإشارات الضوئية وحالة الطرق الدولية فوراً في التطبيق</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($borders as $b)
                <div class="bg-[#0F1726] p-4 rounded-2xl border border-brand-border flex flex-col justify-between gap-4">
                    <div>
                        <div class="flex justify-between items-start">
                            <span class="font-bold text-white text-sm">{{ $b->name_ar }}</span>
                            <span class="w-3.5 h-3.5 rounded-full" style="background-color: {{ $b->color_hex ?? '#00C48C' }};"></span>
                        </div>
                        <div class="text-xs text-gray-400 mt-1">{{ $b->location_ar }}</div>
                        <div class="text-xs text-brand-green font-bold mt-2">الحالة الحالية: {{ $b->status_ar }} ({{ $b->reports_count }} بلاغ)</div>
                    </div>

                    <form action="{{ route('admin.borders.update', $b->id) }}" method="POST" class="flex gap-2">
                        @csrf
                        <select name="status_ar" class="bg-brand-card border border-brand-border text-xs rounded-xl px-2 py-2 text-white flex-1 focus:outline-none">
                            <option value="سالك وطبيعي" {{ $b->status_ar == 'سالك وطبيعي' ? 'selected' : '' }}>سالك (أخضر)</option>
                            <option value="متوسط الحركة" {{ $b->status_ar == 'متوسط الحركة' ? 'selected' : '' }}>متوسط (برتقالي)</option>
                            <option value="مزدحم" {{ $b->status_ar == 'مزدحم' ? 'selected' : '' }}>مزدحم (أحمر)</option>
                        </select>
                        <select name="color_hex" class="bg-brand-card border border-brand-border text-xs rounded-xl px-2 py-2 text-white focus:outline-none">
                            <option value="#00C48C" {{ $b->color_hex == '#00C48C' ? 'selected' : '' }}>أخضر</option>
                            <option value="#F59E0B" {{ $b->color_hex == '#F59E0B' ? 'selected' : '' }}>برتقالي</option>
                            <option value="#EF4444" {{ $b->color_hex == '#EF4444' ? 'selected' : '' }}>أحمر</option>
                        </select>
                        <button type="submit" class="px-3 py-2 bg-brand-green/10 text-brand-green border border-brand-green/30 hover:bg-brand-green/20 rounded-xl text-xs font-bold transition">
                            حفظ
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>

    <!-- 2 Column Section: Recent Trips & Recent Users -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Recent Trips Table -->
        <div class="bg-brand-card rounded-3xl border border-brand-border p-6">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-route text-brand-green"></i> أحدث الرحلات الدولية
                </h3>
                <a href="{{ route('admin.trips') }}" class="text-xs text-brand-green hover:underline font-bold">عرض الكل</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="text-gray-400 border-b border-brand-border pb-2">
                        <tr>
                            <th class="py-2.5">المسار</th>
                            <th class="py-2.5">صاحب السيارة</th>
                            <th class="py-2.5">المقاعد</th>
                            <th class="py-2.5">الحالة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-border/50 text-gray-200">
                        @forelse($recentTrips as $t)
                            <tr>
                                <td class="py-3 font-semibold">{{ $t->from_city }} ⇋ {{ $t->to_city }}</td>
                                <td class="py-3 text-gray-400">{{ $t->driver->name ?? 'غير معروف' }}</td>
                                <td class="py-3">{{ $t->available_seats }} متاح</td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $t->status == 'active' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-gray-500/10 text-gray-400' }}">
                                        {{ $t->status == 'active' ? 'متاحة' : 'مكتملة' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-center text-gray-500">لا توجد رحلات مسجلة بعد</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Users Table -->
        <div class="bg-brand-card rounded-3xl border border-brand-border p-6">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-base font-bold text-white flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-blue-400"></i> أحدث المستخدمين المسجلين
                </h3>
                <a href="{{ route('admin.users') }}" class="text-xs text-brand-green hover:underline font-bold">عرض الكل</a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right text-xs">
                    <thead class="text-gray-400 border-b border-brand-border pb-2">
                        <tr>
                            <th class="py-2.5">الاسم</th>
                            <th class="py-2.5">الجوال</th>
                            <th class="py-2.5">النوع</th>
                            <th class="py-2.5">تاريخ التسجيل</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-border/50 text-gray-200">
                        @forelse($recentUsers as $u)
                            <tr>
                                <td class="py-3 font-semibold text-white">{{ $u->name }}</td>
                                <td class="py-3 text-gray-400 font-mono">{{ $u->phone }}</td>
                                <td class="py-3">
                                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold {{ $u->role == 'owner' ? 'bg-brand-green/10 text-brand-green' : 'bg-brand-gold/10 text-brand-gold' }}">
                                        {{ $u->role == 'owner' ? 'صاحب سيارة' : 'مسافر' }}
                                    </span>
                                </td>
                                <td class="py-3 text-gray-400 text-[11px]">{{ $u->created_at ? $u->created_at->diffForHumans() : '-' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="py-4 text-center text-gray-500">لا يوجد مستخدمين مسجلين بعد</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
