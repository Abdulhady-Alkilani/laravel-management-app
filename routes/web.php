<?php

use App\Http\Controllers\EmployeeApplicationController;
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
Route::middleware('guest')->group(function (): void {
    Route::get('/login', CustomLogin::class)->name('login');
    Route::get('/register', CustomRegistration::class)->name(name: 'register');
    Route::get('/forgot-password', CustomForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', CustomResetPassword::class)->name('password.reset');



    Route::prefix('employee-application')->group(function () {
        Route::get('/step-1', [EmployeeApplicationController::class, 'createStep1'])->name('employee.apply.step1');
        Route::post('/step-1', [EmployeeApplicationController::class, 'storeStep1'])->name('employee.apply.store.step1');

        Route::get('/step-2', [EmployeeApplicationController::class, 'createStep2'])->name('employee.apply.step2');
        Route::post('/step-2', [EmployeeApplicationController::class, 'storeStep2'])->name('employee.apply.store.step2');

        Route::get('/step-3', [EmployeeApplicationController::class, 'createStep3'])->name('employee.apply.step3');
        Route::post('/step-3', [EmployeeApplicationController::class, 'storeStep3'])->name('employee.apply.store.step3');

        Route::get('/step-4', [EmployeeApplicationController::class, 'createStep4'])->name('employee.apply.step4');
        Route::post('/step-4', [EmployeeApplicationController::class, 'storeStep4'])->name('employee.apply.store.step4');

        Route::get('/completion', [EmployeeApplicationController::class, 'completion'])->name('employee.apply.completion');

    });







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
        Route::get('/register/completion', [EmployeeApplicationController::class, 'completion'])->name('registration.completion');

    // مثال:
    // Route::post('/engineer/cv', [\App\Http\Controllers\EngineerCvController::class, 'store'])->name('engineer.cv.store');
    // Route::post('/profile/update', [\App\Http\Controllers\EngineerCvController::class, 'updateProfile'])->name('profile.update');
});