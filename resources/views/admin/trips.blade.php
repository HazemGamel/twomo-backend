@extends('admin.layout')

@section('title', 'إدارة الرحلات الدولية | تومو TWOMO')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-black text-white flex items-center gap-2">
            <i class="fa-solid fa-route text-brand-green"></i> سجل الرحلات الدولية المسجلة ({{ $trips->total() }})
        </h1>
        <p class="text-sm text-gray-400 mt-1">متابعة جميع الرحلات المعروضة بين مدن السعودية والأردن</p>
    </div>

    <div class="bg-brand-card rounded-3xl border border-brand-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="text-gray-400 bg-[#0F1726] border-b border-brand-border">
                    <tr>
                        <th class="p-4">#</th>
                        <th class="p-4">صاحب السيارة</th>
                        <th class="p-4">خط السير (من ⇋ إلى)</th>
                        <th class="p-4">المنفذ البري</th>
                        <th class="p-4">تاريخ وموعد الرحلة</th>
                        <th class="p-4">المقاعد المتاحة</th>
                        <th class="p-4">الحالة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/40 text-gray-200">
                    @forelse($trips as $t)
                        <tr class="hover:bg-brand-dark/30 transition">
                            <td class="p-4 text-gray-500 font-mono">{{ $t->id }}</td>
                            <td class="p-4">
                                <div class="font-bold text-white">{{ $t->driver->name ?? 'غير معروف' }}</div>
                                <div class="text-[10px] text-gray-400 font-mono">{{ $t->driver->phone ?? '' }}</div>
                            </td>
                            <td class="p-4 font-bold text-brand-green text-sm">
                                {{ $t->from_city }} <i class="fa-solid fa-arrow-left text-xs mx-1 text-gray-500"></i> {{ $t->to_city }}
                            </td>
                            <td class="p-4 text-gray-300">{{ $t->border_name }}</td>
                            <td class="p-4 text-gray-300 font-mono">{{ $t->date }} - {{ $t->time }}</td>
                            <td class="p-4 font-bold">{{ $t->available_seats }} مقاعد</td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $t->status == 'active' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' : 'bg-gray-500/10 text-gray-400' }}">
                                    {{ $t->status == 'active' ? 'متاحة للركاب' : 'مكتملة' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-500">لا توجد رحلات مسجلة حالياً</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-brand-border">
            {{ $trips->links() }}
        </div>
    </div>
</div>
@endsection
