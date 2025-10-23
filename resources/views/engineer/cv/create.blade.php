<x-layouts.app-layout title="تقديم السيرة الذاتية">
    <div class="dashboard-card">
        <h2 class="text-primary mb-4 text-center">تقديم السيرة الذاتية</h2>
        <p class="text-center text-muted mb-4">الرجاء إكمال بيانات سيرتك الذاتية. هذه المعلومات أساسية لتفعيل حسابك بالكامل.</p>

        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if (session('warning'))
            <div class="alert alert-warning">{{ session('warning') }}</div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('engineer.cv.store') }}" method="POST">
            @csrf

            <fieldset class="mb-4 p-3 border rounded">
                <legend class="float-none w-auto px-2 fs-5">البيانات الشخصية والخبرات</legend>

                <div class="mb-3">
                    <x-forms.input-label for="profile_details" :value="__('نبذة عنك (تفاصيل الملف الشخصي)')" />
                    <textarea class="form-control" id="profile_details" name="profile_details" rows="3" placeholder="شاركنا نبذة مختصرة عن خلفيتك المهنية، اهتماماتك، أو أي معلومات إضافية ترى أنها مهمة.">{{ old('profile_details', $oldInput['profile_details'] ?? ($user->profile_details ?? '')) }}</textarea>
                    <div class="form-text">هذه النبذة ستظهر في ملفك الشخصي.</div>
                    <x-forms.input-error :messages="$errors->get('profile_details')" />
                </div>

                <div class="mb-3">
                    <x-forms.input-label for="experience" :value="__('الخبرات المهنية')" /><span class="text-danger">*</span>
                    <textarea class="form-control" id="experience" name="experience" rows="5" placeholder="اذكر خبراتك المهنية السابقة بدءًا من الأحدث، مع ذكر اسم الشركة، المسمى الوظيفي، وفترة العمل، وأبرز الإنجازات.">{{ old('experience', $oldInput['experience'] ?? '') }}</textarea>
                    <div class="form-text">مثال: مهندس موقع في شركة [اسم الشركة] من 2020-2024، أشرفت على مشروع [اسم المشروع] بنجاح.</div>
                    <x-forms.input-error :messages="$errors->get('experience')" />
                </div>

                <div class="mb-3">
                    <x-forms.input-label for="education" :value="__('المؤهلات العلمية والدورات التدريبية')" /><span class="text-danger">*</span>
                    <textarea class="form-control" id="education" name="education" rows="5" placeholder="اذكر شهاداتك الأكاديمية (بكالوريوس، دبلوم، ثانوية)، والمؤسسة التعليمية، وسنة التخرج. بالإضافة إلى أي دورات تدريبية متخصصة أو شهادات مهنية حصلت عليها.">{{ old('education', $oldInput['education'] ?? '') }}</textarea>
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
                        @foreach($skills as $skill)
                            <option value="{{ $skill->id }}" {{ in_array($skill->id, old('selected_skills', $oldInput['selected_skills'] ?? [])) ? 'selected' : '' }}>
                                {{ $skill->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">يمكنك اختيار عدة مهارات بالضغط على مفتاح Ctrl (أو Command في Mac) أثناء النقر.</div>
                    <x-forms.input-error :messages="$errors->get('selected_skills')" />
                </div>

                <div class="mb-3">
                    <x-forms.input-label for="new_skills" :value="__('أضف مهارات جديدة (إن وجدت)')" />
                    <textarea class="form-control" id="new_skills" name="new_skills" rows="2" placeholder="الرجاء فصل كل مهارة بفاصلة، مثال: تصميم جرافيك، برمجة بايثون">{{ old('new_skills', $oldInput['new_skills'] ?? '') }}</textarea>
                    <div class="form-text">إذا كانت لديك مهارات غير موجودة في القائمة أعلاه، يمكنك إضافتها هنا.</div>
                    <x-forms.input-error :messages="$errors->get('new_skills')" />
                </div>
            </fieldset>

            <div class="d-grid">
                <x-forms.primary-button class="py-2 fs-5">{{ __('إرسال السيرة الذاتية') }}</x-forms.primary-button>
            </div>
        </form>
    </div>
</x-layouts.app-layout>