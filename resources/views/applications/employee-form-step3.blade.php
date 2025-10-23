<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نموذج طلب التوظيف - الخطوة 3</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 600px; margin-top: 50px; margin-bottom: 50px; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .form-group label { font-weight: bold; }
        .progress-bar-container { height: 20px; background-color: #e9ecef; border-radius: 5px; margin-bottom: 30px; }
        .progress-bar { width: 75%; background-color: #007bff; border-radius: 5px; text-align: center; color: white; font-size: 0.8em; line-height: 20px; }
        .alert-fixed { position: fixed; top: 20px; right: 20px; z-index: 1050; max-width: 90%; }
        @media (max-width: 768px) { .alert-fixed { right: 5%; left: 5%; max-width: 90%; } }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4 text-center">نموذج طلب التوظيف</h2>
        <p class="text-center text-muted">الخطوة 3 من 4: تعيين كلمة المرور والمعلومات الشخصية</p>

        <div class="progress-bar-container mb-4">
            <div class="progress-bar" style="width: 75%;">75%</div>
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

        <form action="{{ route('employee.apply.store.step3') }}" method="POST">
            @csrf

            <fieldset class="mb-4 p-3 border rounded">
                <legend class="float-none w-auto px-2 fs-5">بيانات تسجيل الدخول و معلوماتك الشخصية</legend>

                <div class="mb-3">
                    <label for="password" class="form-label">كلمة المرور <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="password" name="password" required>
                    <div class="form-text">يجب أن تكون كلمة المرور قوية (8 أحرف على الأقل، تتضمن حروفًا كبيرة وصغيرة، أرقامًا، ورموزًا خاصة).</div>
                </div>
                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">تأكيد كلمة المرور <span class="text-danger">*</span></label>
                    <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    <div class="form-text">الرجاء إعادة إدخال كلمة المرور للتأكيد.</div>
                </div>

                <hr class="my-4">

                <div class="row mb-3">
                    <div class="col-md-6 col-12 mb-3 mb-md-0">
                        <label for="gender" class="form-label">الجنس</label>
                        <select class="form-select" id="gender" name="gender">
                            <option value="">اختر الجنس</option>
                            <option value="male" {{ (old('gender', $applicationData['step3']['gender'] ?? '') == 'male') ? 'selected' : '' }}>ذكر</option>
                            <option value="female" {{ (old('gender', $applicationData['step3']['gender'] ?? '') == 'female') ? 'selected' : '' }}>أنثى</option>
                        </select>
                        <div class="form-text">مساعدتنا على فهم التركيبة السكانية لمقدمي الطلبات.</div>
                    </div>
                    <div class="col-md-6 col-12">
                        <label for="nationality" class="form-label">الجنسية</label>
                        <input type="text" class="form-control" id="nationality" name="nationality" value="{{ old('nationality', $applicationData['step3']['nationality'] ?? '') }}">
                        <div class="form-text">الرجاء إدخال جنسيتك (مثال: سوري، مصري، أردني).</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label for="address" class="form-label">العنوان</label>
                    <input type="text" class="form-control" id="address" name="address" value="{{ old('address', $applicationData['step3']['address'] ?? '') }}">
                    <div class="form-text">عنوان إقامتك الحالي بالكامل (مثال: حماة، حي الفداء، شارع العروبة، بناء رقم 12).</div>
                </div>

                <div class="mb-3">
                    <label for="phone_number" class="form-label">رقم الهاتف</label>
                    <input type="text" class="form-control" id="phone_number" name="phone_number" value="{{ old('phone_number', $applicationData['step3']['phone_number'] ?? '') }}">
                    <div class="form-text">رقم هاتفك المحمول للتواصل السريع والضروري (مثال: 0930123456).</div>
                </div>

                <div class="mb-3">
                    <label for="profile_details" class="form-label">نبذة عنك (تفاصيل الملف الشخصي)</label>
                    <textarea class="form-control" id="profile_details" name="profile_details" rows="3">{{ old('profile_details', $applicationData['step3']['profile_details'] ?? '') }}</textarea>
                    <div class="form-text">شاركنا نبذة مختصرة عن خلفيتك المهنية، اهتماماتك، أو أي معلومات إضافية ترى أنها مهمة وتدعم طلبك (مثال: مهندس مدني بخبرة 5 سنوات في مشاريع البناء، متحمس للعمل ضمن فريق).</div>
                </div>
            </fieldset>

            <div class="d-grid gap-2">
                <a href="{{ route('employee.apply.step2') }}" class="btn btn-secondary btn-lg">السابق</a>
                <button type="submit" class="btn btn-primary btn-lg">التالي</button>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>