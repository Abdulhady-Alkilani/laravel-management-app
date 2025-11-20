<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException; // لاستخدام ValidationException

class CustomRegistration extends Component
{
    // الخصائص العامة لحقول النموذج
    public $first_name = '';
    public $last_name = '';
    public $role_id = '';
    public $username = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $gender = '';
    public $address = '';
    public $nationality = '';
    public $phone_number = '';
    public $profile_details = '';

    // دالة mount تُنفذ عند تهيئة المكون
    public function mount(): void
    {
        if (Auth::check()) {
            // توجيه المستخدمين المسجلين بالفعل بعيداً عن صفحة التسجيل
            if (Auth::user()->hasRole('Admin')) {
                redirect()->intended('/admin');
            } else {
                redirect()->intended('/login'); // أو أي لوحة افتراضية أخرى
            }
        }
    }

    // --- دوال مساعدة للتوليد التلقائي ---
    protected function generateUniqueEmail()
    {
        do {
            $uuid = Str::uuid();
            $email = "applicant_{$uuid}@generated.local";
        } while (User::where('email', $email)->exists());
        return $email;
    }

    protected function generateUniqueUsername(string $firstName, string $lastName)
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
            'Service Proposer' => 'مستخدم', // <== إضافة ترجمة هذا الدور
        ][$englishName] ?? $englishName;
    }

    // --- قواعد التحقق من الصحة ---
    protected function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'role_id' => ['required', 'exists:roles,id'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'gender' => ['nullable', 'in:male,female'],
            'address' => ['nullable', 'string', 'max:255'],
            'nationality' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'profile_details' => ['nullable', 'string'],
            // قواعد التفرد تُطبق فقط إذا تم إدخال قيمة
            'email' => ['nullable', 'email', 'unique:users,email'],
            'username' => ['nullable', 'string', 'unique:users,username'],
        ];
    }

    protected function messages(): array
    {
        return [
            'first_name.required' => 'الاسم الأول مطلوب.',
            'last_name.required' => 'الاسم الأخير مطلوب.',
            'role_id.required' => 'يرجى اختيار الدور الوظيفي.',
            'role_id.exists' => 'الدور المختار غير صالح.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
            'email.email' => 'يجب أن يكون البريد الإلكتروني صالحًا.',
            'email.unique' => 'البريد الإلكتروني هذا مسجل مسبقاً، يرجى استخدام بريد آخر أو تركه فارغاً.',
            'username.unique' => 'اسم المستخدم هذا محجوز مسبقاً، يرجى استخدام اسم آخر أو تركه فارغاً.',
        ];
    }

    // --- منطق تسجيل الحساب ---
    public function register()
    {
        try {
            $this->validate(); // تشغيل قواعد التحقق

            // توليد البيانات إذا كانت فارغة (بعد التحقق من التفرد إذا تم إدخالها)
            $finalEmail = $this->email ?? $this->generateUniqueEmail();
            $finalUsername = $this->username ?? $this->generateUniqueUsername($this->first_name, $this->last_name);

            $user = User::create([
                'first_name' => $this->first_name,
                'last_name' => $this->last_name,
                'email' => $finalEmail,
                'username' => $finalUsername,
                'password' => Hash::make($this->password),
                'gender' => $this->gender,
                'address' => $this->address,
                'nationality' => $this->nationality,
                'phone_number' => $this->phone_number,
                'profile_details' => $this->profile_details,
            ]);

            // ربط الدور
            $user->roles()->attach($this->role_id);

            Auth::login($user); // تسجيل الدخول تلقائياً

            session()->flash('registered_username', $finalUsername);
            session()->flash('registered_password', $this->password); // كلمة المرور الواضحة لصفحة الإكمال

            return redirect()->route('registration.completion');
        } catch (ValidationException $e) {
            session()->flash('error', 'الرجاء تصحيح الأخطاء في النموذج.');
            throw $e; // إعادة رمي الاستثناء لعرض الأخطاء في الواجهة
        } catch (\Exception $e) {
            session()->flash('error', 'حدث خطأ غير متوقع: ' . $e->getMessage());
        }
    }

    // --- دالة Render لعرض الـ View ---
      public function render()
    {
        // <== هنا التعديل: جلب الأدوار المحددة فقط
        $allowedRolesNames = [
            'Manager',
            'Workshop Supervisor',
            'Investor',
            'Reviewer',
            'Service Proposer'
        ];
        
        $roles = Role::whereIn('name', $allowedRolesNames)->get();
        $translatedRoles = $roles->map(fn ($role) => ['id' => $role->id, 'name' => $this->translateRoleName($role->name)]);

        return view('livewire.custom-registration', compact('translatedRoles'))->layout('components.layouts.guest-layout', ['title' => 'إنشاء حساب جديد']);
    }

}