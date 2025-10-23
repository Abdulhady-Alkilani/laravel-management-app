<x-layouts.guest-layout title="تم إكمال التسجيل">
    <div class="text-center">
        <h2 class="text-success mb-4 fw-bold">تهانينا! تم إنشاء حسابك بنجاح.</h2>
        <p class="lead mb-4">هذه هي معلومات تسجيل الدخول الخاصة بك:</p>

        <div class="alert alert-info py-4 mb-4">
            <h4 class="alert-heading fw-bold">معلومات الدخول</h4>
            <hr>
            <p class="fs-5 mb-1"><strong>اسم المستخدم:</strong> <code>{{ $username }}</code></p>
            <p class="fs-5 mb-0"><strong>كلمة المرور:</strong> <code>{{ $password }}</code></p>
        </div>

        <div class="alert alert-warning mb-5 p-3">
            <p class="mb-0 fs-6">
                <i class="bi bi-exclamation-circle-fill text-danger"></i> <strong class="text-danger">ملاحظة هامة:</strong>
                الرجاء <strong class="text-danger">أخذ لقطة شاشة (Screenshot)</strong> لهذه الصفحة أو حفظ بيانات تسجيل الدخول في مكان آمن.
                لن تتمكن من رؤية كلمة المرور هذه مرة أخرى بعد مغادرة هذه الصفحة.
            </p>
        </div>

        {{-- زر الانتقال للوحة التحكم (بدون مؤقت) --}}
        <a href="{{ app(\App\Http\Controllers\CustomAuthController::class)->redirectBasedOnRole(Auth::user())->getTargetUrl() }}" class="btn btn-primary btn-lg px-5">
            الانتقال إلى لوحة التحكم
        </a>
    </div>

    {{-- إزالة السكريبت الخاص بالعد التنازلي --}}
</x-layouts.guest-layout>