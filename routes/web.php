<?php

use App\Http\Controllers\CustomAuthController;
use App\Http\Controllers\EngineerCvController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// مسارات المصادقة المخصصة (login, register, registration completion)
// هذه المسارات يجب أن تكون متاحة للضيوف فقط
Route::middleware('guest')->group(function () {
    Route::get('/register', [CustomAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [CustomAuthController::class, 'register']);

    Route::get('/login', [CustomAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [CustomAuthController::class, 'login']);

    // صفحة إكمال التسجيل يجب أن تكون محمية بـ 'auth' لأن المستخدم يكون قد سجل الدخول بالفعل
    // لذلك، سننقلها خارج مجموعة 'guest'
});

// المسار الجذري (الصفحة الأولى عند فتح التطبيق)
// يجب أن يعيد التوجيه إلى صفحة تسجيل الدخول إذا لم يكن مسجلاً للدخول.
// وإذا كان مسجلاً للدخول، يعيد توجيهه إلى لوحته الخاصة.
Route::get('/', function () {
    if (Auth::check()) {
        return app(CustomAuthController::class)->redirectBasedOnRole(Auth::user());
    }
    // إذا لم يكن مسجلاً للدخول، توجهه إلى صفحة تسجيل الدخول
    return redirect()->route('login');
});

// مسار تسجيل الخروج (للمسجلين فقط)
Route::post('/logout', [CustomAuthController::class, 'logout'])->middleware('auth')->name('logout');

// ------------------------------------------------------------------
// مسارات محمية (للمستخدمين المسجلين للدخول فقط)
// ------------------------------------------------------------------
Route::middleware(['auth'])->group(function () {

    // مسار إكمال التسجيل، الآن هو محمي بـ 'auth'
    Route::get('/register/completion', [CustomAuthController::class, 'showRegistrationCompletion'])->name('registration.completion');

    // مسار لتحديث الملف الشخصي للمستخدم
    Route::post('/profile/update', [EngineerCvController::class, 'updateProfile'])->name('profile.update');

    // مسارات تقديم السيرة الذاتية للمهندسين
    Route::get('/engineer/cv/create', [EngineerCvController::class, 'create'])->name('engineer.cv.create');
    Route::post('/engineer/cv', [EngineerCvController::class, 'store'])->name('engineer.cv.store');

    // لوحة تحكم عامة افتراضية (كخيار احتياطي إذا لم يتطابق أي دور)
    Route::get('/dashboard', function () {
        return view('dashboards.general-dashboard');
    })->name('dashboard');

    // لوحة تحكم للمدير
    Route::get('/manager/dashboard', function () {
        return view('dashboards.manager-dashboard');
    })->name('manager.dashboard');

    // لوحة تحكم للعامل
    Route::get('/worker/dashboard', function () {
        return view('dashboards.worker-dashboard');
    })->name('worker.dashboard');

    // لوحة تحكم للمستثمر
    Route::get('/investor/dashboard', function () {
        return view('dashboards.investor-dashboard');
    })->name('investor.dashboard');

    // لوحة تحكم لمشرف الورشة
    Route::get('/workshop-supervisor/dashboard', function () {
        return view('dashboards.workshop-supervisor-dashboard');
    })->name('workshop_supervisor.dashboard');

    // لوحة تحكم للمراجع
    Route::get('/reviewer/dashboard', function () {
        return view('dashboards.reviewer-dashboard');
    })->name('reviewer.dashboard');

    // لوحات تحكم المهندسين
    // هذه المسارات ستعرض لوحات التحكم الخاصة بهم، وقد تحتوي على منطق عرض نموذج CV إذا لم يكن لديهم
    Route::get('/architectural-engineer/dashboard', function () { return view('dashboards.architectural-engineer-dashboard'); })->name('architectural_engineer.dashboard');
    Route::get('/civil-engineer/dashboard', function () { return view('dashboards.civil-engineer-dashboard'); })->name('civil_engineer.dashboard');
    Route::get('/structural-engineer/dashboard', function () { return view('dashboards.structural-engineer-dashboard'); })->name('structural_engineer.dashboard');
    Route::get('/electrical-engineer/dashboard', function () { return view('dashboards.electrical-engineer-dashboard'); })->name('electrical_engineer.dashboard');
    Route::get('/mechanical-engineer/dashboard', function () { return view('dashboards.mechanical-engineer-dashboard'); })->name('mechanical_engineer.dashboard');
    Route::get('/geotechnical-engineer/dashboard', function () { return view('dashboards.geotechnical-engineer-dashboard'); })->name('geotechnical_engineer.dashboard');
    Route::get('/quantity-surveyor/dashboard', function () { return view('dashboards.quantity-surveyor-dashboard'); })->name('quantity_surveyor.dashboard');
    Route::get('/site-engineer/dashboard', function () { return view('dashboards.site-engineer-dashboard'); })->name('site_engineer.dashboard');
    Route::get('/environmental-engineer/dashboard', function () { return view('dashboards.environmental-engineer-dashboard'); })->name('environmental_engineer.dashboard');
    Route::get('/surveying-engineer/dashboard', function () { return view('dashboards.surveying-engineer-dashboard'); })->name('surveying_engineer.dashboard');

});

// ملاحظة: لوحة تحكم الـ Admin سيتم التوجيه إليها مباشرة عبر '/admin' (Filament)