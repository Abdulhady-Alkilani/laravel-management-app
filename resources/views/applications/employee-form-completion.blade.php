<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نموذج طلب التوظيف - اكتمال الطلب</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .container { max-width: 600px; margin-top: 50px; margin-bottom: 50px; background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        .alert-info { background-color: #d1ecf1; border-color: #bee5eb; color: #0c5460; }
        .alert-warning { background-color: #fff3cd; border-color: #ffeeba; color: #856404; }
        .alert-fixed { position: fixed; top: 20px; right: 20px; z-index: 1050; max-width: 90%; }
        @media (max-width: 768px) { .alert-fixed { right: 5%; left: 5%; max-width: 90%; } }
    </style>
</head>
<body>
    <div class="container text-center">
        <h2 class="mb-4 text-success">تم استلام طلبك بنجاح!</h2>
        <p class="lead">نشكرك على تقديم طلبك. سيتم مراجعته قريباً.</p>

        @if (session('success'))
            <div class="alert alert-success mt-4 alert-fixed fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="alert alert-info mt-4" role="alert">
            <h4 class="alert-heading">بيانات تسجيل الدخول الخاصة بك:</h4>
            <hr>
            <p class="mb-1"><strong>اسم المستخدم:</strong> <code>{{ $username }}</code></p>
            <p class="mb-0"><strong>كلمة المرور:</strong> <code>{{ $password }}</code></p>
        </div>

        <div class="alert alert-warning mt-3" role="alert">
            <p class="mb-0">
                <strong class="text-danger">ملاحظة هامة:</strong> الرجاء <strong class="text-danger">أخذ لقطة شاشة (Screenshot)</strong> لهذه الصفحة أو حفظ بيانات تسجيل الدخول في مكان آمن.
                لن تتمكن من رؤية كلمة المرور هذه مرة أخرى بعد مغادرة هذه الصفحة.
            </p>
        </div>

        <p class="mt-4">
            يمكنك الآن العودة إلى صفحة تقديم طلب جديد.
        </p>
        {{-- التعديل هنا: استخدام route('employee.apply.step1') بدلاً من '/' --}}
        <a href="{{ route('employee.apply.step1') }}" class="btn btn-primary btn-lg mt-3">تقديم طلب جديد</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>