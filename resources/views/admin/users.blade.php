@extends('admin.layout')

@section('title', 'إدارة المستخدمين | تومو TWOMO')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <h1 class="text-2xl font-black text-white flex items-center gap-2">
                <i class="fa-solid fa-users text-blue-400"></i> قائمة مستخدمي التطبيق ({{ $users->total() }})
            </h1>
            <p class="text-sm text-gray-400 mt-1">عرض وتفاصيل أصحاب السيارات والمسافرين وحالة الحسابات</p>
        </div>

        <!-- Filter & Search -->
        <form method="GET" class="flex flex-wrap items-center gap-3 w-full sm:w-auto">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="بحث بالاسم أو الجوال..."
                class="bg-brand-card border border-brand-border rounded-xl px-4 py-2 text-white text-xs focus:outline-none focus:border-brand-green flex-1 sm:w-64">
            <select name="role" class="bg-brand-card border border-brand-border rounded-xl px-3 py-2 text-white text-xs focus:outline-none">
                <option value="">جميع الأدوار</option>
                <option value="owner" {{ request('role') == 'owner' ? 'selected' : '' }}>أصحاب السيارات</option>
                <option value="traveler" {{ request('role') == 'traveler' ? 'selected' : '' }}>المسافرين</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-brand-green/10 text-brand-green border border-brand-green/30 rounded-xl text-xs font-bold hover:bg-brand-green/20">
                بحث
            </button>
        </form>
    </div>

    <div class="bg-brand-card rounded-3xl border border-brand-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-right text-xs">
                <thead class="text-gray-400 bg-[#0F1726] border-b border-brand-border">
                    <tr>
                        <th class="p-4">#</th>
                        <th class="p-4">المستخدم</th>
                        <th class="p-4">الجوال</th>
                        <th class="p-4">الدور</th>
                        <th class="p-4">بيانات المركبة</th>
                        <th class="p-4">نقاط المنافذ</th>
                        <th class="p-4">الحالة</th>
                        <th class="p-4">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-border/40 text-gray-200">
                    @forelse($users as $u)
                        <tr class="hover:bg-brand-dark/30 transition">
                            <td class="p-4 text-gray-500 font-mono">{{ $u->id }}</td>
                            <td class="p-4 font-bold text-white">{{ $u->name }}</td>
                            <td class="p-4 font-mono text-gray-400">{{ $u->phone }}</td>
                            <td class="p-4">
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $u->role == 'owner' ? 'bg-brand-green/10 text-brand-green border border-brand-green/30' : 'bg-brand-gold/10 text-brand-gold border border-brand-gold/30' }}">
                                    {{ $u->role == 'owner' ? 'صاحب سيارة' : 'مسافر' }}
                                </span>
                            </td>
                            <td class="p-4 text-gray-400">
                                @if($u->car_model)
                                    <div>{{ $u->car_model }}</div>
                                    <div class="text-[10px] text-gray-500 font-mono">{{ $u->car_plate }}</div>
                                @else
                                    <span class="text-gray-600">-</span>
                                @endif
                            </td>
                            <td class="p-4">
                                <span class="text-brand-gold font-bold">{{ $u->border_points ?? 0 }}</span> نقطة
                            </td>
                            <td class="p-4">
                                <span class="px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $u->is_verified ? 'text-emerald-400 bg-emerald-500/10' : 'text-gray-400 bg-gray-500/10' }}">
                                    {{ $u->is_verified ? 'موثق' : 'عادي' }}
                                </span>
                            </td>
                            <td class="p-4">
                                <form action="{{ route('admin.users.toggle', $u->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-xs px-2.5 py-1 rounded-lg border {{ $u->is_verified ? 'border-amber-500/30 text-amber-400 hover:bg-amber-500/10' : 'border-emerald-500/30 text-emerald-400 hover:bg-emerald-500/10' }}">
                                        {{ $u->is_verified ? 'إلغاء التوثيق' : 'توثيق الحساب' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-gray-500">لا يوجد مستخدمين يطابقون خيارات البحث</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-brand-border">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
