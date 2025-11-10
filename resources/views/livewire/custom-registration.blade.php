{{--
    هذا الملف هو الـ View لمكون Livewire "CustomRegistration".
    يجب أن يحتوي على عنصر HTML جذري واحد فقط.
    الـ Layout (guest-layout) يتم تطبيقه من دالة `render()` في CustomRegistration.php.
    لذلك، يجب ألا يحتوي هذا الملف على `<x-layouts.guest-layout>` أو أي وسوم HTML أخرى مثل <html>, <head>, <body>.
--}}
<div class="filament-register-form-wrapper">
    <h2 class="text-center mb-4 fw-bold text-primary">إنشاء حساب جديد</h2>
        
    @if (session('status'))
        <div class="alert alert-success mb-4">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger mb-4">{{ session('error') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form wire:submit.prevent="register">
        {{-- Filament Form سيقوم بتوليد جميع الحقول وتنسيقاتها الأساسية --}}
        {{ $this->form }}

        <div class="col-12 mt-4">
            <button type="submit" class="btn btn-primary w-100 py-2 fs-5">إنشاء الحساب</button>
        </div>
        <div class="col-12 text-center mt-3">
            <p>لديك حساب بالفعل؟ <a href="{{ route('login') }}" class="text-decoration-none fw-bold">سجل الدخول هنا</a></p>
        </div>
    </form>
</div>

{{--
    تذكر: لا تضع أي وسم <style> أو <script> هنا.
    يجب أن تكون جميع التنسيقات في ملفات CSS (مثلاً app.css)
    أو ضمن وسم <style> في ملف الـ Layout الرئيسي `guest-layout.blade.php`.
--}}