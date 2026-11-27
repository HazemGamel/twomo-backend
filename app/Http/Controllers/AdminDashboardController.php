<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Trip;
use App\Models\TripBooking;
use App\Models\Shipment;
use App\Models\Banner;
use App\Models\BorderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminDashboardController extends Controller
{
    // Check simple admin session
    private function checkAuth(Request $request)
    {
        if (!$request->session()->get('is_admin')) {
            return false;
        }
        return true;
    }

    // Login page
    public function showLogin(Request $request)
    {
        if ($request->session()->get('is_admin')) {
            return redirect()->route('admin.dashboard');
        }
        return view('admin.login');
    }

    // Process login
    public function login(Request $request)
    {
        $username = $request->input('username');
        $password = $request->input('password');

        // Simple default secure credentials
        if ($username === 'admin' && $password === 'twomo2026') {
            $request->session()->put('is_admin', true);
            $request->session()->put('admin_user', 'مدير منصة تومو');
            return redirect()->route('admin.dashboard')->with('success', 'مرحباً بك في لوحة تحكم تومو!');
        }

        return back()->with('error', 'بيانات الدخول غير صحيحة. يرجى المحاولة مرة أخرى.');
    }

    // Logout
    public function logout(Request $request)
    {
        $request->session()->forget(['is_admin', 'admin_user']);
        return redirect()->route('admin.login')->with('success', 'تم تسجيل الخروج بنجاح.');
    }

    // Main Dashboard
    public function index(Request $request)
    {
        if (!$this->checkAuth($request)) return redirect()->route('admin.login');

        $usersCount = User::count();
        $ownersCount = User::where('role', 'owner')->count();
        $travelersCount = User::where('role', 'traveler')->count();
        $tripsCount = Trip::count();
        $activeTripsCount = Trip::where('status', 'active')->count();
        $shipmentsCount = Shipment::count();
        $bookingsCount = TripBooking::count();
        $bannersCount = Banner::count();

        $recentTrips = Trip::with('driver')->orderBy('created_at', 'desc')->take(6)->get();
        $recentUsers = User::orderBy('created_at', 'desc')->take(6)->get();
        $borders = BorderStatus::all();

        return view('admin.dashboard', compact(
            'usersCount',
            'ownersCount',
            'travelersCount',
            'tripsCount',
            'activeTripsCount',
            'shipmentsCount',
            'bookingsCount',
            'bannersCount',
            'recentTrips',
            'recentUsers',
            'borders'
        ));
    }

    // Users List
    public function users(Request $request)
    {
        if (!$this->checkAuth($request)) return redirect()->route('admin.login');

        $query = User::query();
        if ($request->has('search') && $request->search != '') {
            $s = $request->search;
            $query->where('name', 'like', "%$s%")
                  ->orWhere('phone', 'like', "%$s%");
        }
        if ($request->has('role') && $request->role != '') {
            $query->where('role', $request->role);
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);
        return view('admin.users', compact('users'));
    }

    // Toggle User Status
    public function toggleUserStatus(Request $request, $id)
    {
        if (!$this->checkAuth($request)) return redirect()->route('admin.login');

        $user = User::findOrFail($id);
        $user->is_verified = !$user->is_verified;
        $user->save();

        return back()->with('success', 'تم تحديث حالة المستخدم بنجاح.');
    }

    // Trips List
    public function trips(Request $request)
    {
        if (!$this->checkAuth($request)) return redirect()->route('admin.login');

        $trips = Trip::with('driver')->orderBy('created_at', 'desc')->paginate(15);
        return view('admin.trips', compact('trips'));
    }

    // Shipments List
    public function shipments(Request $request)
    {
        if (!$this->checkAuth($request)) return redirect()->route('admin.login');

        $shipments = Shipment::with(['sender', 'traveler', 'trip'])->orderBy('created_at', 'desc')->paginate(15);
        return view('admin.shipments', compact('shipments'));
    }

    // Banners & Ads List
    public function banners(Request $request)
    {
        if (!$this->checkAuth($request)) return redirect()->route('admin.login');

        $banners = Banner::orderBy('display_order', 'asc')->orderBy('created_at', 'desc')->get();
        return view('admin.banners', compact('banners'));
    }

    // Store Banner
    public function storeBanner(Request $request)
    {
        if (!$this->checkAuth($request)) return redirect()->route('admin.login');

        $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'image_url' => 'nullable|string',
            'target_url' => 'nullable|string',
            'display_order' => 'nullable|integer',
        ]);

        $imageUrl = $request->input('image_url');

        if ($request->hasFile('image')) {
            try {
                $file = $request->file('image');
                $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_\.]/', '', $file->getClientOriginalName());
                $destinationPath = public_path('uploads/banners');
                if (!is_dir($destinationPath)) {
                    @mkdir($destinationPath, 0777, true);
                }
                $file->move($destinationPath, $filename);
                $imageUrl = asset('uploads/banners/' . $filename);
            } catch (\Exception $e) {
                return back()->with('error', 'تعذر حفظ ملف الصورة: ' . $e->getMessage());
            }
        }

        if (empty($imageUrl)) {
            return back()->with('error', 'يرجى رفع صورة أو إدخال رابط صورة صالح للإعلان.');
        }

        Banner::create([
            'title' => $request->title,
            'subtitle' => $request->subtitle,
            'image_url' => $imageUrl,
            'target_url' => $request->target_url,
            'display_order' => $request->display_order ?? 0,
            'is_active' => true,
        ]);

        return back()->with('success', 'تمت إضافة الإعلان بنجاح وسيظهر في التطبيق فوراً!');
    }

    // Toggle Banner
    public function toggleBanner(Request $request, $id)
    {
        if (!$this->checkAuth($request)) return redirect()->route('admin.login');

        $banner = Banner::findOrFail($id);
        $banner->is_active = !$banner->is_active;
        $banner->save();

        return back()->with('success', 'تم تحديث حالة الإعلان.');
    }

    // Delete Banner
    public function deleteBanner(Request $request, $id)
    {
        if (!$this->checkAuth($request)) return redirect()->route('admin.login');

        $banner = Banner::findOrFail($id);
        $banner->delete();

        return back()->with('success', 'تم حذف الإعلان بنجاح.');
    }

    // Update Border Status
    public function updateBorder(Request $request, $id)
    {
        if (!$this->checkAuth($request)) return redirect()->route('admin.login');

        $border = BorderStatus::findOrFail($id);
        $border->status_ar = $request->input('status_ar', $border->status_ar);
        $border->color_hex = $request->input('color_hex', $border->color_hex);
        $border->save();

        return back()->with('success', 'تم تحديث حالة المنفذ البري بنجاح.');
    }

    // Clean All Demo / Dummy Data
    public function cleanDemoData(Request $request)
    {
        if (!$this->checkAuth($request)) return redirect()->route('admin.login');

        // Delete dummy seeded users with placeholder numbers
        $deletedUsers = User::where('phone', 'like', '%000 000%')
            ->orWhere('email', 'like', '%@twomo.app%')
            ->get();

        foreach ($deletedUsers as $user) {
            Trip::where('driver_id', $user->id)->delete();
            Shipment::where('sender_id', $user->id)->orWhere('traveler_id', $user->id)->delete();
            TripBooking::where('passenger_id', $user->id)->delete();
            $user->delete();
        }

        return back()->with('success', 'تم مسح وتنظيف جميع الحسابات والرحلات الوهمية بنجاح! الآن تظهر فقط بيانات المستخدمين الحقيقيين.');
    }
}
