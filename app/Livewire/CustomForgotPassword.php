<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class CustomForgotPassword extends Component
{
    public $email = '';
    public $status;

    protected $rules = [
        'email' => 'required|email',
    ];

    protected $messages = [
        'email.required' => 'يرجى إدخال البريد الإلكتروني.',
        'email.email' => 'يجب أن يكون البريد الإلكتروني صالحًا.',
    ];

    public function sendResetLink(Request $request)
    {
        $this->validate();

        $status = Password::sendResetLink($this->only('email'));

        $this->status = __($status);

        if ($status === Password::RESET_LINK_SENT) {
            session()->flash('status', $this->status);
            $this->email = ''; // مسح حقل البريد الإلكتروني
        } else {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }
    }

    public function render()
    {
        return view('livewire.custom-forgot-password')->layout('components.layouts.guest-layout', ['title' => 'نسيت كلمة المرور']);
    } 
}