<x-layouts.guest-layout title="تسجيل الدخول">
    <h3 class="text-center mb-4 fw-bold text-primary">تسجيل الدخول</h3>

    @if (session('status'))
        <div class="alert alert-success mb-4">{{ session('status') }}</div>
    @endif
    @if (session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('login') }}" method="POST">
        @csrf
        <div class="mb-3">
            <x-forms.input-label for="username" :value="__('اسم المستخدم')" />
            <x-forms.text-input id="username" type="text" name="username" :value="old('username')" required autofocus placeholder="أدخل اسم المستخدم الخاص بك" class="@error('username') is-invalid @enderror" />
            <x-forms.input-error :messages="$errors->get('username')" />
        </div>

        <div class="mb-4">
            <x-forms.input-label for="password" :value="__('كلمة المرور')" />
            <x-forms.text-input id="password" type="password" name="password" required placeholder="••••••••" class="@error('password') is-invalid @enderror" />
            <x-forms.input-error :messages="$errors->get('password')" />
        </div>

        <div class="mb-4 form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label" for="remember">تذكرني على هذا الجهاز</label>
        </div>

        <x-forms.primary-button class="w-100 mb-3">دخول</x-forms.primary-button>
        
        <div class="text-center">
            <a href="{{ route('register') }}" class="text-decoration-none">ليس لديك حساب؟ أنشئ حساباً جديداً</a>
        </div>
    </form>
</x-layouts.guest-layout>