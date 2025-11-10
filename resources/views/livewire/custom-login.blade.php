<div class="login-card">
    <h3 class="text-center mb-4 fw-bold text-primary">تسجيل الدخول</h3>

    @if (session('status'))
        <div class="alert alert-success mb-4">{{ session('status') }}</div>
    @endif
    @if (session('success'))
        <div class="alert alert-success mb-4">{{ session('success') }}</div>
    @endif

    <form wire:submit.prevent="authenticate">
        @csrf
        <div class="mb-3">
            <label for="username" class="form-label fw-bold">اسم المستخدم</label>
            <input wire:model="username" type="text" class="form-control @error('username') is-invalid @enderror" id="username" required autofocus placeholder="أدخل اسم المستخدم الخاص بك">
            @error('username')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password" class="form-label fw-bold">كلمة المرور</label>
            <input wire:model="password" type="password" class="form-control @error('password') is-invalid @enderror" id="password" required placeholder="••••••••">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4 form-check">
            <input wire:model="remember" type="checkbox" class="form-check-input" id="remember">
            <label class="form-check-label" for="remember">تذكرني على هذا الجهاز</label>
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">دخول</button>
        
        <div class="text-center">
            <a href="{{ route('register') }}" class="text-decoration-none">ليس لديك حساب؟ أنشئ حساباً جديداً</a>
        </div>
        <div class="text-center mt-2">
            <a href="{{ route('password.request') }}" class="text-decoration-none">نسيت كلمة المرور؟</a>
        </div>
    </form>
</div>