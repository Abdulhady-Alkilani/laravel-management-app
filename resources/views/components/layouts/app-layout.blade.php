<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'لوحة التحكم' }}</title>
    <!-- تضمين Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- تضمين Bootstrap Icons (اختياري) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f3f4f6; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .navbar-custom { background-color: #4f46e5; }
        .navbar-custom .navbar-brand, .navbar-custom .nav-link, .navbar-custom .dropdown-toggle { color: white !important; }
        .navbar-custom .nav-link:hover, .navbar-custom .dropdown-toggle:hover { color: #d1d5db !important; }
        .main-content { padding: 20px; }
        .dashboard-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); margin-bottom: 20px; }
        @media (max-width: 768px) { .dashboard-card { padding: 15px; } .main-content { padding: 10px; } }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ url('/') }}">نظام إدارة المشاريع</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ Auth::check() ? app(\App\Http\Controllers\CustomAuthController::class)->redirectBasedOnRole(Auth::user()) : route('login') }}">الرئيسية</a>
                    </li>
                    @auth
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
                        @endphp

                        @if($isEngineer && !$user->cvs()->exists())
                        <li class="nav-item">
                            <a class="nav-link text-warning fw-bold" href="{{ route('engineer.cv.create') }}">تقديم السيرة الذاتية <i class="bi bi-exclamation-triangle-fill"></i></a>
                        </li>
                        @endif

                        {{-- يمكنك إضافة المزيد من روابط التنقل هنا --}}
                        @if($user->hasRole('Manager'))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('manager.dashboard') }}">إدارة المشاريع</a>
                        </li>
                        @endif
                        {{-- ... روابط لأدوار أخرى ... --}}
                    @endauth
                </ul>
                <ul class="navbar-nav">
                    @auth
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                {{ Auth::user()->first_name }} {{ Auth::user()->last_name }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                {{-- <a class="dropdown-item" href="#">الملف الشخصي</a> --}}
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">تسجيل الخروج</button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('login') }}">تسجيل الدخول</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('register') }}">إنشاء حساب</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    <main class="main-content">
        @if (session('status'))
            <div class="alert alert-success mt-3" role="alert">{{ session('status') }}</div>
        @endif
        @if (session('success'))
            <div class="alert alert-success mt-3" role="alert">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger mt-3" role="alert">{{ session('error') }}</div>
        @endif
        @if (session('info'))
            <div class="alert alert-info mt-3" role="alert">{{ session('info') }}</div>
        @endif
        @if (session('warning'))
            <div class="alert alert-warning mt-3" role="alert">{{ session('warning') }}</div>
        @endif
        {{ $slot }}
    </main>
    <!-- تضمين Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>