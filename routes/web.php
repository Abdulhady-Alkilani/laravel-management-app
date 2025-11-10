<?php

use App\Livewire\CustomLogin;
use App\Livewire\CustomRegistration;
use App\Livewire\CustomForgotPassword;
use App\Livewire\CustomResetPassword;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// مسارات المصادقة المشتركة (Livewire Components)
Route::middleware('guest')->group(function () {
    Route::get('/login', CustomLogin::class)->name('login');
    Route::get('/register', CustomRegistration::class)->name('register');
    Route::get('/forgot-password', CustomForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', CustomResetPassword::class)->name('password.reset');
});

// المسار الجذري للموقع
Route::get('/', function () {
    if (Auth::check()) {
        // إذا كان مسجلاً للدخول، توجهه إلى /admin (لوحة Filament Admin)
        // هذا مجرد توجيه أولي، Filament سيتحقق من الأذونات ويقوم بالتوجيه الصحيح إذا كان المستخدم ليس Admin
      //  return redirect('/admin');
    }
    // إذا لم يكن مسجلاً للدخول، توجه إلى صفحة تسجيل الدخول
    return redirect()->route('login');
})->name('home');

// مسار تسجيل الخروج (للمسجلين فقط)
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('login');
})->middleware('auth')->name('logout');


// ------------------------------------------------------------------
// مسارات لوحات التحكم الخاصة بكل دور (محمية بـ 'auth' middleware)
// ------------------------------------------------------------------
// هذه المسارات تُنشأ تلقائياً بواسطة Filament Panels
// لا تحتاج لتعريفها هنا يدوياً إلا إذا كان لديك Pages/Resources خارج نطاق الـ Panels
// في حالتنا، كل شيء داخل Filament، لذا هذه المجموعة يمكن أن تكون فارغة
// أو تحتوي على مسارات خاصة مثل تحديث الملف الشخصي العام أو تقديم الـ CV لو كانت خارج Panels
Route::middleware(['auth'])->group(function () {
    // مثال:
    // Route::post('/engineer/cv', [\App\Http\Controllers\EngineerCvController::class, 'store'])->name('engineer.cv.store');
    // Route::post('/profile/update', [\App\Http\Controllers\EngineerCvController::class, 'updateProfile'])->name('profile.update');
});