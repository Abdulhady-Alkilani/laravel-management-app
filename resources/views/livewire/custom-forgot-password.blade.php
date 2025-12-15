<div class="auth-card" style="max-width: 500px;">
    <h3 class="text-center mb-4 fw-bold text-primary">نسيت كلمة المرور؟</h3>
    <p class="text-center text-muted mb-4">لا تقلق! أدخل بريدك الإلكتروني وسنرسل لك رابطاً لإعادة تعيين كلمة المرور.</p>

    @if (session('status'))
        <div class="alert alert-success mb-4">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit.prevent="sendResetLink">
        <div class="mb-3">
            <label for="email" class="form-label fw-bold">البريد الإلكتروني</label>
            <input wire:model="email" type="email" class="form-control @error('email') is-invalid @enderror" id="email" required autofocus placeholder="example@example.com">
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">إرسال رابط إعادة تعيين كلمة المرور</button>

        <div class="text-center">
            <a href="{{ route('login') }}" class="text-decoration-none">العودة إلى تسجيل الدخول</a>
        </div>
    </form>
</div> 