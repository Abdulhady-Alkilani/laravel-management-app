<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cv;
use App\Models\Role;
use App\Models\Skill; // استيراد Model المهارة الجديد
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;

class EmployeeApplicationController extends Controller
{
    // ... (generateUniqueEmail, generateUsernameFromNames - تبقى كما هي) ...

    protected function generateUniqueEmail()
    {
        do {
            $uuid = Str::uuid();
            $email = "applicant_{$uuid}@generated.com";
        } while (User::where('email', $email)->exists());
        return $email;
    }

    protected function generateUsernameFromNames(string $firstName, string $lastName)
    {
        $baseUsername = Str::slug($firstName . '.' . $lastName, '.');
        if (empty($baseUsername) || preg_match('/^\.+$/', $baseUsername)) {
            $baseUsername = 'applicant';
        }

        $username = $baseUsername;
        $counter = 1;
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter++;
        }
        return $username;
    }

    /**
     * Show the form for Step 1: Basic Info & Role.
     */
    public function createStep1(Request $request)
    {
        $applicationData = $request->session()->get('employee_application', []);
        $roles = Role::whereNotIn('name', ['Admin', 'Investor'])->get();
        $translatedRoles = $roles->map(function ($role) {
            return ['id' => $role->id, 'name' => $this->translateRoleName($role->name)];
        });
        return view('applications.employee-form-step1', compact('applicationData', 'translatedRoles'));
    }

    /**
     * Store Step 1 data in session, generate username, and redirect to Step 2.
     */
    public function storeStep1(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'role_id' => ['required', 'exists:roles,id'],
        ]);

        $firstName = $request->input('first_name');
        $lastName = $request->input('last_name');
        $generatedUsername = $this->generateUsernameFromNames($firstName, $lastName);

        $request->session()->put('employee_application.step1', $request->only(['first_name', 'last_name', 'role_id']));
        $request->session()->put('employee_application.generated_username', $generatedUsername);

        return redirect()->route('employee.apply.step2');
    }

    /**
     * Show the form for Step 2: Email & Username.
     */
    public function createStep2(Request $request)
    {
        if (!$request->session()->has('employee_application.step1')) {
            return redirect()->route('employee.apply.step1')->with('error', 'الرجاء إكمال الخطوة الأولى أولاً.');
        }
        $applicationData = $request->session()->get('employee_application', []);
        $generatedUsername = $request->session()->get('employee_application.generated_username');

        $email = old('email', $applicationData['step2']['email'] ?? '');
        $username = old('username', $applicationData['step2']['username'] ?? $generatedUsername);

        return view('applications.employee-form-step2', compact('applicationData', 'email', 'username', 'generatedUsername'));
    }

    /**
     * Store Step 2 data in session and redirect to Step 3.
     */
    public function storeStep2(Request $request)
    {
        $request->validate([
            'email' => [
                'nullable', 'string', 'email', 'max:255',
                function ($attribute, $value, $fail) {
                    if ($value && User::where($attribute, $value)->exists()) {
                        $fail('هذا البريد الإلكتروني مستخدم بالفعل. الرجاء استخدام بريد إلكتروني آخر فريد أو تركه فارغًا ليتم توليده تلقائياً.');
                    }
                },
            ],
            'username' => [
                'nullable', 'string', 'max:255',
                function ($attribute, $value, $fail) {
                    if ($value && User::where($attribute, $value)->exists()) {
                        $fail('اسم المستخدم هذا مستخدم بالفعل. الرجاء استخدام اسم مستخدم آخر فريد أو تركه فارغًا ليتم توليده تلقائياً.');
                    }
                },
            ],
        ]);

        $request->session()->put('employee_application.step2', $request->only(['email', 'username']));

        return redirect()->route('employee.apply.step3');
    }

    /**
     * Show the form for Step 3: Password & Personal Info.
     */
    public function createStep3(Request $request)
    {
        if (!$request->session()->has('employee_application.step1')) {
            return redirect()->route('employee.apply.step1')->with('error', 'الرجاء إكمال الخطوة الأولى أولاً.');
        }
        $applicationData = $request->session()->get('employee_application', []);
        return view('applications.employee-form-step3', compact('applicationData'));
    }

    /**
     * Store Step 3 data in session (including clear-text password) and redirect to Step 4.
     */
    public function storeStep3(Request $request)
    {
        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
            'gender' => ['nullable', 'string', 'in:male,female'],
            'address' => ['nullable', 'string', 'max:255'],
            'nationality' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'profile_details' => ['nullable', 'string'],
        ]);

        $request->session()->put('employee_application.step3', $request->only([
            'password',
            'gender', 'address', 'nationality', 'phone_number', 'profile_details'
        ]));
        $request->session()->put('employee_application.clear_password', $request->input('password'));


        return redirect()->route('employee.apply.step4');
    }

    /**
     * Show the form for Step 4: CV Details (Final Step).
     */
    public function createStep4(Request $request)
    {
        if (!$request->session()->has('employee_application.step1')) {
            return redirect()->route('employee.apply.step1')->with('error', 'الرجاء إكمال الخطوة الأولى أولاً.');
        }
        $applicationData = $request->session()->get('employee_application', []);
        $skills = Skill::orderBy('name')->get(); // جلب جميع المهارات المتاحة

        return view('applications.employee-form-step4', compact('applicationData', 'skills'));
    }

    /**
     * Store Step 4 data and finalize the application, then redirect to completion page.
     */
    public function storeStep4(Request $request)
    {
        $request->validate([
            'selected_skills' => ['nullable', 'array'], // المهارات المختارة من القائمة
            'selected_skills.*' => ['exists:skills,id'], // التأكد أن المهارات المختارة موجودة
            'new_skills' => ['nullable', 'string', 'max:500'], // المهارات الجديدة التي يدخلها المستخدم
            'cv_experience' => ['nullable', 'string'],
            'cv_education' => ['nullable', 'string'],
        ]);

        $request->session()->put('employee_application.step4', $request->only([
            'selected_skills', 'new_skills', 'cv_experience', 'cv_education'
        ]));

        $allApplicationData = $request->session()->get('employee_application');

        $step1Data = $allApplicationData['step1'];
        $step2Data = $allApplicationData['step2'] ?? [];
        $step3Data = $allApplicationData['step3'];
        $cvData = $allApplicationData['step4'];

        // تحديد البريد الإلكتروني النهائي
        $finalEmail = $step2Data['email'] ?? null;
        if (empty($finalEmail)) {
            $finalEmail = $this->generateUniqueEmail();
        }

        // تحديد اسم المستخدم النهائي
        $finalUsername = $step2Data['username'] ?? null;
        if (empty($finalUsername)) {
            $finalUsername = $this->generateUsernameFromNames($step1Data['first_name'], $step1Data['last_name']);
        }

        // إنشاء المستخدم
        $user = User::create([
            'first_name' => $step1Data['first_name'],
            'last_name' => $step1Data['last_name'],
            'email' => $finalEmail,
            'username' => $finalUsername,
            'password' => Hash::make($step3Data['password']),
            'gender' => $step3Data['gender'] ?? null,
            'address' => $step3Data['address'] ?? null,
            'nationality' => $step3Data['nationality'] ?? null,
            'phone_number' => $step3Data['phone_number'] ?? null,
            'profile_details' => $step3Data['profile_details'] ?? null,
        ]);

        // ربط الدور بالمستخدم
        $userRole = Role::find($step1Data['role_id']);
        if ($userRole) {
            $user->roles()->attach($userRole->id);
        } else {
             \Log::warning("Selected role ID {$step1Data['role_id']} not found for new user.");
        }

        // إنشاء السيرة الذاتية
        $cv = Cv::create([ // استخدم متغير $cv هنا لربط المهارات لاحقاً
            'user_id' => $user->id,
            'profile_details' => $step3Data['profile_details'] ?? null,
            // 'skills' لم يعد موجوداً في Cv Model
            'experience' => $cvData['cv_experience'] ?? null,
            'education' => $cvData['cv_education'] ?? null,
            'cv_status' => 'قيد الانتظار',
            'rejection_reason' => null,
        ]);

        // معالجة المهارات
        $skillIdsToAttach = [];

        // 1. المهارات المختارة من القائمة
        if (!empty($cvData['selected_skills'])) {
            $skillIdsToAttach = array_merge($skillIdsToAttach, $cvData['selected_skills']);
        }

        // 2. المهارات الجديدة المدخلة من المستخدم
        if (!empty($cvData['new_skills'])) {
            // تقسيم المهارات الجديدة بفاصلة أو سطر جديد (أو أي فاصل تحدده)
            $newSkillsArray = array_map('trim', explode(',', $cvData['new_skills']));
            $newSkillsArray = array_filter($newSkillsArray); // إزالة القيم الفارغة

            foreach ($newSkillsArray as $newSkillName) {
                if (!empty($newSkillName)) {
                    $skill = Skill::firstOrCreate(['name' => $newSkillName]);
                    $skillIdsToAttach[] = $skill->id;
                }
            }
        }

        // ربط المهارات بالسيرة الذاتية
        if (!empty($skillIdsToAttach)) {
            $cv->skills()->syncWithoutDetaching(array_unique($skillIdsToAttach));
        }


        $clearPassword = $request->session()->get('employee_application.clear_password');

        $request->session()->forget('employee_application');
        $request->session()->forget('employee_application.clear_password');

        return redirect()->route('employee.apply.completion')->with([
            'username' => $finalUsername,
            'password' => $clearPassword,
            'success' => 'تم تقديم طلبك بنجاح! يرجى حفظ بيانات تسجيل الدخول هذه.'
        ]);
    }

    /**
     * Show the completion page with login details.
     */
    public function completion(Request $request)
    {
        if (!session()->has('username') || !session()->has('password')) {
            return redirect()->route('employee.apply.step1')->with('error', 'لا يمكن الوصول إلى هذه الصفحة مباشرة.');
        }

        $username = session('username');
        $password = session('password');

        session()->forget('username');
        session()->forget('password');

        return view('applications.employee-form-completion', compact('username', 'password'));
    }

    // دالة مساعدة لترجمة أسماء الأدوار الإنجليزية إلى العربية للعرض
   protected function translateRoleName(string $englishName): string
    {
        return [
            'Manager' => 'مدير مشروع',
            'Worker' => 'عامل',
            'Workshop Supervisor' => 'مشرف ورشة',
            'Reviewer' => 'مراجع',
            'Architectural Engineer' => 'مهندس معماري',
            'Civil Engineer' => 'مهندس مدني',
            'Structural Engineer' => 'مهندس إنشائي',       // إضافة ترجمة الدور الجديد
            'Electrical Engineer' => 'مهندس كهربائي',     // إضافة ترجمة الدور الجديد
            'Mechanical Engineer' => 'مهندس ميكانيكي',     // إضافة ترجمة الدور الجديد
            'Geotechnical Engineer' => 'مهندس جيوتقني',   // إضافة ترجمة الدور الجديد
            'Quantity Surveyor' => 'مهندس كميات / تكاليف', // إضافة ترجمة الدور الجديد
            'Site Engineer' => 'مهندس موقع',             // إضافة ترجمة الدور الجديد
            'Environmental Engineer' => 'مهندس بيئي',      // إضافة ترجمة الدور الجديد
            'Surveying Engineer' => 'مهندس مساحة',        // إضافة ترجمة الدور الجديد
        ][$englishName] ?? $englishName; // العودة إلى الاسم الإنجليزي إذا لم توجد ترجمة
    }
}