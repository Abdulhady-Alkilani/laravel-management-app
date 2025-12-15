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
Route::middleware('guest')->group(function (): void {
    Route::get('/login', CustomLogin::class)->name('login');
    Route::get('/register', CustomRegistration::class)->name(name: 'register');
    Route::get('/forgot-password', CustomForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', CustomResetPassword::class)->name('password.reset');

    // مسارات تطبيق الموظف (إذا كانت مخصصة لغير المسجلين)
    Route::prefix('employee-application')->group(function () {
        // ... (مساراتك الحالية هنا) ...
        Route::get('/step-1', [\App\Http\Controllers\EmployeeApplicationController::class, 'createStep1'])->name('employee.apply.step1');
        Route::post('/step-1', [\App\Http\Controllers\EmployeeApplicationController::class, 'storeStep1'])->name('employee.apply.store.step1');
        Route::get('/step-2', [\App\Http\Controllers\EmployeeApplicationController::class, 'createStep2'])->name('employee.apply.step2');
        Route::post('/step-2', [\App\Http\Controllers\EmployeeApplicationController::class, 'storeStep2'])->name('employee.apply.store.step2');
        Route::get('/step-3', [\App\Http\Controllers\EmployeeApplicationController::class, 'createStep3'])->name('employee.apply.step3');
        Route::post('/step-3', [\App\Http\Controllers\EmployeeApplicationController::class, 'storeStep3'])->name('employee.apply.store.step3');
        Route::get('/step-4', [\App\Http\Controllers\EmployeeApplicationController::class, 'createStep4'])->name('employee.apply.step4');
        Route::post('/step-4', [\App\Http\Controllers\EmployeeApplicationController::class, 'storeStep4'])->name('employee.apply.store.step4');
        Route::get('/completion', [\App\Http\Controllers\EmployeeApplicationController::class, 'completion'])->name('employee.apply.completion');
    });
});

// المسار الجذري للموقع
Route::get('/', function () {
    if (Auth::check()) {
        // <== التعديل الرئيسي هنا: توجيه المستخدم المسجل للدخول إلى لوحته مباشرة
        // نستخدم نفس منطق التوجيه الموجود في مكونات Livewire
        $user = Auth::user();
        if ($user->hasRole('Admin')) {
            return redirect('/admin');
        }
        if ($user->hasRole('Manager')) {
            return redirect('/manager');
        }
        if ($user->hasRole('Workshop Supervisor')) {
            return redirect('/workshop-supervisor');
        }
        if ($user->hasRole('Worker')) {
            return redirect('/worker');
        }
        if ($user->hasRole('Investor')) {
            return redirect('/investor');
        }
        if ($user->hasRole('Reviewer')) {
            return redirect('/reviewer');
        }
        
        $engineerRoles = [
            'Architectural Engineer', 'Civil Engineer', 'Structural Engineer', 'Electrical Engineer',
            'Mechanical Engineer', 'Geotechnical Engineer', 'Quantity Surveyor', 'Site Engineer',
            'Environmental Engineer', 'Surveying Engineer'
        ];
        foreach ($engineerRoles as $roleName) {
            if ($user->hasRole($roleName)) {
                return redirect('/engineer');
            }
        }
        
        if ($user->hasRole('Service Proposer')) {
            return redirect('/service-proposer');
        }
        //Fallback: إذا كان مسجلاً للدخول ولكن ليس لديه دور موجه صراحةً، أرسله إلى لوحة Filament Admin كـ fallback (أو أي مكان آمن آخر).
        return redirect('/admin'); 

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


// مسارات لوحات التحكم الخاصة بكل دور (محمية بـ 'auth' middleware)
Route::middleware(['auth'])->group(function () {
    Route::get('/registration/completion', [\App\Http\Controllers\EmployeeApplicationController::class, 'completion'])->name('registration.completion');
    // تأكد من أن EmployeeApplicationController::completion لا تقوم بإعادة توجيه المستخدم إلى /login إذا كان مسجلاً للدخول.
    // يجب أن تعرض صفحة إكمال التسجيل للمستخدم المسجل للدخول.
}); 