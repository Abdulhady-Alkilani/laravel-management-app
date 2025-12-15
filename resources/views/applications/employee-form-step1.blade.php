<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نموذج طلب التوظيف - الخطوة 1</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 600px; margin-top: 50px; margin-bottom: 50px; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .form-group label { font-weight: bold; }
        .progress-bar-container { height: 20px; background-color: #e9ecef; border-radius: 5px; margin-bottom: 30px; }
        .progress-bar { width: 25%; background-color: #007bff; border-radius: 5px; text-align: center; color: white; font-size: 0.8em; line-height: 20px; }
        .alert-fixed { position: fixed; top: 20px; right: 20px; z-index: 1050; max-width: 90%; }
        @media (max-width: 768px) { .alert-fixed { right: 5%; left: 5%; max-width: 90%; } }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="mb-4 text-center">نموذج طلب التوظيف</h2>
        <p class="text-center text-muted">الخطوة 1 من 4: المعلومات الأساسية ودورك المطلوب</p>

        <div class="progress-bar-container mb-4">
            <div class="progress-bar" style="width: 25%;">25%</div>
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

        <form action="{{ route('employee.apply.store.step1') }}" method="POST">
            @csrf

            <fieldset class="mb-4 p-3 border rounded">
                <legend class="float-none w-auto px-2 fs-5">معلوماتك الأساسية</legend>

                <div class="mb-3">
                    <label for="first_name" class="form-label">الاسم الأول <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="first_name" name="first_name" value="{{ old('first_name', $applicationData['step1']['first_name'] ?? '') }}" required>
                    <div class="form-text">الرجاء إدخال اسمك الأول كما يظهر في الوثائق الرسمية.</div>
                </div>

                <div class="mb-3">
                    <label for="last_name" class="form-label">الاسم الأخير <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="last_name" name="last_name" value="{{ old('last_name', $applicationData['step1']['last_name'] ?? '') }}" required>
                    <div class="form-text">الرجاء إدخال اسم عائلتك أو اسمك الأخير.</div>
                </div>

                <div class="mb-3">
                    <label for="role_id" class="form-label">الدور الذي تتقدم له <span class="text-danger">*</span></label>
                    <select class="form-select" id="role_id" name="role_id" required>
                        <option value="">اختر دورك</option>
                        @foreach($translatedRoles as $role)
                            <option value="{{ $role['id'] }}" {{ (old('role_id', $applicationData['step1']['role_id'] ?? '') == $role['id']) ? 'selected' : '' }}>
                                {{ $role['name'] }}
                            </option>
                        @endforeach
                    </select>
                    <div class="form-text">اختر الدور الذي ترغب في العمل به ضمن الشركة. (الأدوار الإدارية العليا مثل "مدير نظام" أو "مستثمر" غير متاحة هنا).</div>
                </div>
            </fieldset>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary btn-lg">التالي</button>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 