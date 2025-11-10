<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Task;
use App\Models\Project;
use App\Models\Workshop;
use App\Models\User;
use Carbon\Carbon;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $project1 = Project::where('name', 'مجمع الواحة السكني')->first();
        $project2 = Project::where('name', 'برج الأفق التجاري')->first();
        $project3 = Project::where('name', 'مدرسة الأجيال الجديدة')->first();
        $project6 = Project::where('name', 'تطوير حديقة الأمل')->first();

        $concreteWorkshop = Workshop::where('name', 'ورشة الهياكل الخرسانية')->first();
        $electricalWorkshop = Workshop::where('name', 'ورشة التمديدات الكهربائية')->first();
        $finishingWorkshop = Workshop::where('name', 'ورشة التشطيبات الداخلية')->first();
        $joineryWorkshop = Workshop::where('name', 'ورشة أعمال النجارة')->first();
        $plumbingWorkshop = Workshop::where('name', 'ورشة أنظمة السباكة')->first();
        $designWorkshop = Workshop::where('name', 'ورشة التصميم المعماري')->first(); // <== الورشة الجديدة

        $khalid = User::where('username', 'khalid.worker')->first();
        $nora = User::where('username', 'nora.tech')->first();
        $yousef = User::where('username', 'yousef.plumber')->first();
        $mazen = User::where('username', 'mazen.civil')->first(); // مهندس مدني قد يكون مسؤولاً عن مهمة
        $lana = User::where('username', 'lana.architect')->first();

        // Ensure all dependencies exist
        if (!$project1 || !$project2 || !$project3 || !$project6 || !$concreteWorkshop || !$electricalWorkshop ||
            !$finishingWorkshop ||   !$designWorkshop || !$joineryWorkshop || !$plumbingWorkshop || !$khalid || !$nora || !$yousef || !$mazen || !$lana) {
            $this->command->error('Some dependencies for TaskSeeder not found! Please run Project, Workshop, User Seeders first.');
            return;
        }

        $tasksData = [
            // Project 1: مجمع الواحة السكني
            [
                'project_id' => $project1->id, 'workshop_id' => $concreteWorkshop->id, 'description' => 'صب أساسات الفلل الأولى',
                'progress' => 100, 'start_date' => Carbon::parse('2024-01-20'), 'end_date_planned' => Carbon::parse('2024-02-10'),
                'actual_end_date' => Carbon::parse('2024-02-08'), 'assigned_to_user_id' => $khalid->id, 'status' => 'مكتملة',
                'estimated_cost' => 150000.00, 'actual_cost' => 145000.00,
            ],
            [
                'project_id' => $project1->id, 'workshop_id' => $finishingWorkshop->id, 'description' => 'دهان داخلي للوحدات التجريبية',
                'progress' => 70, 'start_date' => Carbon::parse('2024-05-01'), 'end_date_planned' => Carbon::parse('2024-06-15'),
                'assigned_to_user_id' => $khalid->id, 'status' => 'قيد التنفيذ', 'estimated_cost' => 80000.00,
            ],
            // Project 2: برج الأفق التجاري
            [
                'project_id' => $project2->id, 'workshop_id' => $electricalWorkshop->id, 'description' => 'تمديد شبكة كهرباء الطوابق السفلية',
                'progress' => 100, 'start_date' => Carbon::parse('2023-09-01'), 'end_date_planned' => Carbon::parse('2023-10-20'),
                'actual_end_date' => Carbon::parse('2023-10-18'), 'assigned_to_user_id' => $nora->id, 'status' => 'مكتملة',
                'estimated_cost' => 200000.00, 'actual_cost' => 195000.00,
            ],
            [
                'project_id' => $project2->id, 'workshop_id' => $plumbingWorkshop->id, 'description' => 'تركيب أنظمة الصرف الصحي المركزية',
                'progress' => 90, 'start_date' => Carbon::parse('2024-03-01'), 'end_date_planned' => Carbon::parse('2024-04-30'),
                'assigned_to_user_id' => $yousef->id, 'status' => 'قيد التنفيذ', 'estimated_cost' => 120000.00,
            ],
            // Project 3: مدرسة الأجيال الجديدة
            [
                'project_id' => $project3->id, 'workshop_id' => $joineryWorkshop->id, 'description' => 'تركيب أبواب ونوافذ الفصول الدراسية',
                'progress' => 100, 'start_date' => Carbon::parse('2023-11-01'), 'end_date_planned' => Carbon::parse('2023-12-15'),
                'actual_end_date' => Carbon::parse('2023-12-10'), 'assigned_to_user_id' => $mazen->id, 'status' => 'مكتملة', // مهندس يشرف على التركيب
                'estimated_cost' => 90000.00, 'actual_cost' => 88000.00,
            ],
            // Project 6: تطوير حديقة الأمل
            [
                'project_id' => $project6->id, 'workshop_id' => $concreteWorkshop->id, 'description' => 'صب مسارات المشي الرئيسية',
                'progress' => 40, 'start_date' => Carbon::parse('2024-04-15'), 'end_date_planned' => Carbon::parse('2024-06-30'),
                'assigned_to_user_id' => $khalid->id, 'status' => 'قيد التنفيذ', 'estimated_cost' => 60000.00,
            ],
            [
                'project_id' => $project6->id, 'workshop_id' => $finishingWorkshop->id, 'description' => 'تركيب مقاعد ومناطق لعب للأطفال',
                'progress' => 0, 'start_date' => Carbon::parse('2024-07-01'), 'end_date_planned' => Carbon::parse('2024-08-31'),
                'assigned_to_user_id' => null, 'status' => 'لم تبدأ', 'estimated_cost' => 45000.00,
            ],


             [
                'project_id' => $project1->id, 'workshop_id' => $designWorkshop->id, 'description' => 'تصميم الواجهات الخارجية للمرحلة الأولى - مجمع الواحة السكني',
                'progress' => 70, 'start_date' => Carbon::parse('2024-07-01'), 'end_date_planned' => Carbon::parse('2024-08-15'),
                'assigned_to_user_id' => $lana->id, 'status' => 'قيد التنفيذ', 'estimated_cost' => 80000.00,
            ],
            // مهمة مراجعة تصميم لـ Lana
            [
                'project_id' => $project2->id, 'workshop_id' => $designWorkshop->id, 'description' => 'مراجعة المخططات الداخلية للطابق الخامس - برج الأفق التجاري',
                'progress' => 100, 'start_date' => Carbon::parse('2024-06-01'), 'end_date_planned' => Carbon::parse('2024-06-30'),
                'actual_end_date' => Carbon::parse('2024-06-28'), 'assigned_to_user_id' => $lana->id, 'status' => 'مكتملة', 'estimated_cost' => 50000.00,
            ],
           

        ];

        foreach ($tasksData as $taskData) {
            // Using firstOrCreate to avoid duplicates if seeder runs multiple times
            Task::firstOrCreate(
                [
                    'project_id' => $taskData['project_id'],
                    'workshop_id' => $taskData['workshop_id'],
                    'description' => $taskData['description'],
                ],
                $taskData
            );
        }
        $this->command->info('Tasks seeded successfully!');
    }
}