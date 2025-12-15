<div class="auth-card" style="max-width: 500px;">
    <h3 class="text-center mb-4 fw-bold text-primary">إعادة تعيين كلمة المرور</h3>
    
    @if (session('status'))
        <div class="alert alert-success mb-4">
            {{ session('status') }}
        </div>
    @endif

    <form wire:submit.prevent="resetPassword">
        <input type="hidden" wire:model="token">

        <div class="mb-3">
            <label for="email" class="form-label fw-bold">البريد الإلكتروني</label>
            <input wire:model="email" type="email" class="form-control @error('email') is-invalid @enderror" id="email" required autofocus placeholder="example@example.com" readonly>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label fw-bold">كلمة المرور الجديدة</label>
            <input wire:model="password" type="password" class="form-control @error('password') is-invalid @enderror" id="password" required placeholder="••••••••">
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label fw-bold">تأكيد كلمة المرور الجديدة</label>
            <input wire:model="password_confirmation" type="password" class="form-control" id="password_confirmation" required placeholder="••••••••">
        </div>

        <button type="submit" class="btn btn-primary w-100 mb-3">إعادة تعيين كلمة المرور</button>
    </form>
</div> 