<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'لوحة تحكم تومو | TWOMO')</title>
    <!-- Google Fonts Cairo -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            green: '#00C48C',
                            dark: '#0A111E',
                            card: '#141D2D',
                            border: '#1F2E45',
                            gold: '#F59E0B',
                        }
                    },
                    fontFamily: {
                        cairo: ['Cairo', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <style>
        body {
            font-family: 'Cairo', sans-serif;
            background-color: #0A111E;
            color: #F3F4F6;
        }
    </style>
</head>
<body class="bg-brand-dark min-h-screen flex flex-col">
    <!-- Top Navbar -->
    <header class="bg-brand-card border-b border-brand-border sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between items-center h-16">
            <div class="flex items-center gap-4">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <span class="text-2xl font-black text-brand-green tracking-wide">TWOMO</span>
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-brand-green/10 text-brand-green border border-brand-green/30">لوحة التحكم</span>
                </a>
            </div>

            <nav class="hidden md:flex items-center gap-6">
                <a href="{{ route('admin.dashboard') }}" class="text-sm font-bold text-gray-300 hover:text-brand-green transition flex items-center gap-2">
                    <i class="fa-solid fa-chart-pie"></i> الرئيسية والإحصائيات
                </a>
                <a href="{{ route('admin.banners') }}" class="text-sm font-bold text-gray-300 hover:text-brand-green transition flex items-center gap-2">
                    <i class="fa-solid fa-rectangle-ad"></i> إعلانات التطبيق
                </a>
                <a href="{{ route('admin.users') }}" class="text-sm font-bold text-gray-300 hover:text-brand-green transition flex items-center gap-2">
                    <i class="fa-solid fa-users"></i> المستخدمين
                </a>
                <a href="{{ route('admin.trips') }}" class="text-sm font-bold text-gray-300 hover:text-brand-green transition flex items-center gap-2">
                    <i class="fa-solid fa-route"></i> الرحلات
                </a>
                <a href="{{ route('admin.shipments') }}" class="text-sm font-bold text-gray-300 hover:text-brand-green transition flex items-center gap-2">
                    <i class="fa-solid fa-box-open"></i> الشحنات (وصّيني)
                </a>
            </nav>

            <div class="flex items-center gap-4">
                <div class="text-left hidden sm:block">
                    <div class="text-xs font-bold text-white">{{ session('admin_user', 'مدير النظام') }}</div>
                    <div class="text-[10px] text-brand-green font-semibold">نشط الآن</div>
                </div>
                <form action="{{ route('admin.logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="p-2 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 transition text-sm flex items-center gap-2">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span class="hidden sm:inline">خروج</span>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if(session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-brand-green/15 border border-brand-green text-brand-green font-bold flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-xl"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 p-4 rounded-2xl bg-red-500/15 border border-red-500 text-red-400 font-bold flex items-center gap-3">
                <i class="fa-solid fa-triangle-exclamation text-xl"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-brand-card border-t border-brand-border py-4 text-center text-xs text-gray-500">
        منصة تومو (TWOMO) &copy; 2026 - نظام الإدارة المركزي والتحكم المباشر
    </footer>
</body>
</html>
