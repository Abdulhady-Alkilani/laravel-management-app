<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cv;
use App\Models\Role;
use App\Models\Skill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Str;
use Closure;

class EmployeeApplicationController extends Controller
{
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
            $baseUsername = 'applicant';
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
            'Information Technology Engineer' => 'مهندس معلوماتية',
            'Telecommunications Engineer' => 'مهندس اتصالات',
            'Investor' => 'مستثمر',
        ][$englishName] ?? $englishName;
    }

    // <== دالة مساعدة للحصول على قائمة الدول (يمكن توسيعها من قاعدة بيانات لاحقاً)
    protected function getCountries(): array
    {
        return [
            'السعودية', 'الإمارات', 'الكويت', 'قطر', 'البحرين', 'عمان', 'اليمن', 'مصر', 'السودان',
            'ليبيا', 'تونس', 'الجزائر', 'المغرب', 'موريتانيا', 'الصومال', 'جيبوتي', 'جزر القمر',
            'فلسطين', 'الأردن', 'لبنان', 'سوريا', 'العراق', 'تركيا', 'إيران', 'باكستان', 'الهند',
            'الصين', 'روسيا', 'الولايات المتحدة', 'كندا', 'المملكة المتحدة', 'ألمانيا', 'فرنسا',
            'إسبانيا', 'إيطاليا', 'أستراليا', 'نيوزيلندا', 'اليابان', 'كوريا الجنوبية', 'أخرى'
        ];
    }

    // <== دالة مساعدة للحصول على خيارات سنوات الخبرة
    protected function getExperienceOptions(): array
    {
        return [
            'Fresh' => 'فريش (أقل من سنة)',
            'Less than 5 years' => 'أقل من 5 سنوات',
            '5 to 10 years' => '5 - 10 سنوات',
            'More than 10 years' => 'أكثر من 10 سنوات',
        ];
    }


    /**
     * Show the form for Step 1: Basic Info & Role.
     */
    public function createStep1(Request $request)
    {
        $applicationData = $request->session()->get('employee_application', []);
        
        $engineerAndWorkerRolesNames = [
            'Worker',
            'Architectural Engineer', 'Civil Engineer', 'Structural Engineer', 'Electrical Engineer',
            'Mechanical Engineer', 'Geotechnical Engineer', 'Quantity Surveyor', 'Site Engineer',
            'Environmental Engineer', 'Surveying Engineer', 'Information Technology Engineer', 'Telecommunications Engineer',
        ];
        $roles = Role::whereIn('name', $engineerAndWorkerRolesNames)->get();
        $translatedRoles = $roles->map(fn ($role) => ['id' => $role->id, 'name' => $this->translateRoleName($role->name)]);
        
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
        ], [
            'first_name.required' => 'الاسم الأول مطلوب.',
            'last_name.required' => 'الاسم الأخير مطلوب.',
            'role_id.required' => 'يرجى اختيار الدور الوظيفي.',
            'role_id.exists' => 'الدور المختار غير صالح.',
        ]);

        $firstName = $request->input('first_name');
        $lastName = $request->input('last_name');
        $generatedUsername = $this->generateUniqueUsername($firstName, $lastName);

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
                function ($attribute, $value, Closure $fail) {
                    if ($value && User::where($attribute, $value)->exists()) {
                        $fail('هذا البريد الإلكتروني مستخدم بالفعل. الرجاء استخدام بريد إلكتروني آخر فريد أو تركه فارغًا ليتم توليده تلقائياً.');
                    }
                },
            ],
            'username' => [
                'nullable', 'string', 'max:255',
                function ($attribute, $value, Closure $fail) {
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
        $countries = $this->getCountries(); // <== جلب قائمة الدول
        $experienceOptions = $this->getExperienceOptions(); // <== جلب خيارات الخبرة

        return view('applications.employee-form-step3', compact('applicationData', 'countries', 'experienceOptions'));
    }

    /**
     * Store Step 3 data in session (including clear-text password) and redirect to Step 4.
     */
    public function storeStep3(Request $request)
    {
        $experienceOptionsKeys = array_keys($this->getExperienceOptions()); // لجلب المفاتيح للتحقق

        $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
            'gender' => ['nullable', 'in:male,female'],
            'address' => ['nullable', 'string', 'max:255'],
            'nationality' => ['nullable', 'string', 'max:255', 'in:' . implode(',', $this->getCountries())], // <== تحقق من الجنسية
            'phone_number' => ['nullable', 'string', 'max:20'],
            'profile_details' => ['nullable', 'string'],
            'years_of_experience_summary' => ['required', 'string', 'in:' . implode(',', $experienceOptionsKeys)], // <== حقل سنوات الخبرة الجديد
        ], [
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
            'years_of_experience_summary.required' => 'يرجى تحديد سنوات الخبرة.',
            'years_of_experience_summary.in' => 'قيمة سنوات الخبرة المختارة غير صالحة.',
            'nationality.in' => 'الجنسية المختارة غير صالحة.',
        ]);

        $request->session()->put('employee_application.step3', $request->only([
            'password', 'gender', 'address', 'nationality', 'phone_number', 'profile_details', 'years_of_experience_summary' // <== تخزين سنوات الخبرة
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
            'selected_skills' => ['nullable', 'array'],
            'selected_skills.*' => ['exists:skills,id'],
            'new_skill' => ['nullable', 'string', 'max:255', 'unique:skills,name'], // <== حقل المهارة الجديدة
            'education' => ['required', 'string', 'max:1000'],
            // 'experience' تم إزالته من هنا لأنه تم استبداله بـ years_of_experience_summary
        ], [
            // 'experience.required' => 'الخبرة مطلوبة لإكمال السيرة الذاتية.',
            'education.required' => 'المؤهلات العلمية مطلوبة لإكمال السيرة الذاتية.',
            'new_skill.unique' => 'هذه المهارة موجودة بالفعل، يرجى اختيارها من القائمة أو إضافة مهارة فريدة.',
        ]);

        $request->session()->put('employee_application.step4', $request->only([
            'selected_skills', 'new_skill', 'education'
        ]));

        $allApplicationData = $request->session()->get('employee_application');

        $step1Data = $allApplicationData['step1'];
        $step2Data = $allApplicationData['step2'] ?? [];
        $step3Data = $allApplicationData['step3'];
        $cvData = $allApplicationData['step4'];

        $finalEmail = $step2Data['email'] ?? $this->generateUniqueEmail();
        $finalUsername = $step2Data['username'] ?? $this->generateUniqueUsername($step1Data['first_name'], $step1Data['last_name']);

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

        $userRole = Role::find($step1Data['role_id']);
        if ($userRole) {
            $user->roles()->attach($userRole->id);
        } else {
             \Log::warning("Selected role ID {$step1Data['role_id']} not found for new employee application user.");
        }

        $cv = Cv::create([
            'user_id' => $user->id,
            'profile_details' => $step3Data['profile_details'] ?? null,
            'experience' => $step3Data['years_of_experience_summary'] ?? null, // <== حفظ سنوات الخبرة هنا
            'education' => $cvData['education'] ?? null,
            'cv_status' => 'قيد الانتظار',
            'rejection_reason' => null,
        ]);

        $skillIdsToAttach = [];
        if (!empty($cvData['selected_skills'])) {
            $skillIdsToAttach = array_merge($skillIdsToAttach, $cvData['selected_skills']);
        }
        // <== معالجة المهارة الجديدة إذا تم إدخالها
        if (!empty($cvData['new_skill'])) {
            $newSkill = Skill::firstOrCreate(['name' => $cvData['new_skill']]); // إنشاء المهارة إذا لم تكن موجودة
            $skillIdsToAttach[] = $newSkill->id;
        }

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
} 