<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usersData = [
            // Admin User
            [
                'first_name' => 'مدير', 'last_name' => 'النظام', 'email' => 'admin@app.com', 'username' => 'admin.sys',
                'password' => Hash::make('password'), 'gender' => 'male', 'address' => 'المركز الرئيسي، المدينة',
                'nationality' => 'عربي', 'phone_number' => '00966501112222', 'profile_details' => 'مسؤول رئيسي للنظام.',
                'roles' => ['Admin']
            ],
            // Manager User
            [
                'first_name' => 'فهد', 'last_name' => 'المدير', 'email' => 'fahad.m@app.com', 'username' => 'fahad.manager',
                'password' => Hash::make('password'), 'gender' => 'male', 'address' => 'مكتب إدارة المشاريع، المدينة',
                'nationality' => 'سعودي', 'phone_number' => '00966503334444', 'profile_details' => 'مدير مشاريع ذو خبرة عالية.',
                'roles' => ['Manager']
            ],
            // Worker Users
            [
                'first_name' => 'خالد', 'last_name' => 'العامل', 'email' => 'khalid.w@app.com', 'username' => 'khalid.worker',
                'password' => Hash::make('password'), 'gender' => 'male', 'address' => 'حي العمال، المدينة',
                'nationality' => 'مصري', 'phone_number' => '00201012345678', 'profile_details' => 'عامل بناء ماهر.',
                'roles' => ['Worker']
            ],
            [
                'first_name' => 'نورا', 'last_name' => 'الفنية', 'email' => 'nora.t@app.com', 'username' => 'nora.tech',
                'password' => Hash::make('password'), 'gender' => 'female', 'address' => 'منطقة الصناعات الخفيفة',
                'nationality' => 'أردنية', 'phone_number' => '00962779876543', 'profile_details' => 'فنية كهرباء متميزة.',
                'roles' => ['Worker']
            ],
            [
                'first_name' => 'يوسف', 'last_name' => 'السباك', 'email' => 'yousef.p@app.com', 'username' => 'yousef.plumber',
                'password' => Hash::make('password'), 'gender' => 'male', 'address' => 'حي الصفا، المدينة',
                'nationality' => 'سوري', 'phone_number' => '00963991234567', 'profile_details' => 'سباك بخبرة 10 سنوات.',
                'roles' => ['Worker']
            ],
            [
                'first_name' => 'مريم', 'last_name' => 'الرسامة', 'email' => 'maryam.d@app.com', 'username' => 'maryam.drawer',
                'password' => Hash::make('password'), 'gender' => 'female', 'address' => 'حي الأندلس، المدينة',
                'nationality' => 'لبنانية', 'phone_number' => '0096170123456', 'profile_details' => 'رسامة معمارية موهوبة.',
                'roles' => ['Worker']
            ],
            // Investor User
            [
                'first_name' => 'جاسم', 'last_name' => 'المستثمر', 'email' => 'jasim.i@app.com', 'username' => 'jasim.investor',
                'password' => Hash::make('password'), 'gender' => 'male', 'address' => 'حي الأثرياء، المدينة',
                'nationality' => 'قطري', 'phone_number' => '0097455123456', 'profile_details' => 'مستثمر كبير في العقارات.',
                'roles' => ['Investor']
            ],
            // Workshop Supervisor
            [
                'first_name' => 'سعيد', 'last_name' => 'المشرف', 'email' => 'saeed.s@app.com', 'username' => 'saeed.supervisor',
                'password' => Hash::make('password'), 'gender' => 'male', 'address' => 'منطقة الورشات، المدينة',
                'nationality' => 'تونسي', 'phone_number' => '0021620123456', 'profile_details' => 'مشرف ورشة ميكانيكية.',
                'roles' => ['Workshop Supervisor']
            ],
            // Reviewer User
            [
                'first_name' => 'علياء', 'last_name' => 'المراجعة', 'email' => 'alya.r@app.com', 'username' => 'alya.reviewer',
                'password' => Hash::make('password'), 'gender' => 'female', 'address' => 'قسم المراجعة، الشركة',
                'nationality' => 'عمانية', 'phone_number' => '0096899123456', 'profile_details' => 'مراجعة طلبات الخدمات.',
                'roles' => ['Reviewer']
            ],
            // Engineers
            [
                'first_name' => 'لانا', 'last_name' => 'المعمارية', 'email' => 'lana.a@app.com', 'username' => 'lana.architect',
                'password' => Hash::make('password'), 'gender' => 'female', 'address' => 'مكتب التصميم، المدينة',
                'nationality' => 'مصرية', 'phone_number' => '00201112223334', 'profile_details' => 'مهندسة معمارية ذات رؤية إبداعية.',
                'roles' => ['Architectural Engineer']
            ],
            [
                'first_name' => 'مازن', 'last_name' => 'المدني', 'email' => 'mazen.c@app.com', 'username' => 'mazen.civil',
                'password' => Hash::make('password'), 'gender' => 'male', 'address' => 'قسم الإنشاءات، المدينة',
                'nationality' => 'سعودي', 'phone_number' => '00966551122334', 'profile_details' => 'مهندس مدني متخصص في الإشراف.',
                'roles' => ['Civil Engineer']
            ],
            [
                'first_name' => 'سمير', 'last_name' => 'الإنشائي', 'email' => 'samir.s@app.com', 'username' => 'samir.structural',
                'password' => Hash::make('password'), 'gender' => 'male', 'address' => 'مكتب التصميم الإنشائي',
                'nationality' => 'عراقي', 'phone_number' => '009647801234567', 'profile_details' => 'مهندس إنشائي يحترف تحليل الهياكل.',
                'roles' => ['Structural Engineer']
            ],
            [
                'first_name' => 'هدى', 'last_name' => 'الكهربائية', 'email' => 'huda.e@app.com', 'username' => 'huda.electrical',
                'password' => Hash::make('password'), 'gender' => 'female', 'address' => 'قسم الكهرباء، المدينة',
                'nationality' => 'مغربية', 'phone_number' => '00212612345678', 'profile_details' => 'مهندسة كهربائية خبيرة بأنظمة الطاقة.',
                'roles' => ['Electrical Engineer']
            ],
            [
                'first_name' => 'طارق', 'last_name' => 'الميكانيكي', 'email' => 'tariq.m@app.com', 'username' => 'tariq.mechanical',
                'password' => Hash::make('password'), 'gender' => 'male', 'address' => 'قسم الميكانيك، المدينة',
                'nationality' => 'إماراتي', 'phone_number' => '00971501234567', 'profile_details' => 'مهندس ميكانيكي لأنظمة التبريد.',
                'roles' => ['Mechanical Engineer']
            ],
        ];

        foreach ($usersData as $userData) {
            $rolesToAssign = $userData['roles'];
            unset($userData['roles']);

            $user = User::firstOrCreate(['email' => $userData['email']], $userData);

            foreach ($rolesToAssign as $roleName) {
                $role = Role::where('name', $roleName)->first();
                if ($role) {
                    $user->roles()->syncWithoutDetaching([$role->id]);
                }
            }
        }
        $this->command->info('Users seeded successfully!');
    }
}