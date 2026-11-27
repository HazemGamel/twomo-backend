<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل دخول المدير | تومو TWOMO</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
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
                        }
                    },
                    fontFamily: {
                        cairo: ['Cairo', 'sans-serif'],
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-brand-dark min-h-screen flex items-center justify-center p-4" style="font-family: 'Cairo', sans-serif;">
    <div class="w-full max-w-md bg-brand-card p-8 rounded-3xl border border-brand-border shadow-2xl">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-black text-brand-green tracking-wider mb-2">TWOMO</h1>
            <p class="text-gray-400 text-sm font-semibold">بوابة إدارة التطبيق ولوحة التحكم المركزية</p>
        </div>

        @if(session('error'))
            <div class="mb-5 p-3 rounded-xl bg-red-500/10 border border-red-500/30 text-red-400 text-sm font-bold flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        <form action="{{ route('admin.login.post') }}" method="POST" class="space-y-5">
            @csrf
            <div>
                <label class="block text-xs font-bold text-gray-300 mb-2">اسم المستخدم</label>
                <div class="relative">
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-gray-500">
                        <i class="fa-solid fa-user-shield"></i>
                    </span>
                    <input type="text" name="username" required value="admin"
                        class="w-full bg-[#0F1726] border border-brand-border rounded-xl pr-10 pl-4 py-3 text-white text-sm focus:outline-none focus:border-brand-green transition" placeholder="admin">
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-gray-300 mb-2">كلمة المرور</label>
                <div class="relative">
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3.5 pointer-events-none text-gray-500">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <input type="password" name="password" required
                        class="w-full bg-[#0F1726] border border-brand-border rounded-xl pr-10 pl-4 py-3 text-white text-sm focus:outline-none focus:border-brand-green transition" placeholder="••••••••">
                </div>
            </div>

            <button type="submit" class="w-full bg-brand-green hover:bg-emerald-400 text-slate-950 font-bold py-3.5 rounded-xl transition duration-200 shadow-lg shadow-brand-green/20 text-base">
                دخول لوحة التحكم
            </button>
        </form>

        <div class="mt-6 text-center text-xs text-gray-500">
            تطبيق تومو - رحلات وشحنات الأردن والسعودية
        </div>
    </div>
</body>
</html>
