<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نموذج طلب التوظيف - الخطوة 4</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
            <div class="alert alert-success alert-fixed fade show" role="alert"> {{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-fixed fade show" role="alert"> {{ session('error') }} <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button> </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0"> @foreach ($errors->all() as $error) <li>{{ $error }}</li> @endforeach </ul>
            </div>
        @endif

        <form action="{{ route('employee.apply.store.step4') }}" method="POST">
            @csrf

            <fieldset class="mb-4 p-3 border rounded">
                <legend class="float-none w-auto px-2 fs-5">مهاراتك ومؤهلاتك التعليمية</legend>

                <div class="mb-3">
                    <label class="form-label">اختر مهاراتك من القائمة</label>
                    <div class="row">
                        @foreach($skills as $skill)
                            <div class="col-sm-6 col-12">
                                <div class="form-check">
                                    <input class="form-check-input @error('selected_skills') is-invalid @enderror" type="checkbox" name="selected_skills[]" id="skill_{{ $skill->id }}" value="{{ $skill->id }}"
                                           {{ in_array($skill->id, old('selected_skills', $applicationData['step4']['selected_skills'] ?? [])) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="skill_{{ $skill->id }}">
                                        {{ $skill->name }}
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    @error('selected_skills') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                    @error('selected_skills.*') <div class="text-danger mt-1">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="new_skill" class="form-label">أضف مهارة جديدة (إن وجدت)</label>
                    <input type="text" class="form-control @error('new_skill') is-invalid @enderror" id="new_skill" name="new_skill" value="{{ old('new_skill', $applicationData['step4']['new_skill'] ?? '') }}" placeholder="مثال: تحليل البيانات">
                    <div class="form-text">إذا كانت لديك مهارة غير موجودة في القائمة أعلاه، يمكنك إضافتها هنا.</div>
                    @error('new_skill') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="mb-3">
                    <label for="education" class="form-label">المؤهلات العلمية والدورات التدريبية <span class="text-danger">*</span></label>
                    <textarea class="form-control @error('education') is-invalid @enderror" id="education" name="education" rows="5" placeholder="اذكر شهاداتك الأكاديمية (بكالوريوس، دبلوم، ثانوية)، والمؤسسة التعليمية، وسنة التخرج. بالإضافة إلى أي دورات تدريبية متخصصة أو شهادات مهنية حصلت عليها.">{{ old('education', $applicationData['step4']['education'] ?? '') }}</textarea>
                    <div class="form-text">مثال: بكالوريوس هندسة مدنية - جامعة دمشق 2019، دورة سلامة مهنية OSHA 2021.</div>
                    @error('education') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </fieldset>

            <div class="d-grid">
                <a href="{{ route('employee.apply.step3') }}" class="btn btn-secondary btn-lg mb-3">السابق</a>
                <button type="submit" class="btn btn-primary btn-lg">إرسال طلب التوظيف</button>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 