<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نموذج طلب التوظيف - الخطوة 4</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- يمكنك إضافة مكتبة Select2 لتحسين تجربة اختيار المهارات -->
    {{-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" /> --}}

    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 600px; margin-top: 50px; margin-bottom: 50px; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .form-group label { font-weight: bold; }
        .progress-bar-container { height: 20px; background-color: #e9ecef; border-radius: 5px; margin-bottom: 30px; }
        .progress-bar { width: 100%; background-color: #007bff; border-radius: 5px; text-align: center; color: white; font-size: 0.8em; line-height: 20px; }
        .alert-fixed { position: fixed; top: 20px; right: 20px; z-index: 1050; max-width: 90%; }
        @media (max-width: 768px) { .alert-fixed { right: 5%; left: 5%; max-width: 90%; } }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4 text-center">نموذج طلب التوظيف</h2>
        <p class="text-center text-muted">الخطوة 4 من 4: تفاصيل السيرة الذاتية (الخطوة الأخيرة)</p>

        <div class="progress-bar-container mb-4">
            <div class="progress-bar" style="width: 100%;">100%</div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-fixed fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-fixed fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('employee.apply.store.step4') }}" method="POST">
            @csrf

            <fieldset class="mb-4 p-3 border rounded">
                <legend class="float-none w-auto px-2 fs-5">بيانات السيرة الذاتية (CV)</legend>

                <div class="mb-3">
                    <label for="selected_skills" class="form-label">اختر مهاراتك من القائمة</label>
                    <select class="form-select" id="selected_skills" name="selected_skills[]" multiple>
                        <option value="">-- اختر مهارة أو أكثر --</option>
                        @foreach($skills as $skill)
                            <option value="{{ $skill->id }}" {{ in_array($skill->id, old('selected_skills', $applicationData['step4']['selected_skills'] ?? [])) ? 'selected' : '' }}>
                                {{ $skill->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">يمكنك اختيار عدة مهارات بالضغط على مفتاح Ctrl (أو Command في Mac) أثناء النقر.</div>
                </div>

                <div class="mb-3">
                    <label for="new_skills" class="form-label">أضف مهارات جديدة (إن وجدت)</label>
                    <textarea class="form-control" id="new_skills" name="new_skills" rows="2">{{ old('new_skills', $applicationData['step4']['new_skills'] ?? '') }}</textarea>
                    <div class="form-text">إذا كانت لديك مهارات غير موجودة في القائمة أعلاه، يمكنك إضافتها هنا. الرجاء فصل كل مهارة بفاصلة (مثال: تصميم جرافيك، برمجة بايثون).</div>
                </div>

                <hr class="my-4">

                <div class="mb-3">
                    <label for="cv_experience" class="form-label">الخبرات المهنية</label>
                    <textarea class="form-control" id="cv_experience" name="cv_experience" rows="4">{{ old('cv_experience', $applicationData['step4']['cv_experience'] ?? '') }}</textarea>
                    <div class="form-text">اذكر خبراتك المهنية السابقة بدءًا من الأحدث، مع ذكر اسم الشركة، المسمى الوظيفي، وفترة العمل، وأبرز الإنجازات. (مثال: مهندس موقع في شركة [اسم الشركة] من 2020-2024، أشرفت على مشروع [اسم المشروع] بنجاح).</div>
                </div>

                <div class="mb-3">
                    <label for="cv_education" class="form-label">المؤهلات العلمية والدورات التدريبية</label>
                    <textarea class="form-control" id="cv_education" name="cv_education" rows="4">{{ old('cv_education', $applicationData['step4']['cv_education'] ?? '') }}</textarea>
                    <div class="form-text">اذكر شهاداتك الأكاديمية (بكالوريوس، دبلوم، ثانوية)، والمؤسسة التعليمية، وسنة التخرج. بالإضافة إلى أي دورات تدريبية متخصصة أو شهادات مهنية حصلت عليها. (مثال: بكالوريوس هندسة مدنية - جامعة دمشق 2019، دورة سلامة مهنية OSHA 2021).</div>
                </div>
            </fieldset>

            <div class="d-grid gap-2">
                <a href="{{ route('employee.apply.step3') }}" class="btn btn-secondary btn-lg">السابق</a>
                <button type="submit" class="btn btn-primary btn-lg">إرسال طلب التوظيف</button>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    {{-- إذا استخدمت Select2 --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script> --}}
    {{-- <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script> --}}
    {{-- <script>
        $(document).ready(function() {
            $('#selected_skills').select2({
                placeholder: "-- اختر مهارة أو أكثر --",
                allowClear: true
            });
        });
    </script> --}}
</body>
</html>