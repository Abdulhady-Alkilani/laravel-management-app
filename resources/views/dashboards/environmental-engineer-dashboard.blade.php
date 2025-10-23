<x-layouts.app-layout title="لوحة تحكم المهندس البيئي">
    <div class="dashboard-card">
        <h2 class="text-primary mb-4">لوحة تحكم المهندس البيئي</h2>

        <ul class="nav nav-tabs mb-4" id="myTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-tab-pane" type="button" role="tab" aria-controls="profile-tab-pane" aria-selected="true">الملف الشخصي</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="dashboard-content-tab" data-bs-toggle="tab" data-bs-target="#dashboard-content-tab-pane" type="button" role="tab" aria-controls="dashboard-content-tab-pane" aria-selected="false">الرئيسية</button>
            </li>
            @php
                $user = Auth::user();
                $engineerRoles = [
                    'Architectural Engineer', 'Civil Engineer', 'Structural Engineer', 'Electrical Engineer',
                    'Mechanical Engineer', 'Geotechnical Engineer', 'Quantity Surveyor', 'Site Engineer',
                    'Environmental Engineer', 'Surveying Engineer'
                ];
                $isEngineer = false;
                foreach ($engineerRoles as $roleName) {
                    if ($user->hasRole($roleName)) {
                        $isEngineer = true;
                        break;
                    }
                }
                $hasCv = $user->cvs()->exists();
                $allSkills = \App\Models\Skill::orderBy('name')->get();
            @endphp
            @if($isEngineer && !$hasCv)
            <li class="nav-item" role="presentation">
                <button class="nav-link text-warning fw-bold" id="cv-tab" data-bs-toggle="tab" data-bs-target="#cv-tab-pane" type="button" role="tab" aria-controls="cv-tab-pane" aria-selected="false">تقديم السيرة الذاتية <i class="bi bi-exclamation-triangle-fill"></i></button>
            </li>
            @endif
        </ul>

        <div class="tab-content" id="myTabContent">
            {{-- Profile Tab Pane --}}
            <div class="tab-pane fade show active" id="profile-tab-pane" role="tabpanel" aria-labelledby="profile-tab" tabindex="0">
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
            </div>

            {{-- Dashboard Content Tab Pane --}}
            <div class="tab-pane fade" id="dashboard-content-tab-pane" role="tabpanel" aria-labelledby="dashboard-content-tab" tabindex="0">
                <h3 class="mb-3">مرحبًا بك في لوحة تحكم المهندس البيئي!</h3>
                <p>هنا يمكنك إدارة الجوانب البيئية للمشاريع لضمان الامتثال للمعايير.</p>
                <ul>
                    <li>تقييم الأثر البيئي للمشاريع.</li>
                    <li>تطوير خطط إدارة النفايات والتلوث.</li>
                    <li>ضمان الامتثال للقوانين واللوائح البيئية.</li>
                </ul>
            </div>

            {{-- CV Application Tab Pane (Conditional for engineers without CV) --}}
            @if($isEngineer && !$hasCv)
            <div class="tab-pane fade" id="cv-tab-pane" role="tabpanel" aria-labelledby="cv-tab" tabindex="0">
                <h3 class="text-primary mb-4">تقديم سيرتك الذاتية</h3>
                <p class="text-muted mb-4">الرجاء إكمال بيانات سيرتك الذاتية. هذه المعلومات أساسية لتفعيل حسابك بالكامل.</p>

                <form action="{{ route('engineer.cv.store') }}" method="POST">
                    @csrf

                    <fieldset class="mb-4 p-3 border rounded">
                        <legend class="float-none w-auto px-2 fs-5">البيانات الشخصية والخبرات</legend>

                        <div class="mb-3">
                            <x-forms.input-label for="profile_details_cv" :value="__('نبذة عنك (تفاصيل الملف الشخصي)')" />
                            <textarea class="form-control" id="profile_details_cv" name="profile_details" rows="3" placeholder="شاركنا نبذة مختصرة عن خلفيتك المهنية، اهتماماتك، أو أي معلومات إضافية ترى أنها مهمة.">{{ old('profile_details', $user->profile_details ?? '') }}</textarea>
                            <div class="form-text">هذه النبذة ستظهر في ملفك الشخصي.</div>
                            <x-forms.input-error :messages="$errors->get('profile_details')" />
                        </div>

                        <div class="mb-3">
                            <x-forms.input-label for="experience" :value="__('الخبرات المهنية')" /><span class="text-danger">*</span>
                            <textarea class="form-control" id="experience" name="experience" rows="5" placeholder="اذكر خبراتك المهنية السابقة بدءًا من الأحدث، مع ذكر اسم الشركة، المسمى الوظيفي، وفترة العمل، وأبرز الإنجازات.">{{ old('experience') }}</textarea>
                            <div class="form-text">مثال: مهندس موقع في شركة [اسم الشركة] من 2020-2024، أشرفت على مشروع [اسم المشروع] بنجاح.</div>
                            <x-forms.input-error :messages="$errors->get('experience')" />
                        </div>

                        <div class="mb-3">
                            <x-forms.input-label for="education" :value="__('المؤهلات العلمية والدورات التدريبية')" /><span class="text-danger">*</span>
                            <textarea class="form-control" id="education" name="education" rows="5" placeholder="اذكر شهاداتك الأكاديمية (بكالوريوس، دبلوم، ثانوية)، والمؤسسة التعليمية، وسنة التخرج. بالإضافة إلى أي دورات تدريبية متخصصة أو شهادات مهنية حصلت عليها.">{{ old('education') }}</textarea>
                            <div class="form-text">مثال: بكالوريوس هندسة مدنية - جامعة دمشق 2019، دورة سلامة مهنية OSHA 2021.</div>
                            <x-forms.input-error :messages="$errors->get('education')" />
                        </div>
                    </fieldset>

                    <fieldset class="mb-4 p-3 border rounded">
                        <legend class="float-none w-auto px-2 fs-5">مهاراتك</legend>

                        <div class="mb-3">
                            <x-forms.input-label for="selected_skills" :value="__('اختر مهاراتك من القائمة')" />
                            <select class="form-select" id="selected_skills" name="selected_skills[]" multiple>
                                <option value="">-- اختر مهارة أو أكثر --</option>
                                @foreach($allSkills as $skill)
                                    <option value="{{ $skill->id }}" {{ in_array($skill->id, old('selected_skills', [])) ? 'selected' : '' }}>
                                        {{ $skill->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">يمكنك اختيار عدة مهارات بالضغط على مفتاح Ctrl (أو Command في Mac) أثناء النقر.</div>
                            <x-forms.input-error :messages="$errors->get('selected_skills')" />
                        </div>

                        <div class="mb-3">
                            <x-forms.input-label for="new_skills" :value="__('أضف مهارات جديدة (إن وجدت)')" />
                            <textarea class="form-control" id="new_skills" name="new_skills" rows="2" placeholder="الرجاء فصل كل مهارة بفاصلة، مثال: تصميم جرافيك، برمجة بايثون">{{ old('new_skills') }}</textarea>
                            <div class="form-text">إذا كانت لديك مهارات غير موجودة في القائمة أعلاه، يمكنك إضافتها هنا.</div>
                            <x-forms.input-error :messages="$errors->get('new_skills')" />
                        </div>
                    </fieldset>

                    <div class="d-grid">
                        <x-forms.primary-button class="py-2 fs-5">{{ __('إرسال السيرة الذاتية') }}</x-forms.primary-button>
                    </div>
                </form>
            </div>
            @endif
        </div>
    </div>
</x-layouts.app-layout>