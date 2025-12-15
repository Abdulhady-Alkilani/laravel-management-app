<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password as PasswordRule; // لتجنب تضارب الاسم

class CustomResetPassword extends Component
{
    public $token;
    public $email;
    public $password = '';
    public $password_confirmation = '';
    public $status;

    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ];
    }

    protected $messages = [
        'email.required' => 'البريد الإلكتروني مطلوب.',
        'email.email' => 'يجب أن يكون البريد الإلكتروني صالحًا.',
        'password.required' => 'كلمة المرور مطلوبة.',
        'password.confirmed' => 'تأكيد كلمة المرور غير متطابق.',
    ];

    public function mount($token)
    {
        $this->token = $token;
        $this->email = request()->query('email', ''); // جلب البريد الإلكتروني من الـ query string
    }

    public function resetPassword()
    {
        $this->validate();

        $response = Password::reset(
            $this->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user) {
                $user->forceFill([
                    'password' => Hash::make($this->password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));

                Auth::guard('web')->login($user);
            }
        );

        if ($response == Password::PASSWORD_RESET) {
            session()->flash('status', __($response));
            return redirect()->route('login'); // توجيه لصفحة تسجيل الدخول بعد النجاح
        } else {
            $this->addError('email', __($response));
            throw ValidationException::withMessages([
                'email' => __($response),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.custom-reset-password')->layout('components.layouts.guest-layout', ['title' => 'إعادة تعيين كلمة المرور']);
    }
} 