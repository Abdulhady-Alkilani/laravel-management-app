<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Report;
use App\Models\User;
use App\Models\Project;
use App\Models\Workshop;
use App\Models\Service;
use Carbon\Carbon;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('username', 'admin.sys')->first();
        $fahadManager = User::where('username', 'fahad.manager')->first();
        $khalidWorker = User::where('username', 'khalid.worker')->first();
        $noraTech = User::where('username', 'nora.tech')->first();
        $saeedSupervisor = User::where('username', 'saeed.supervisor')->first();

        $project1 = Project::where('name', 'مجمع الواحة السكني')->first();
        $project2 = Project::where('name', 'برج الأفق التجاري')->first();
        $project3 = Project::where('name', 'مدرسة الأجيال الجديدة')->first();
        $project6 = Project::where('name', 'تطوير حديقة الأمل')->first();

        $concreteWorkshop = Workshop::where('name', 'ورشة الهياكل الخرسانية')->first();
        $electricalWorkshop = Workshop::where('name', 'ورشة التمديدات الكهربائية')->first();
        $plumbingWorkshop = Workshop::where('name', 'ورشة أنظمة السباكة')->first();

        $electricalService = Service::where('name', 'خدمة صيانة كهربائية عامة')->first();
        $plumbingService = Service::where('name', 'خدمة تركيب شبكات مياه')->first();
        $constructionConsultingService = Service::where('name', 'خدمة استشارات هندسية إنشائية')->first();

        // Debugging: check if dependencies are found
        if (!$admin) $this->command->error('ReportSeeder DEBUG: Admin user (admin.sys) not found!');
        if (!$fahadManager) $this->command->error('ReportSeeder DEBUG: Manager user (fahad.manager) not found!');
        if (!$khalidWorker) $this->command->error('ReportSeeder DEBUG: Worker user (khalid.worker) not found!');
        if (!$noraTech) $this->command->error('ReportSeeder DEBUG: Worker user (nora.tech) not found!');
        if (!$saeedSupervisor) $this->command->error('ReportSeeder DEBUG: Supervisor user (saeed.supervisor) not found!');
        if (!$project1) $this->command->error('ReportSeeder DEBUG: Project 1 (مجمع الواحة السكني) not found!');
        if (!$project2) $this->command->error('ReportSeeder DEBUG: Project 2 (برج الأفق التجاري) not found!');
        if (!$project3) $this->command->error('ReportSeeder DEBUG: Project 3 (مدرسة الأجيال الجديدة) not found!');
        if (!$project6) $this->command->error('ReportSeeder DEBUG: Project 6 (تطوير حديقة الأمل) not found!');
        if (!$concreteWorkshop) $this->command->error('ReportSeeder DEBUG: Workshop (ورشة الهياكل الخرسانية) not found!');
        if (!$electricalWorkshop) $this->command->error('ReportSeeder DEBUG: Workshop (ورشة التمديدات الكهربائية) not found!');
        if (!$plumbingWorkshop) $this->command->error('ReportSeeder DEBUG: Workshop (ورشة أنظمة السباكة) not found!');
        if (!$electricalService) $this->command->error('ReportSeeder DEBUG: Service (خدمة صيانة كهربائية عامة) not found!');
        if (!$plumbingService) $this->command->error('ReportSeeder DEBUG: Service (خدمة تركيب شبكات مياه) not found!');
        if (!$constructionConsultingService) $this->command->error('ReportSeeder DEBUG: Service (خدمة استشارات هندسية إنشائية) not found!');


        // Proceed only if all critical dependencies are met
        if ($admin && $fahadManager && $khalidWorker && $noraTech && $saeedSupervisor &&
            $project1 && $project2 && $project3 && $project6 && $concreteWorkshop && $electricalWorkshop && $plumbingWorkshop &&
            $electricalService && $plumbingService && $constructionConsultingService)
        {
            $reportsData = [
                // Report 1: Worker Progress Report
                [
                    'employee_id' => $khalidWorker->id,
                    'project_id' => $project1->id,
                    'workshop_id' => $concreteWorkshop->id,
                    'report_type' => 'Progress Report',
                    'report_details' => json_encode(['progress_percentage' => 85, 'issues' => 'لا توجد تحديات كبرى', 'next_steps' => 'الانتهاء من صب الدور الثاني']),
                    'report_status' => 'تمت المراجعة',
                    'created_at' => Carbon::parse('2024-03-01'),
                ],
                // Report 2: Electrical Worker Productivity
                [
                    'employee_id' => $noraTech->id,
                    'project_id' => $project2->id,
                    'workshop_id' => $electricalWorkshop->id,
                    'report_type' => 'Productivity Report',
                    'report_details' => json_encode(['daily_output' => '12 نقطة كهرباء', 'hours_worked' => 8, 'efficiency_rating' => 'ممتاز']),
                    'report_status' => 'معلقة',
                    'created_at' => Carbon::parse('2024-03-05'),
                ],
                // Report 3: Manager Cost Report
                [
                    'employee_id' => $fahadManager->id,
                    'project_id' => $project1->id,
                    'workshop_id' => null, // تقرير على مستوى المشروع
                    'service_id' => null,
                    'report_type' => 'Cost Report',
                    'report_details' => json_encode(['total_spent' => 25000000, 'remaining_budget' => 70000000, 'variance' => 'ضمن الميزانية بنسبة 5%']),
                    'report_status' => 'تمت الموافقة',
                    'created_at' => Carbon::parse('2024-04-10'),
                ],
                // Report 4: Admin Project Progress Overview
                [
                    'employee_id' => $admin->id,
                    'project_id' => $project2->id,
                    'workshop_id' => null,
                    'service_id' => null,
                    'report_type' => 'Progress Report',
                    'report_details' => json_encode(['overall_progress' => 65, 'key_milestones' => 'الانتهاء من الهيكل الخرساني', 'risks' => 'تأخير محتمل في توريد الزجاج']),
                    'report_status' => 'تمت المراجعة',
                    'created_at' => Carbon::parse('2024-05-01'),
                ],
                // Report 5: Supervisor Workshop Report
                [
                    'employee_id' => $saeedSupervisor->id,
                    'project_id' => $project3->id,
                    'workshop_id' => $plumbingWorkshop->id,
                    'service_id' => null,
                    'report_type' => 'Workshop Activity',
                    'report_details' => json_encode(['daily_tasks' => 'فحص تمديدات المياه، تركيب 15 نقطة صرف', 'challenges' => 'نقص في بعض القطع الصغيرة']),
                    'report_status' => 'معلقة',
                    'created_at' => Carbon::parse('2024-05-15'),
                ],
                // Report 6: Service Call Report (not linked to a project)
                [
                    'employee_id' => $noraTech->id,
                    'project_id' => null, // هذا التقرير لا يرتبط بمشروع
                    'workshop_id' => null,
                    'service_id' => $electricalService->id,
                    'report_type' => 'Service Call',
                    'report_details' => json_encode(['customer' => 'فيلا رقم 12', 'issue' => 'عطل في مفتاح الإضاءة الرئيسي', 'resolution' => 'تم استبدال المفتاح']),
                    'report_status' => 'مكتملة',
                    'created_at' => Carbon::parse('2024-05-20'),
                ],
                // Report 7: Project Quality Report
                [
                    'employee_id' => $fahadManager->id,
                    'project_id' => $project6->id,
                    'workshop_id' => null,
                    'service_id' => null,
                    'report_type' => 'Quality Assurance',
                    'report_details' => json_encode(['inspection_area' => 'أعمال تنسيق الحدائق', 'findings' => 'جودة ممتازة، لا توجد ملاحظات', 'recommendations' => 'متابعة الصيانة الدورية']),
                    'report_status' => 'تمت الموافقة',
                    'created_at' => Carbon::parse('2024-06-01'),
                ],
            ];

            foreach ($reportsData as $reportData) {
                Report::create($reportData);
            }
            $this->command->info('Reports seeded successfully!');
        } else {
            $this->command->error('ReportSeeder SKIPPED: Not all critical dependencies were found.');
        }
    }
}