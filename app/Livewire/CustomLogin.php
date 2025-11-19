<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Filament\Panel; // <== استيراد Panel

class CustomLogin extends Component
{
    public $username = '';
    public $password = '';
    public $remember = false;

    protected $rules = [
        'username' => 'required|string',
        'password' => 'required|string',
    ];

    protected $messages = [
        'username.required' => 'يرجى إدخال اسم المستخدم.',
        'password.required' => 'يرجى إدخال كلمة المرور.',
    ];

    public function authenticate(Request $request)
    {
        $this->validate();

        if (Auth::attempt(['username' => $this->username, 'password' => $this->password], $this->remember)) {
            $request->session()->regenerate();
            
            $user = Auth::user();

            return $this->redirectBasedOnRole($user); // <== توجيه موحد من هنا
        }

        $this->addError('username', 'بيانات تسجيل الدخول غير صحيحة.');
    }

    // <== هنا التعديل: دالة التوجيه الرئيسية
    public function redirectBasedOnRole(User $user)
    {
        session()->flash('status', "مرحباً بك، {$user->first_name}! تم تسجيل دخولك بنجاح.");

        // المنطق الدقيق للتوجيه لكل دور
        if ($user->hasRole('Admin')) {
            session()->flash('status', "مرحباً بك، {$user->first_name}! تم تسجيل دخولك بنجاح.");
            return redirect()->intended('/admin');
        }
        if ($user->hasRole('Manager')) {
            session()->flash('status', "مرحباً بك، {$user->first_name}! تم تسجيل دخولك بنجاح.");
            return redirect()->intended('/manager');
        }
        if ($user->hasRole('Workshop Supervisor')) {
            session()->flash('status', "مرحباً بك، {$user->first_name}! تم تسجيل دخولك بنجاح.");
            return redirect()->intended('/workshop-supervisor');
        }
        if ($user->hasRole('Worker')) {
            session()->flash('status', "مرحباً بك، {$user->first_name}! تم تسجيل دخولك بنجاح.");
            return redirect()->intended('/worker');
        }
        if ($user->hasRole('Investor')) {
            session()->flash('status', "مرحباً بك، {$user->first_name}! تم تسجيل دخولك بنجاح.");
            return redirect()->intended('/investor');
        }
        if ($user->hasRole('Reviewer')) {
            session()->flash('status', "مرحباً بك، {$user->first_name}! تم تسجيل دخولك بنجاح.");
            return redirect()->intended('/reviewer');
        }
        // لوحة المهندس موحدة لجميع أنواع المهندسين
        $engineerRoles = [
            'Architectural Engineer', 'Civil Engineer', 'Structural Engineer', 'Electrical Engineer',
            'Mechanical Engineer', 'Geotechnical Engineer', 'Quantity Surveyor', 'Site Engineer',
            'Environmental Engineer', 'Surveying Engineer'
        ];
        foreach ($engineerRoles as $roleName) {
            if ($user->hasRole($roleName)) {
                session()->flash('status', "مرحباً بك، {$user->first_name}! تم تسجيل دخولك بنجاح.");
                return redirect()->intended('/engineer'); // توجيه جميع المهندسين إلى /engineer
            }
        }
        // لمقدم طلب/اقتراح خدمة
        if ($user->hasRole('Service Proposer')) {
            session()->flash('status', "مرحباً بك، {$user->first_name}! تم تسجيل دخولك بنجاح.");
            return redirect()->intended('/service-proposer');
        }

        //Fallback: إذا لم يتطابق أي دور (وهذا لا ينبغي أن يحدث في تطبيق منظم)
       // return redirect()->intended('/admin'); // توجيه Admin كـ fallback نهائي
    }

    public function render()
    {
        return view('livewire.custom-login')->layout('components.layouts.guest-layout', ['title' => 'تسجيل الدخول']);
    }
}