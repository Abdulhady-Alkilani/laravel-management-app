{{--
    هذا الملف هو الـ View لمكون Livewire "CustomRegistration".
    يجب أن يحتوي على عنصر HTML جذري واحد فقط.
    الـ Layout (guest-layout) يتم تطبيقه من دالة `render()` في CustomRegistration.php.
    لذلك، يجب ألا يحتوي هذا الملف على `<x-layouts.guest-layout>` أو أي وسوم HTML أخرى مثل <html>, <head>, <body>.
--}}
<div>
    <h2 class="text-center mb-4 fw-bold text-primary">إنشاء حساب جديد</h2>
        
    @if (session('status'))
        <div class="alert alert-success mb-4">{{ session('status') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger mb-4">{{ session('error') }}</div>
    @endif
    
    <form wire:submit.prevent="register">
        <div class="row g-3">
            <!-- الاسم الأول والأخير -->
            <div class="col-md-6">
                <label for="first_name" class="form-label fw-bold">الاسم الأول <span class="text-danger">*</span></label>
                <input wire:model.defer="first_name" type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" required autofocus>
                @error('first_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label for="last_name" class="form-label fw-bold">الاسم الأخير <span class="text-danger">*</span></label>
                <input wire:model.defer="last_name" type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" required>
                @error('last_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- الدور -->
            <div class="col-12">
                <label for="role_id" class="form-label fw-bold">الدور الوظيفي <span class="text-danger">*</span></label>
                <select wire:model.defer="role_id" class="form-select @error('role_id') is-invalid @enderror" id="role_id" required>
                    <option value="">اختر دورك في النظام...</option>
                    @foreach($translatedRoles as $role)
                        <option value="{{ $role['id'] }}">{{ $role['name'] }}</option>
                    @endforeach
                </select>
                <div class="form-text">حدد طبيعة عملك لتخصيص واجهة النظام المناسبة لك.</div>
                @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <hr class="my-4">

            <!-- معلومات الدخول (اختيارية للتوليد التلقائي) -->
            <div class="col-md-6">
                 <label for="username" class="form-label fw-bold">اسم المستخدم</label>
                 <input wire:model.defer="username" type="text" class="form-control @error('username') is-invalid @enderror" id="username">
                 <div class="form-text text-success"><i class="bi bi-info-circle"></i> اختياري: اتركه فارغاً ليتم توليده تلقائياً من اسمك.</div>
                 @error('username') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label for="email" class="form-label fw-bold">البريد الإلكتروني</label>
                <input wire:model.defer="email" type="email" class="form-control @error('email') is-invalid @enderror" id="email">
                <div class="form-text text-success">اختياري: إذا لم تملك بريداً، سيتم إنشاء بريد افتراضي لك.</div>
                @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <!-- كلمة المرور -->
            <div class="col-md-6">
                <label for="password" class="form-label fw-bold">كلمة المرور <span class="text-danger">*</span></label>
                <input wire:model.defer="password" type="password" class="form-control @error('password') is-invalid @enderror" id="password" required>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label for="password_confirmation" class="form-label fw-bold">تأكيد كلمة المرور <span class="text-danger">*</span></label>
                <input wire:model.defer="password_confirmation" type="password" class="form-control" id="password_confirmation" required>
            </div>

            <hr class="my-4">

            <!-- معلومات إضافية -->
            <div class="col-md-6">
                <label for="phone_number" class="form-label fw-bold">رقم الهاتف</label>
                <input wire:model.defer="phone_number" type="text" class="form-control @error('phone_number') is-invalid @enderror" id="phone_number">
                @error('phone_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
                <label for="gender" class="form-label fw-bold">الجنس</label>
                <select wire:model.defer="gender" class="form-select @error('gender') is-invalid @enderror" id="gender">
                    <option value="">اختر...</option>
                    <option value="male">ذكر</option>
                    <option value="female">أنثى</option>
                </select>
                @error('gender') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <label for="address" class="form-label fw-bold">العنوان</label>
                <input wire:model.defer="address" type="text" class="form-control @error('address') is-invalid @enderror" id="address">
                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <label for="nationality" class="form-label fw-bold">الجنسية</label>
                <input wire:model.defer="nationality" type="text" class="form-control @error('nationality') is-invalid @enderror" id="nationality">
                @error('nationality') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
                <label for="profile_details" class="form-label fw-bold">نبذة مختصرة</label>
                <textarea wire:model.defer="profile_details" class="form-control @error('profile_details') is-invalid @enderror" id="profile_details" rows="2" placeholder="معلومات إضافية عن مهاراتك أو خبراتك..."></textarea>
                @error('profile_details') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="col-12 mt-4">
                <button type="submit" class="btn btn-primary w-100 py-2 fs-5">إنشاء الحساب</button>
            </div>
            <div class="col-12 text-center mt-3">
                <p>لديك حساب بالفعل؟ <a href="{{ route('login') }}" class="text-decoration-none fw-bold">سجل الدخول هنا</a></p>
            </div>
        </div>
    </form>
</div> 