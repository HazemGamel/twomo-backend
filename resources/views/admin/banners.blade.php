@extends('admin.layout')

@section('title', 'إدارة إعلانات وبانرات التطبيق | تومو TWOMO')

@section('content')
<div class="space-y-8">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-black text-white flex items-center gap-2">
                <i class="fa-solid fa-rectangle-ad text-brand-green"></i> إدارة إعلانات وبانرات التطبيق
            </h1>
            <p class="text-sm text-gray-400 mt-1">ارفع صورة الإعلان وستظهر فوراً في الشريط الترويجي داخل التطبيق لجميع المستخدمين</p>
        </div>
    </div>

    <!-- Upload New Banner Card -->
    <div class="bg-brand-card p-6 rounded-3xl border border-brand-border">
        <h2 class="text-base font-bold text-white mb-4 flex items-center gap-2">
            <i class="fa-solid fa-cloud-arrow-up text-brand-gold"></i> إضافة إعلان جديد
        </h2>

        <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-300 mb-2">عنوان الإعلان *</label>
                <input type="text" name="title" required placeholder="مثال: خصم 20% على شحنات الأردن"
                    class="w-full bg-[#0F1726] border border-brand-border rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-brand-green">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-300 mb-2">النص الفرعي (اختياري)</label>
                <input type="text" name="subtitle" placeholder="مثال: استخدم كود TWOMO20 عند الشحن"
                    class="w-full bg-[#0F1726] border border-brand-border rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-brand-green">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-300 mb-2">رفع صورة من جهازك</label>
                <input type="file" name="image" accept="image/*"
                    class="w-full bg-[#0F1726] border border-brand-border rounded-xl px-3 py-2 text-gray-300 text-xs focus:outline-none file:mr-2 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-brand-green/20 file:text-brand-green hover:file:bg-brand-green/30">
                <span class="text-[11px] text-gray-500 mt-1 block">أبعاد مقترحة: 1200x600 بكسل (PNG, JPG, WebP)</span>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-300 mb-2">أو رابط صورة مباشر (URL)</label>
                <input type="url" name="image_url" placeholder="https://example.com/banner.jpg"
                    class="w-full bg-[#0F1726] border border-brand-border rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-brand-green">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-300 mb-2">رابط خارجي عند النقر (اختياري)</label>
                <input type="url" name="target_url" placeholder="https://wa.me/966... أو رابط خارجي"
                    class="w-full bg-[#0F1726] border border-brand-border rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-brand-green">
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-300 mb-2">ترتيب الظهور</label>
                <input type="number" name="display_order" value="1"
                    class="w-full bg-[#0F1726] border border-brand-border rounded-xl px-4 py-2.5 text-white text-sm focus:outline-none focus:border-brand-green">
            </div>

            <div class="md:col-span-2 pt-2">
                <button type="submit" class="px-6 py-3 bg-brand-green hover:bg-emerald-400 text-slate-950 font-bold rounded-xl transition text-sm flex items-center gap-2 shadow-lg shadow-brand-green/20">
                    <i class="fa-solid fa-check"></i> حفظ ونشر الإعلان فوراً في التطبيق
                </button>
            </div>
        </form>
    </div>

    <!-- Active Banners List -->
    <div class="bg-brand-card p-6 rounded-3xl border border-brand-border">
        <h2 class="text-base font-bold text-white mb-6 flex items-center gap-2">
            <i class="fa-solid fa-list-check text-brand-green"></i> الإعلانات الحالية في التطبيق ({{ $banners->count() }})
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($banners as $b)
                <div class="bg-[#0F1726] rounded-2xl border border-brand-border overflow-hidden flex flex-col justify-between">
                    <div>
                        <div class="relative h-44 w-full bg-slate-900 overflow-hidden">
                            <img src="{{ $b->image_url }}" alt="{{ $b->title }}" class="w-full h-full object-cover">
                            <div class="absolute top-3 right-3">
                                <span class="px-2.5 py-1 rounded-full text-xs font-bold {{ $b->is_active ? 'bg-emerald-500 text-slate-950' : 'bg-red-500 text-white' }}">
                                    {{ $b->is_active ? 'نشط ويظهر بالتطبيق' : 'معطل' }}
                                </span>
                            </div>
                        </div>

                        <div class="p-4">
                            <h3 class="font-bold text-white text-base">{{ $b->title }}</h3>
                            @if($b->subtitle)
                                <p class="text-xs text-gray-400 mt-1">{{ $b->subtitle }}</p>
                            @endif
                            @if($b->target_url)
                                <a href="{{ $b->target_url }}" target="_blank" class="text-xs text-brand-green hover:underline mt-2 inline-block">
                                    <i class="fa-solid fa-arrow-up-right-from-square"></i> الرابط المرتبط
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="p-4 bg-brand-card/50 border-t border-brand-border flex justify-between items-center">
                        <span class="text-xs text-gray-500">الترتيب: {{ $b->display_order }}</span>
                        <div class="flex items-center gap-2">
                            <form action="{{ route('admin.banners.toggle', $b->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-bold {{ $b->is_active ? 'bg-amber-500/10 text-amber-400 border border-amber-500/30' : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/30' }}">
                                    {{ $b->is_active ? 'إيقاف' : 'تفعيل' }}
                                </button>
                            </form>

                            <form action="{{ route('admin.banners.delete', $b->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا الإعلان؟')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg text-xs text-red-400 hover:bg-red-500/10 border border-red-500/30">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-12 text-center text-gray-500">
                    <i class="fa-solid fa-rectangle-ad text-4xl mb-3 text-gray-600 block"></i>
                    لا توجد إعلانات مضافة بعد. أضف إعلانك الأول ليظهر فوراً في شاشة التطبيق الرئيسية!
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
