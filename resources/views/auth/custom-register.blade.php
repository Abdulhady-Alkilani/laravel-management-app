<x-layouts.guest-layout title="إنشاء حساب جديد">
    <h2 class="text-center mb-4 fw-bold text-primary">إنشاء حساب جديد</h2>
            
    @if (session('status'))
        <div class="alert alert-success mb-4">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('register') }}" method="POST">
        @csrf
        <div class="row g-3">
            <!-- الاسم الأول والأخير -->
            <div class="col-md-6">
                <x-forms.input-label for="first_name" :value="__('الاسم الأول')" /><span class="text-danger">*</span>
                <x-forms.text-input id="first_name" type="text" name="first_name" :value="old('first_name')" required autofocus />
                <x-forms.input-error :messages="$errors->get('first_name')" />
            </div>
            <div class="col-md-6">
                <x-forms.input-label for="last_name" :value="__('الاسم الأخير')" /><span class="text-danger">*</span>
                <x-forms.text-input id="last_name" type="text" name="last_name" :value="old('last_name')" required />
                <x-forms.input-error :messages="$errors->get('last_name')" />
            </div>

            <!-- الدور -->
            <div class="col-12">
                <x-forms.input-label for="role_id" :value="__('الدور الوظيفي')" /><span class="text-danger">*</span>
                <select class="form-select" id="role_id" name="role_id" required>
                    <option value="">اختر دورك في النظام...</option>
                    @foreach($translatedRoles as $role)
                        <option value="{{ $role['id'] }}" {{ old('role_id') == $role['id'] ? 'selected' : '' }}>{{ $role['name'] }}</option>
                    @endforeach
                </select>
                <div class="form-text">حدد طبيعة عملك لتخصيص واجهة النظام المناسبة لك.</div>
                <x-forms.input-error :messages="$errors->get('role_id')" />
            </div>

            <hr class="my-4">

            <!-- معلومات الدخول (اختيارية للتوليد التلقائي) -->
            <div class="col-md-6">
                 <x-forms.input-label for="username" :value="__('اسم المستخدم')" />
                 <x-forms.text-input id="username" type="text" name="username" :value="old('username')" />
                 <div class="form-text text-success"><i class="bi bi-info-circle"></i> اختياري: اتركه فارغاً ليتم توليده تلقائياً من اسمك.</div>
                 <x-forms.input-error :messages="$errors->get('username')" />
            </div>
            <div class="col-md-6">
                <x-forms.input-label for="email" :value="__('البريد الإلكتروني')" />
                <x-forms.text-input id="email" type="email" name="email" :value="old('email')" />
                <div class="form-text text-success">اختياري: إذا لم تملك بريداً، سيتم إنشاء بريد افتراضي لك.</div>
                <x-forms.input-error :messages="$errors->get('email')" />
            </div>

            <!-- كلمة المرور -->
            <div class="col-md-6">
                <x-forms.input-label for="password" :value="__('كلمة المرور')" /><span class="text-danger">*</span>
                <x-forms.text-input id="password" type="password" name="password" required />
                <x-forms.input-error :messages="$errors->get('password')" />
            </div>
            <div class="col-md-6">
                <x-forms.input-label for="password_confirmation" :value="__('تأكيد كلمة المرور')" /><span class="text-danger">*</span>
                <x-forms.text-input id="password_confirmation" type="password" name="password_confirmation" required />
                <x-forms.input-error :messages="$errors->get('password_confirmation')" />
            </div>

            <hr class="my-4">

            <!-- معلومات إضافية -->
            <div class="col-md-6">
                <x-forms.input-label for="phone_number" :value="__('رقم الهاتف')" />
                <x-forms.text-input id="phone_number" type="text" name="phone_number" :value="old('phone_number')" />
                <x-forms.input-error :messages="$errors->get('phone_number')" />
            </div>
            <div class="col-md-6">
                <x-forms.input-label for="gender" :value="__('الجنس')" />
                <select class="form-select" id="gender" name="gender">
                    <option value="">اختر...</option>
                    <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>ذكر</option>
                    <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>أنثى</option>
                </select>
                <x-forms.input-error :messages="$errors->get('gender')" />
            </div>
            <div class="col-12">
                <x-forms.input-label for="profile_details" :value="__('نبذة مختصرة')" />
                <textarea class="form-control" id="profile_details" name="profile_details" rows="2" placeholder="معلومات إضافية عن مهاراتك أو خبراتك...">{{ old('profile_details') }}</textarea>
                <x-forms.input-error :messages="$errors->get('profile_details')" />
            </div>

            <div class="col-12 mt-4">
                <x-forms.primary-button class="w-100 py-2 fs-5">{{ __('إنشاء الحساب') }}</x-forms.primary-button>
            </div>
            <div class="col-12 text-center mt-3">
                <p>لديك حساب بالفعل؟ <a href="{{ route('login') }}" class="text-decoration-none fw-bold">سجل الدخول هنا</a></p>
            </div>
        </div>
    </form>
</x-layouts.guest-layout>