<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class CustomAuthController extends Controller
{
    // --- دوال مساعدة للتوليد التلقائي ---
    protected function generateUniqueEmail()
    {
        do {
            $uuid = Str::uuid();
            $email = "user_{$uuid}@generated.local"; // نطاق محلي لتمييزه
        } while (User::where('email', $email)->exists());
        return $email;
    }

    protected function generateUsernameFromNames(string $firstName, string $lastName)
    {
        $baseUsername = Str::slug($firstName . '.' . $lastName, '.');
        if (empty($baseUsername) || preg_match('/^\.+$/', $baseUsername)) {
            $baseUsername = 'user';
        }

        $username = $baseUsername;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter++;
        }
        return $username;
    }

    // --- تسجيل حساب جديد (Registration) ---

    public function showRegisterForm()
    {
        $roles = Role::whereNotIn('name', ['Admin', 'Investor'])->get();
        $translatedRoles = $roles->map(function ($role) {
             return [
                'id' => $role->id,
                'name' => $this->translateRoleName($role->name),
            ];
        });

        return view('auth.custom-register', compact('translatedRoles'));
    }

    public function register(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'gender' => ['nullable', 'in:male,female'],
            'address' => ['nullable', 'string', 'max:255'],
            'nationality' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'profile_details' => ['nullable', 'string'],
            'email' => ['nullable', 'email', 'unique:users,email'],
            'username' => ['nullable', 'string', 'unique:users,username'],
        ], [
            'first_name.required' => 'الاسم الأول مطلوب.',
            'last_name.required' => 'الاسم الأخير مطلوب.',
            'role_id.required' => 'يرجى اختيار الدور الوظيفي.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
            'email.email' => 'يجب أن يكون البريد الإلكتروني صالحًا.',
            'email.unique' => 'البريد الإلكتروني هذا مسجل مسبقاً، يرجى استخدام بريد آخر أو تركه فارغاً.',
            'username.unique' => 'اسم المستخدم هذا محجوز مسبقاً، يرجى استخدام اسم آخر أو تركه فارغاً.',
        ]);

        $finalEmail = $request->email ?? $this->generateUniqueEmail();
        $finalUsername = $request->username ?? $this->generateUsernameFromNames($request->first_name, $request->last_name);

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $finalEmail,
            'username' => $finalUsername,
            'password' => Hash::make($request->password),
            'gender' => $request->gender,
            'address' => $request->address,
            'nationality' => $request->nationality,
            'phone_number' => $request->phone_number,
            'profile_details' => $request->profile_details,
        ]);

        $user->roles()->attach($request->role_id);

        Auth::login($user); // تسجيل الدخول تلقائياً بعد التسجيل

        // تخزين معلومات الدخول في الجلسة مؤقتاً لعرضها في صفحة الإكمال
        session()->flash('registered_username', $finalUsername);
        session()->flash('registered_password', $request->password); // كلمة المرور الواضحة

        return redirect()->route('registration.completion'); // التوجيه لصفحة الإكمال
    }

    public function showRegistrationCompletion()
    {
        if (!Auth::check() || !session()->has('registered_username') || !session()->has('registered_password')) {
            return redirect()->route('login')->with('error', 'لا يمكن الوصول إلى هذه الصفحة مباشرة.');
        }

        $username = session('registered_username');
        $password = session('registered_password');

        // مسح بيانات التسجيل من الجلسة بعد عرضها
        session()->forget('registered_username');
        session()->forget('registered_password');

        return view('auth.registration-completion', compact('username', 'password'));
    }

    // --- تسجيل الدخول (Login) ---

    public function showLoginForm()
    {
        return view('auth.custom-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'username.required' => 'يرجى إدخال اسم المستخدم.',
            'password.required' => 'يرجى إدخال كلمة المرور.',
        ]);

        if (Auth::attempt(['username' => $request->username, 'password' => $request->password], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return $this->redirectBasedOnRole(Auth::user());
        }

        return back()->withErrors([
            'username' => 'بيانات تسجيل الدخول غير صحيحة.',
        ])->onlyInput('username');
    }

    // --- تسجيل الخروج (Logout) ---

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }

    // --- دوال مساعدة ---

    public function redirectBasedOnRole(User $user)
    {
        session()->flash('status', "مرحباً بك، {$user->first_name}! تم تسجيل دخولك بنجاح.");

        if ($user->hasRole('Admin')) {
            return redirect()->intended('/admin');
        }

        if ($user->hasRole('Manager')) return redirect()->intended(route('manager.dashboard'));
        if ($user->hasRole('Worker')) return redirect()->intended(route('worker.dashboard'));
        if ($user->hasRole('Investor')) return redirect()->intended(route('investor.dashboard'));
        if ($user->hasRole('Workshop Supervisor')) return redirect()->intended(route('workshop_supervisor.dashboard'));
        if ($user->hasRole('Reviewer')) return redirect()->intended(route('reviewer.dashboard'));
        if ($user->hasRole('Architectural Engineer')) return redirect()->intended(route('architectural_engineer.dashboard'));
        if ($user->hasRole('Civil Engineer')) return redirect()->intended(route('civil_engineer.dashboard'));
        if ($user->hasRole('Structural Engineer')) return redirect()->intended(route('structural_engineer.dashboard'));
        if ($user->hasRole('Electrical Engineer')) return redirect()->intended(route('electrical_engineer.dashboard'));
        if ($user->hasRole('Mechanical Engineer')) return redirect()->intended(route('mechanical_engineer.dashboard'));
        if ($user->hasRole('Geotechnical Engineer')) return redirect()->intended(route('geotechnical_engineer.dashboard'));
        if ($user->hasRole('Quantity Surveyor')) return redirect()->intended(route('quantity_surveyor.dashboard'));
        if ($user->hasRole('Site Engineer')) return redirect()->intended(route('site_engineer.dashboard'));
        if ($user->hasRole('Environmental Engineer')) return redirect()->intended(route('environmental_engineer.dashboard'));
        if ($user->hasRole('Surveying Engineer')) return redirect()->intended(route('surveying_engineer.dashboard'));

        return redirect()->intended('/dashboard');
    }

    protected function translateRoleName(string $englishName): string
    {
        return [
            'Manager' => 'مدير مشروع',
            'Worker' => 'عامل',
            'Workshop Supervisor' => 'مشرف ورشة',
            'Reviewer' => 'مراجع',
            'Architectural Engineer' => 'مهندس معماري',
            'Civil Engineer' => 'مهندس مدني',
            'Structural Engineer' => 'مهندس إنشائي',
            'Electrical Engineer' => 'مهندس كهربائي',
            'Mechanical Engineer' => 'مهندس ميكانيكي',
            'Geotechnical Engineer' => 'مهندس جيوتقني',
            'Quantity Surveyor' => 'مهندس كميات / تكاليف',
            'Site Engineer' => 'مهندس موقع',
            'Environmental Engineer' => 'مهندس بيئي',
            'Surveying Engineer' => 'مهندس مساحة',
            'Investor' => 'مستثمر',
        ][$englishName] ?? $englishName;
    }
}