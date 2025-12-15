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
        // <== التعديل هنا: استخدام redirect() بدلاً من redirect()->intended()
        if ($user->hasRole('Admin')) {
            return redirect('/admin');
        }
        if ($user->hasRole('Manager')) {
            return redirect('/manager');
        }
        if ($user->hasRole('Workshop Supervisor')) {
            return redirect('/workshop-supervisor');
        }
        if ($user->hasRole('Worker')) {
            return redirect('/worker');
        }
        if ($user->hasRole('Investor')) {
            return redirect('/investor');
        }
        if ($user->hasRole('Reviewer')) {
            return redirect('/reviewer');
        }
        
        $engineerRoles = [
            'Architectural Engineer', 'Civil Engineer', 'Structural Engineer', 'Electrical Engineer',
            'Mechanical Engineer', 'Geotechnical Engineer', 'Quantity Surveyor', 'Site Engineer',
            'Environmental Engineer', 'Surveying Engineer',
            'Information Technology Engineer', // <== إضافة الدور الجديد
            'Telecommunications Engineer',    // <== إضافة الدور الجديد

        ];
        foreach ($engineerRoles as $roleName) {
            if ($user->hasRole($roleName)) {
                return redirect('/engineer');
            }
        }
        
        if ($user->hasRole('Service Proposer')) {
            return redirect('/service-proposer');
        }

        // Fallback: إذا لم يتطابق أي دور، قم بتوجيه إلى لوحة تحكم افتراضية
        // هذا يجب أن يكون نادراً إذا كانت جميع الأدوار مغطاة
        return redirect('/'); // توجيه لصفحة البداية، والتي يجب أن توجهه للدخول إن لم يكن لديه دور
    }


    public function render()
    {
        return view('livewire.custom-login')->layout('components.layouts.guest-layout', ['title' => 'تسجيل الدخول']);
    }
} 