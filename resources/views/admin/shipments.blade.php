@extends('admin.layout')

@section('title', 'إدارة شحنات وصّيني | تومو TWOMO')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-black text-white flex items-center gap-2">
            <i class="fa-solid fa-box-open text-brand-gold"></i> شحنات وأمانات (وصّيني) ({{ $shipments->total() }})
        </h1>
        <p class="text-sm text-gray-400 mt-1">متابعة طلبات نقل الطرود والأمانات بين المسافرين وأصحاب السيارات</p>
    </div>

    <div class="bg-brand-card rounded-3xl border border-brand-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="text-gray-400 bg-[#0F1726] border-b border-brand-border">
                    <tr>
                        <th class="p-4">#</th>
                        <th class="p-4">المرسل</th>
                        <th class="p-4">تفاصيل الشحنة</th>
                        <th class="p-4">المسار</th>
                        <th class="p-4">صاحب السيارة المكلف</th>
                        <th class="p-4">الحالة</th>
                        <th class="p-4">التاريخ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/40 text-gray-200">
                    @forelse($shipments as $s)
                        <tr class="hover:bg-brand-dark/30 transition">
                            <td class="p-4 text-gray-500 font-mono">{{ $s->id }}</td>
                            <td class="p-4">
                                <div class="font-bold text-white">{{ $s->sender->name ?? 'غير معروف' }}</div>
                                <div class="text-[10px] text-gray-400 font-mono">{{ $s->sender->phone ?? '' }}</div>
                            </td>
                            <td class="p-4 font-semibold text-white max-w-xs truncate">{{ $s->details ?? $s->item_type }}</td>
                            <td class="p-4 text-brand-green font-bold">
                                {{ $s->from_city }} <i class="fa-solid fa-arrow-left text-xs mx-1 text-gray-500"></i> {{ $s->to_city }}
                            </td>
                            <td class="p-4 text-gray-300">
                                {{ $s->traveler->name ?? 'لم يعين بعد' }}
                            </td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold 
                                    @if($s->status == 'pending') bg-amber-500/10 text-amber-400 border border-amber-500/30
                                    @elseif($s->status == 'accepted') bg-emerald-500/10 text-emerald-400 border border-emerald-500/30
                                    @else bg-gray-500/10 text-gray-400 @endif">
                                    @if($s->status == 'pending') بانتظار الموافقة
                                    @elseif($s->status == 'accepted') مقبولة وجاري التنسيق
                                    @else مكتملة @endif
                                </span>
                            </td>
                            <td class="p-4 text-gray-500 text-[11px]">{{ $s->created_at ? $s->created_at->diffForHumans() : '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-500">لا توجد طلبات شحنات مسجلة حالياً</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-brand-border">
            {{ $shipments->links() }}
        </div>
    </div>
</div>
@endsection
