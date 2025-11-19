<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'المصادقة' }}</title>

    <!-- تضمين Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- تضمين Bootstrap Icons (اختياري) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    {{-- Filament Styles لم تعد ضرورية هنا لأن CustomRegistration لم يعد يستخدم Filament Forms --}}
    {{-- @filamentStyles --}}
    @livewireStyles {{-- Livewire Styles لا تزال ضرورية --}}

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
        /* لا تنسيقات مخصصة لـ Filament Forms هنا بعد الآن */
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
    {{-- Filament Scripts لم تعد ضرورية هنا --}}
    {{-- @filamentScripts --}}
    @livewireScripts {{-- Livewire Scripts لا تزال ضرورية --}}
</body>
</html>