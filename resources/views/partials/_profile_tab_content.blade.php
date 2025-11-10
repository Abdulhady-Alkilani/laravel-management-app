@php
    // هذا الكود سيُنفذ في كل لوحة تحكم تضمن هذا الـ Partial
    $user = Auth::user();
    // التأكد من جلب بيانات المهارات إذا لزم الأمر، لكنها غير ضرورية هنا
@endphp

<h3 class="mb-3">معلومات ملفك الشخصي</h3>
<form action="{{ route('profile.update') }}" method="POST">
    @csrf
    <div class="row g-3 mb-3">
        <div class="col-md-6">
            <x-forms.input-label for="first_name" :value="__('الاسم الأول')" /><span class="text-danger">*</span>
            <x-forms.text-input id="first_name" type="text" name="first_name" value="{{ old('first_name', $user->first_name) }}" required />
            <x-forms.input-error :messages="$errors->get('first_name')" />
        </div>
        <div class="col-md-6">
            <x-forms.input-label for="last_name" :value="__('الاسم الأخير')" /><span class="text-danger">*</span>
            <x-forms.text-input id="last_name" type="text" name="last_name" value="{{ old('last_name', $user->last_name) }}" required />
            <x-forms.input-error :messages="$errors->get('last_name')" />
        </div>
        <div class="col-md-6">
            <x-forms.input-label for="email" :value="__('البريد الإلكتروني')" /><span class="text-danger">*</span>
            <x-forms.text-input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required />
            <x-forms.input-error :messages="$errors->get('email')" />
        </div>
        <div class="col-md-6">
            <x-forms.input-label for="username" :value="__('اسم المستخدم')" /><span class="text-danger">*</span>
            <x-forms.text-input id="username" type="text" name="username" value="{{ old('username', $user->username) }}" required />
            <x-forms.input-error :messages="$errors->get('username')" />
        </div>
        <div class="col-md-6">
            <x-forms.input-label for="phone_number" :value="__('رقم الهاتف')" />
            <x-forms.text-input id="phone_number" type="text" name="phone_number" value="{{ old('phone_number', $user->phone_number) }}" />
            <x-forms.input-error :messages="$errors->get('phone_number')" />
        </div>
        <div class="col-md-6">
            <x-forms.input-label for="gender" :value="__('الجنس')" />
            <select class="form-select" id="gender" name="gender">
                <option value="">اختر...</option>
                <option value="male" {{ (old('gender', $user->gender) == 'male') ? 'selected' : '' }}>ذكر</option>
                <option value="female" {{ (old('gender', $user->gender) == 'female') ? 'selected' : '' }}>أنثى</option>
            </select>
            <x-forms.input-error :messages="$errors->get('gender')" />
        </div>
        <div class="col-12">
            <x-forms.input-label for="address" :value="__('العنوان')" />
            <x-forms.text-input id="address" type="text" name="address" value="{{ old('address', $user->address) }}" />
            <x-forms.input-error :messages="$errors->get('address')" />
        </div>
        <div class="col-12">
            <x-forms.input-label for="nationality" :value="__('الجنسية')" />
            <x-forms.text-input id="nationality" type="text" name="nationality" value="{{ old('nationality', $user->nationality) }}" />
            <x-forms.input-error :messages="$errors->get('nationality')" />
        </div>
        <div class="col-12">
            <x-forms.input-label for="profile_details" :value="__('نبذة مختصرة')" />
            <textarea class="form-control" id="profile_details" name="profile_details" rows="3">{{ old('profile_details', $user->profile_details) }}</textarea>
            <x-forms.input-error :messages="$errors->get('profile_details')" />
        </div>
    </div>
    <div class="d-flex justify-content-end">
        <x-forms.primary-button>{{ __('حفظ التعديلات') }}</x-forms.primary-button>
    </div>
</form>

<hr class="my-5">

<div class="text-end">
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="btn btn-danger">{{ __('تسجيل الخروج') }}</button>
    </form>
</div>