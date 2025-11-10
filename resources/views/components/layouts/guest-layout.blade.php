<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'المصادقة' }}</title>

    <!-- تضمين Bootstrap CSS (احتفظ به للصفحات غير Filament Forms) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- تضمين Bootstrap Icons (اختياري) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- تضمين Filament CSS (مهم جداً لعمل Filament Forms) -->
    @filamentStyles
    @livewireStyles

    <style>
        body {
            background-color: #f3f4f6; /* لون خلفية خفيف */
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
        }
        .auth-container {
            width: 100%;
            max-width: 800px; /* عرض أوسع لنموذج التسجيل */
            margin: 20px auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        }
        @media (max-width: 768px) {
            .auth-container {
                margin: 10px auto;
                padding: 20px;
                border-radius: 10px;
            }
        }
        
        /* تنسيقات Bootstrap الأساسية */
        .text-primary { color: #0d6efd !important; }
        .btn-primary { background-color: #0d6efd; border-color: #0d6efd; }
        .btn-primary:hover { background-color: #0b5ed7; border-color: #0a58ca; }
        .alert-danger, .alert-success, .alert-info, .alert-warning {
            margin-bottom: 1rem;
        }

        /* ---------------------------------------------------------------------- */
        /* تنسيقات مخصصة لـ Filament Forms لتبدو أفضل ضمن سياق Bootstrap/RTL */
        /* ---------------------------------------------------------------------- */
        .filament-register-form-wrapper {
            /* أي تنسيقات عامة للنموذج بأكمله */
        }

        /* إعادة ضبط margin لـ Filament field wrappers */
        .filament-register-form-wrapper .fi-fo-field-wrp {
            margin-bottom: 1rem; /* تباعد Bootstrap */
            padding: 0;
        }

        /* تنسيق الـ labels في Filament Forms */
        .filament-register-form-wrapper .fi-input-wrapper label {
            display: block;
            margin-bottom: 0.5rem; /* تباعد Bootstrap */
            font-weight: 600; /* Bold */
            color: #374151; /* لون نص Filament */
            font-size: 1rem; /* حجم الخط الافتراضي */
            line-height: 1.5;
        }

        /* تنسيق حقول الإدخال (TextInput, Select, Textarea) من Filament Forms */
        .filament-register-form-wrapper .fi-input-wrapper input,
        .filament-register-form-wrapper .fi-input-wrapper select,
        .filament-register-form-wrapper .fi-input-wrapper textarea {
            /* تطبيق تنسيقات مشابهة لـ Bootstrap Form Control */
            display: block;
            width: 100%;
            padding: 0.375rem 0.75rem;
            font-size: 1rem;
            font-weight: 400;
            line-height: 1.5;
            color: #212529; /* لون نص أسود */
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #ced4da; /* حافة Bootstrap */
            border-radius: 0.375rem; /* حواف مستديرة Bootstrap */
            transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
        }

        /* تنسيق التركيز على حقول الإدخال */
        .filament-register-form-wrapper .fi-input-wrapper input:focus,
        .filament-register-form-wrapper .fi-input-wrapper select:focus,
        .filament-register-form-wrapper .fi-input-wrapper textarea:focus {
            border-color: #86b7fe; /* لون أزرق Bootstrap */
            outline: 0;
            box-shadow: 0 0 0 0.25rem rgba(13,110,253,.25); /* ظل تركيز Bootstrap */
        }

        /* تنسيق الـ helper text */
        .filament-register-form-wrapper .fi-input-wrapper .fi-input-helper-text {
            font-size: 0.875rem;
            color: #6b7280; /* لون نص رمادي */
            margin-top: 0.25rem;
        }

        /* تنسيق أخطاء التحقق */
        .filament-register-form-wrapper .fi-input-wrapper .fi-input-error-message {
            color: #dc3545; /* لون أحمر Bootstrap */
            font-size: 0.875rem;
            margin-top: 0.25rem;
        }

        /* تنسيق الشبكة (columns) لـ Filament Forms */
        .filament-register-form-wrapper .grid {
            display: grid;
            gap: 1rem; /* تباعد Bootstrap */
            grid-template-columns: repeat(2, minmax(0, 1fr)); /* عمودين افتراضياً */
        }
        @media (max-width: 768px) {
            .filament-register-form-wrapper .grid {
                grid-template-columns: 1fr; /* عمود واحد على الأجهزة الصغيرة */
            }
        }

        /* ---------------------------------------------------------------------- */
        /* تنسيق أيقونة "عرض كلمة المرور" (revealable password) */
        /* ---------------------------------------------------------------------- */
        .filament-register-form-wrapper .fi-input-wrapper .fi-input-suffix {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            left: 0.75rem; /* لليمين في RTL */
            right: auto;
            color: #6b7280;
            z-index: 10;
        }
        html[dir='rtl'] .filament-register-form-wrapper .fi-input-wrapper .fi-input-suffix {
            left: auto; /* إعادة تعيين لـ RTL */
            right: 0.75rem; /* لليسار في RTL */
        }
        /* ضبط الحقل نفسه لترك مساحة للأيقونة */
        .filament-register-form-wrapper .fi-input-wrapper input.fi-input[type="password"],
        .filament-register-form-wrapper .fi-input-wrapper input.fi-input[type="text"][x-data="{ type: 'text' }"] {
            padding-left: 2.5rem; /* لترك مساحة للأيقونة في RTL */
            padding-right: 0.75rem; /* التباعد الأيمن الافتراضي */
        }
        html[dir='rtl'] .filament-register-form-wrapper .fi-input-wrapper input.fi-input[type="password"],
        html[dir='rtl'] .filament-register-form-wrapper .fi-input-wrapper input.fi-input[type="text"][x-data="{ type: 'text' }"] {
            padding-right: 2.5rem; /* لترك مساحة للأيقونة في RTL */
            padding-left: 0.75rem; /* التباعد الأيسر الافتراضي */
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="auth-container">
            {{ $slot }}
        </div>
    </div>
    <!-- تضمين Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- تضمين Filament JS (مهم جداً لعمل Filament Forms) -->
    @filamentScripts
    @livewireScripts
</body>
</html>