<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fahadManager = User::where('username', 'fahad.manager')->first();
        $mazenCivil = User::where('username', 'mazen.civil')->first(); // يمكن للمهندس المدني أن يكون مديراً لمشروع صغير

        if (!$fahadManager || !$mazenCivil) {
            $this->command->error('Manager or Civil Engineer user not found! Please run UserSeeder first.');
            return;
        }

        $projectsData = [
            [
                'name' => 'مجمع الواحة السكني',
                'description' => 'مجمع سكني فاخر يضم فلل وشققًا، مع مرافق رياضية وترفيهية.',
                'location' => 'ضاحية الرمال الذهبية',
                'budget' => 95000000.00,
                'start_date' => Carbon::parse('2024-01-15'),
                'end_date_planned' => Carbon::parse('2026-01-15'),
                'status' => 'قيد التنفيذ',
                'manager_user_id' => $fahadManager->id,
            ],
            [
                'name' => 'برج الأفق التجاري',
                'description' => 'برج مكاتب حديث في قلب الحي التجاري، يضم 30 طابقاً.',
                'location' => 'المنطقة المالية المركزية',
                'budget' => 120000000.00,
                'start_date' => Carbon::parse('2023-08-01'),
                'end_date_planned' => Carbon::parse('2025-08-01'),
                'status' => 'قيد التنفيذ',
                'manager_user_id' => $fahadManager->id,
            ],
            [
                'name' => 'مدرسة الأجيال الجديدة',
                'description' => 'بناء مدرسة نموذجية حديثة بمرافق تعليمية متطورة.',
                'location' => 'حي السلام الجديد',
                'budget' => 30000000.00,
                'start_date' => Carbon::parse('2023-03-10'),
                'end_date_planned' => Carbon::parse('2024-03-10'),
                'end_date_actual' => Carbon::parse('2024-03-05'),
                'status' => 'مكتمل',
                'manager_user_id' => $fahadManager->id,
            ],
            [
                'name' => 'مركز اللياقة البدنية',
                'description' => 'إنشاء مركز رياضي متكامل يضم صالات ألعاب ومسابح.',
                'location' => 'المنطقة الترفيهية',
                'budget' => 45000000.00,
                'start_date' => Carbon::parse('2024-07-01'),
                'end_date_planned' => Carbon::parse('2025-07-01'),
                'status' => 'مخطط',
                'manager_user_id' => $mazenCivil->id, // مدير مختلف
            ],
            [
                'name' => 'توسعة مستشفى النور',
                'description' => 'إضافة جناح جديد لغرف المرضى وقسم للطوارئ في المستشفى.',
                'location' => 'المنطقة الطبية',
                'budget' => 60000000.00,
                'start_date' => Carbon::parse('2025-02-01'),
                'end_date_planned' => Carbon::parse('2026-02-01'),
                'status' => 'مخطط',
                'manager_user_id' => $fahadManager->id,
            ],
            [
                'name' => 'تطوير حديقة الأمل',
                'description' => 'مشروع إعادة تأهيل وتطوير شامل لحديقة الأمل العامة.',
                'location' => 'وسط المدينة',
                'budget' => 10000000.00,
                'start_date' => Carbon::parse('2024-04-01'),
                'end_date_planned' => Carbon::parse('2024-11-30'),
                'status' => 'قيد التنفيذ',
                'manager_user_id' => $fahadManager->id,
            ],
        ];

        foreach ($projectsData as $projectData) {
            Project::firstOrCreate(['name' => $projectData['name']], $projectData);
        }
        $this->command->info('Projects seeded successfully!');
    }
}