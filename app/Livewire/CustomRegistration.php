<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

// استيراد Filament Forms
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Illuminate\Support\HtmlString;

class CustomRegistration extends Component implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];

    public function mount(): void
    {
        if (Auth::check()) {
            if (Auth::user()->hasRole('Admin')) {
                redirect()->intended('/admin');
            } else {
                redirect()->intended('/login'); // أو أي لوحة افتراضية أخرى
            }
        }
        $this->form->fill();
    }

    protected function generateUniqueEmail()
    {
        do {
            $uuid = Str::uuid();
            $email = "user_{$uuid}@generated.local";
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

    public function form(Form $form): Form
    {
        // جلب الأدوار المتاحة للتسجيل
        $roles = Role::whereNotIn('name', ['Admin', 'Investor'])->get();
        $translatedRoles = $roles->mapWithKeys(function ($role) {
            return [$role->id => $this->translateRoleName($role->name)];
        })->toArray();

        return $form
            ->schema([
                TextInput::make('first_name')
                    ->label('الاسم الأول')
                    ->required()
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->label('الاسم الأخير')
                    ->required()
                    ->maxLength(255),
                Select::make('role_id')
                    ->label('الدور الوظيفي')
                    ->options($translatedRoles)
                    ->required()
                    ->hint('حدد طبيعة عملك لتخصيص واجهة النظام المناسبة لك.')
                    ->searchable(),
                TextInput::make('username')
                    ->label('اسم المستخدم')
                    ->nullable()
                    ->unique(User::class, 'username') // تحقق من التفرد فقط إذا أدخل المستخدم قيمة
                    ->hint(new HtmlString('<span class="text-success"><i class="bi bi-info-circle"></i> اختياري: اتركه فارغاً ليتم توليده تلقائياً من اسمك.</span>'))
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('البريد الإلكتروني')
                    ->nullable()
                    ->email()
                    ->unique(User::class, 'email') // تحقق من التفرد فقط إذا أدخل المستخدم قيمة
                    ->hint(new HtmlString('<span class="text-success">اختياري: إذا لم تملك بريداً، سيتم إنشاء بريد افتراضي لك.</span>'))
                    ->maxLength(255),
                TextInput::make('password')
                    ->label('كلمة المرور')
                    ->required()
                    ->password()
                    ->revealable() // <== تم تفعيلها
                    ->rule(Password::defaults())
                    ->confirmed(),
                TextInput::make('password_confirmation')
                    ->label('تأكيد كلمة المرور')
                    ->required()
                    ->password()
                    ->revealable(), // <== تم تفعيلها
                Select::make('gender')
                    ->label('الجنس')
                    ->nullable()
                    ->options([
                        'male' => 'ذكر',
                        'female' => 'أنثى',
                    ]),
                TextInput::make('phone_number')
                    ->label('رقم الهاتف')
                    ->nullable()
                    ->tel()
                    ->maxLength(20),
                Textarea::make('address')
                    ->label('العنوان')
                    ->nullable()
                    ->rows(2)
                    ->columnSpanFull(),
                TextInput::make('nationality')
                    ->label('الجنسية')
                    ->nullable()
                    ->maxLength(255),
                Textarea::make('profile_details')
                    ->label('نبذة مختصرة')
                    ->nullable()
                    ->rows(2)
                    ->hint('معلومات إضافية عن مهاراتك أو خبراتك.')
                    ->columnSpanFull(),
            ])
            ->columns(2)
            ->statePath('data');
        }

        public function register()
        {
            try {
                $data = $this->form->getState();

                $finalEmail = $data['email'] ?? $this->generateUniqueEmail();
                $finalUsername = $data['username'] ?? $this->generateUsernameFromNames($data['first_name'], $data['last_name']);

                $user = User::create([
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'email' => $finalEmail,
                    'username' => $finalUsername,
                    'password' => Hash::make($data['password']),
                    'gender' => $data['gender'],
                    'address' => $data['address'] ?? null,
                    'nationality' => $data['nationality'] ?? null,
                    'phone_number' => $data['phone_number'],
                    'profile_details' => $data['profile_details'],
                ]);

                $user->roles()->attach($data['role_id']);

                Auth::login($user);

                session()->flash('registered_username', $finalUsername);
                session()->flash('registered_password', $data['password']);

                return redirect()->route('registration.completion');
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->form->fill($this->data);
                $this->form->withErrors($e->errors());
                session()->flash('error', 'الرجاء تصحيح الأخطاء في النموذج.');
            } catch (\Exception $e) {
                session()->flash('error', 'حدث خطأ غير متوقع: ' . $e->getMessage());
            }
        }

        public function render()
        {
            return view('livewire.custom-registration')->layout('components.layouts.guest-layout', ['title' => 'إنشاء حساب جديد']);
        }
    }