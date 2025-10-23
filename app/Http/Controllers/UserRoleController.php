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
            // ... (المستخدمون الحاليون: Admin, Manager, Worker1, Worker2, Investor, Supervisor, Reviewer) ...
            [
                'first_name' => 'محمد',
                'last_name' => 'الإداري',
                'email' => 'admin@example.com',
                'username' => 'admin_user',
                'password' => Hash::make('password'),
                'gender' => 'male',
                'address' => 'الرياض، السعودية',
                'nationality' => 'سعودي',
                'phone_number' => '0501234567',
                'profile_details' => 'مسؤول نظام ذو صلاحيات كاملة.',
                'roles' => ['Admin']
            ],
            [
                'first_name' => 'أحمد',
                'last_name' => 'المدير',
                'email' => 'manager@example.com',
                'username' => 'ahmed_manager',
                'password' => Hash::make('password'),
                'gender' => 'male',
                'address' => 'دبي، الإمارات',
                'nationality' => 'إماراتي',
                'phone_number' => '0523456789',
                'profile_details' => 'مدير مشاريع بخبرة 10 سنوات.',
                'roles' => ['Manager']
            ],
            [
                'first_name' => 'فاطمة',
                'last_name' => 'العاملة',
                'email' => 'worker1@example.com',
                'username' => 'fatima_worker',
                'password' => Hash::make('password'),
                'gender' => 'female',
                'address' => 'القاهرة، مصر',
                'nationality' => 'مصري',
                'phone_number' => '01012345678',
                'profile_details' => 'عاملة بناء ماهرة.',
                'roles' => ['Worker']
            ],
            [
                'first_name' => 'علي',
                'last_name' => 'العامل',
                'email' => 'worker2@example.com',
                'username' => 'ali_worker',
                'password' => Hash::make('password'),
                'gender' => 'male',
                'address' => 'بيروت، لبنان',
                'nationality' => 'لبناني',
                'phone_number' => '0789012345',
                'profile_details' => 'عامل كهرباء متخصص.',
                'roles' => ['Worker']
            ],
            [
                'first_name' => 'سارة',
                'last_name' => 'المستثمرة',
                'email' => 'investor@example.com',
                'username' => 'sara_investor',
                'password' => Hash::make('password'),
                'gender' => 'female',
                'address' => 'الدوحة، قطر',
                'nationality' => 'قطري',
                'phone_number' => '009741234567',
                'profile_details' => 'مستثمرة في قطاع العقارات.',
                'roles' => ['Investor']
            ],
            [
                'first_name' => 'خالد',
                'last_name' => 'المشرف',
                'email' => 'supervisor@example.com',
                'username' => 'khalid_supervisor',
                'password' => Hash::make('password'),
                'gender' => 'male',
                'address' => 'عمان، الأردن',
                'nationality' => 'أردني',
                'phone_number' => '0778901234',
                'profile_details' => 'مشرف ورشة بخبرة واسعة.',
                'roles' => ['Workshop Supervisor']
            ],
            [
                'first_name' => 'ليلى',
                'last_name' => 'المراجعة',
                'email' => 'reviewer@example.com',
                'username' => 'layla_reviewer',
                'password' => Hash::make('password'),
                'gender' => 'female',
                'address' => 'الكويت',
                'nationality' => 'كويتي',
                'phone_number' => '0096512345678',
                'profile_details' => 'مسؤولة مراجعة واقتراحات الخدمات.',
                'roles' => ['Reviewer']
            ],
            [
                'first_name' => 'يارا',
                'last_name' => 'المعمارية',
                'email' => 'architect@example.com',
                'username' => 'yara_architect',
                'password' => Hash::make('password'),
                'gender' => 'female',
                'address' => 'دبي، الإمارات',
                'nationality' => 'إماراتي',
                'phone_number' => '0509876543',
                'profile_details' => 'مهندسة معمارية مبدعة في التصميم الداخلي والخارجي.',
                'roles' => ['Architectural Engineer']
            ],
            [
                'first_name' => 'سامي',
                'last_name' => 'المدني',
                'email' => 'civil.engineer@example.com',
                'username' => 'sami_civil',
                'password' => Hash::make('password'),
                'gender' => 'male',
                'address' => 'الرياض، السعودية',
                'nationality' => 'سعودي',
                'phone_number' => '0551122334',
                'profile_details' => 'مهندس مدني متخصص في الإشراف على الهياكل والإنشاءات.',
                'roles' => ['Civil Engineer']
            ],
            [ // مهندس إنشائي
                'first_name' => 'حسن',
                'last_name' => 'الإنشائي',
                'email' => 'structural.eng@example.com',
                'username' => 'hassan_structural',
                'password' => Hash::make('password'),
                'gender' => 'male',
                'address' => 'أبو ظبي، الإمارات',
                'nationality' => 'إماراتي',
                'phone_number' => '0567890123',
                'profile_details' => 'مهندس إنشائي ذو خبرة في تصميم وتحليل الهياكل.',
                'roles' => ['Structural Engineer']
            ],
            [ // مهندس كهربائي
                'first_name' => 'نور',
                'last_name' => 'الكهربائية',
                'email' => 'electrical.eng@example.com',
                'username' => 'noor_electrical',
                'password' => Hash::make('password'),
                'gender' => 'female',
                'address' => 'المنامة، البحرين',
                'nationality' => 'بحريني',
                'phone_number' => '0097312345678',
                'profile_details' => 'مهندسة كهربائية متخصصة في أنظمة الطاقة للمباني.',
                'roles' => ['Electrical Engineer']
            ],
            [ // مهندس ميكانيكي
                'first_name' => 'جاسم',
                'last_name' => 'الميكانيكي',
                'email' => 'mechanical.eng@example.com',
                'username' => 'jasim_mechanical',
                'password' => Hash::make('password'),
                'gender' => 'male',
                'address' => 'مسقط، عُمان',
                'nationality' => 'عماني',
                'phone_number' => '0096812345678',
                'profile_details' => 'مهندس ميكانيكي بخبرة في أنظمة HVAC والسباكة.',
                'roles' => ['Mechanical Engineer']
            ],
            [ // مهندس مساحة
                'first_name' => 'مريم',
                'last_name' => 'المساحة',
                'email' => 'surveying.eng@example.com',
                'username' => 'maryam_surveying',
                'password' => Hash::make('password'),
                'gender' => 'female',
                'address' => 'القاهرة، مصر',
                'nationality' => 'مصري',
                'phone_number' => '01123456789',
                'profile_details' => 'مهندسة مساحة ذات دقة عالية في أعمال الرفع المساحي.',
                'roles' => ['Surveying Engineer']
            ],
        ];

        foreach ($usersData as $userData) {
            $rolesToAssign = $userData['roles'];
            unset($userData['roles']);

            $user = User::firstOrCreate(['email' => $userData['email']], $userData);

            foreach ($rolesToAssign as $roleName) {
                $role = Role::where('name', $roleName)->first();
                if ($role) {
                    $user->roles()->syncWithoutDetaching($role->id);
                }
            }
        }
        $this->command->info('Users seeded successfully!');
    }
}