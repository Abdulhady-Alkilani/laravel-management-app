<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cv;
use App\Models\User;
use App\Models\Skill;

class CvSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $khalid = User::where('username', 'khalid.worker')->first();
        $nora = User::where('username', 'nora.tech')->first();
        $yousef = User::where('username', 'yousef.plumber')->first();
        $lana = User::where('username', 'lana.architect')->first();
        $mazen = User::where('username', 'mazen.civil')->first();
        $samir = User::where('username', 'samir.structural')->first();
        $fadi = User::where('username', 'fadi.it')->first();
        $sumaya = User::where('username', 'sumaya.tele')->first();

        // جلب بعض المهارات لربطها
        $concreteWorkSkill = Skill::where('name', 'أعمال الخرسانة المسلحة')->first();
        $electricalWiringSkill = Skill::where('name', 'تمديدات كهربائية صناعية')->first();
        $plumbingSkill = Skill::where('name', 'أعمال سباكة')->first(); // هذا هو المتغير المشتبه به
        $architecturalDesignSkill = Skill::where('name', 'تصميم معماري')->first();
        $siteSupervisionSkill = Skill::where('name', 'إشراف ميداني')->first();
        $structuralAnalysisSkill = Skill::where('name', 'تحليل إنشائي')->first();
        $cadSkill = Skill::where('name', 'برامج CAD')->first();
        $safetySkill = Skill::where('name', 'صحة وسلامة مهنية')->first(); // هذا هو المتغير المشتبه به
        $projectManagementSkill = Skill::where('name', 'إدارة مشاريع البناء')->first();
        $softwareDevelopmentSkill = Skill::where('name', 'تطوير البرمجيات')->firstOrCreate(['name' => 'تطوير البرمجيات']);
        $networkAdminSkill = Skill::where('name', 'إدارة الشبكات')->firstOrCreate(['name' => 'إدارة الشبكات']);
        $telecomProtocolsSkill = Skill::where('name', 'بروتوكولات الاتصالات')->firstOrCreate(['name' => 'بروتوكولات الاتصالات']);


        // ----------------------------------------------------------------------
        // DEBUGGING: إضافة رسائل تصحيح للتحقق من وجود المهارات
        // ----------------------------------------------------------------------
        if (!$concreteWorkSkill) $this->command->error('CvSeeder DEBUG: Skill "أعمال الخرسانة المسلحة" not found!');
        if (!$electricalWiringSkill) $this->command->error('CvSeeder DEBUG: Skill "تمديدات كهربائية صناعية" not found!');
        if (!$plumbingSkill) $this->command->error('CvSeeder DEBUG: Skill "أعمال سباكة" not found!');
        if (!$architecturalDesignSkill) $this->command->error('CvSeeder DEBUG: Skill "تصميم معماري" not found!');
        if (!$siteSupervisionSkill) $this->command->error('CvSeeder DEBUG: Skill "إشراف ميداني" not found!');
        if (!$structuralAnalysisSkill) $this->command->error('CvSeeder DEBUG: Skill "تحليل إنشائي" not found!');
        if (!$cadSkill) $this->command->error('CvSeeder DEBUG: Skill "برامج CAD" not found!');
        if (!$safetySkill) $this->command->error('CvSeeder DEBUG: Skill "صحة وسلامة مهنية" not found!');
        if (!$lana) $this->command->error('CvSeeder DEBUG: User lana.architect not found!');
        if (!$architecturalDesignSkill) $this->command->error('CvSeeder DEBUG: Skill "تصميم معماري" not found!');
        if (!$cadSkill) $this->command->error('CvSeeder DEBUG: Skill "برامج CAD" not found!');
        if (!$projectManagementSkill) $this->command->error('CvSeeder DEBUG: Skill "إدارة مشاريع البناء" not found!');
        if (!$siteSupervisionSkill) $this->command->error('CvSeeder DEBUG: Skill "إشراف ميداني" not found!');
        if (!$fadi) $this->command->error('CvSeeder DEBUG: User fadi.it not found!');
        if (!$sumaya) $this->command->error('CvSeeder DEBUG: User sumaya.tele not found!');


        // ----------------------------------------------------------------------

        // Ensure all users exist
        if (!$khalid || !$nora || !$yousef || !$lana || !$mazen || !$samir) {
            $this->command->error('CvSeeder SKIPPED: Some users not found! Please run UserSeeder first.');
            return;
        }

        // CV for Khalid (Worker)
        if ($khalid && $concreteWorkSkill && $safetySkill && $cadSkill) { // إضافة فحص لوجود المهارات
            $cvKhalid = Cv::firstOrCreate(
                ['user_id' => $khalid->id],
                [
                    'profile_details' => 'عامل بناء ذو خبرة 5 سنوات في الأعمال الإنشائية والخرسانية.',
                    'experience' => '5 سنوات في أعمال البناء، خبرة في قوالب الخرسانة.',
                    'education' => 'شهادة ثانوية مهنية، دورات في السلامة.',
                    'cv_status' => 'تمت الموافقة',
                ]
            );
            $cvKhalid->skills()->syncWithoutDetaching([$concreteWorkSkill->id, $safetySkill->id, $cadSkill->id]);
        } else {
             $this->command->error('CvSeeder DEBUG: Could not create CV for Khalid due to missing dependencies.');
        }


                // CV for Fadi (Information Technology Engineer)
        if ($fadi && $softwareDevelopmentSkill && $networkAdminSkill) {
            $cvFadi = Cv::firstOrCreate(
                ['user_id' => $fadi->id],
                [
                    'profile_details' => 'مهندس معلوماتية متخصص في تطوير الويب وإدارة قواعد البيانات، خبرة 4 سنوات.',
                    'experience' => '4 سنوات خبرة في تطوير أنظمة ERP، وصيانة خوادم.',
                    'education' => 'بكالوريوس هندسة معلوماتية، جامعة حلب 2020.',
                    'cv_status' => 'تمت الموافقة',
                ]
            );
            $cvFadi->skills()->syncWithoutDetaching([$softwareDevelopmentSkill->id, $networkAdminSkill->id]);
        } else { $this->command->error('CvSeeder DEBUG: Could not create CV for Fadi due to missing dependencies.'); }

        // CV for Sumaya (Telecommunications Engineer)
        if ($sumaya && $telecomProtocolsSkill && $networkAdminSkill) {
            $cvSumaya = Cv::firstOrCreate(
                ['user_id' => $sumaya->id],
                [
                    'profile_details' => 'مهندسة اتصالات بخبرة في تصميم وتطوير شبكات الاتصالات اللاسلكية، خبرة 3 سنوات.',
                    'experience' => '3 سنوات خبرة في شركة اتصالات، تخطيط شبكات 5G.',
                    'education' => 'بكالوريوس هندسة اتصالات، جامعة تشرين 2021.',
                    'cv_status' => 'تمت الموافقة',
                ]
            );
            $cvSumaya->skills()->syncWithoutDetaching([$telecomProtocolsSkill->id, $networkAdminSkill->id]);
        } else { $this->command->error('CvSeeder DEBUG: Could not create CV for Sumaya due to missing dependencies.'); }



        // CV for Nora (Worker - Electrical)
        if ($nora && $electricalWiringSkill && $cadSkill) { // إضافة فحص لوجود المهارات
            $cvNora = Cv::firstOrCreate(
                ['user_id' => $nora->id],
                [
                    'profile_details' => 'فنية كهرباء بخبرة 7 سنوات في تركيب وصيانة الأنظمة الكهربائية.',
                    'experience' => '7 سنوات كفنية كهرباء في مشاريع سكنية وتجارية.',
                    'education' => 'دبلوم فني كهرباء صناعية.',
                    'cv_status' => 'تمت الموافقة',
                ]
            );
            $cvNora->skills()->syncWithoutDetaching([$electricalWiringSkill->id, $cadSkill->id]);
        } else {
             $this->command->error('CvSeeder DEBUG: Could not create CV for Nora due to missing dependencies.');
        }

        // CV for Yousef (Worker - Plumber)
        if ($yousef && $plumbingSkill && $safetySkill) { // إضافة فحص لوجود المهارات
            $cvYousef = Cv::firstOrCreate(
                ['user_id' => $yousef->id],
                [
                    'profile_details' => 'سباك محترف ذو خبرة في تمديدات المياه والصرف الصحي.',
                    'experience' => '10 سنوات في أعمال السباكة للمباني الكبيرة.',
                    'education' => 'شهادة تدريب مهني في السباكة.',
                    'cv_status' => 'قيد الانتظار',
                ]
            );
            $cvYousef->skills()->syncWithoutDetaching([$plumbingSkill->id, $safetySkill->id]);
        } else {
             $this->command->error('CvSeeder DEBUG: Could not create CV for Yousef due to missing dependencies.');
        }

         // CV for Lana (Architectural Engineer)
        if ($lana && $architecturalDesignSkill && $cadSkill && $projectManagementSkill) {
            $cvLana = Cv::firstOrCreate(
                ['user_id' => $lana->id],
                [
                    'profile_details' => 'مهندسة معمارية مبدعة في تصميم المساحات السكنية والتجارية، بخبرة 3 سنوات.',
                    'experience' => '3 سنوات خبرة في مكتب استشارات هندسية، تصميم واجهات داخلية وخارجية.',
                    'education' => 'بكالوريوس هندسة معمارية، جامعة دمشق 2021.',
                    'cv_status' => 'تمت الموافقة',
                ]
            );
            $cvLana->skills()->syncWithoutDetaching([$architecturalDesignSkill->id, $cadSkill->id, $projectManagementSkill->id, $siteSupervisionSkill->id]);
        } else { $this->command->error('CvSeeder DEBUG: Could not create CV for Lana due to missing dependencies.'); }
        

        // CV for Mazen (Civil Engineer)
        if ($mazen && $siteSupervisionSkill && $concreteWorkSkill && $safetySkill) { // إضافة فحص لوجود المهارات
            $cvMazen = Cv::firstOrCreate(
                ['user_id' => $mazen->id],
                [
                    'profile_details' => 'مهندس مدني متخصص في إدارة المواقع والإشراف على التنفيذ.',
                    'experience' => '6 سنوات خبرة كمهندس موقع.',
                    'education' => 'بكالوريوس هندسة مدنية.',
                    'cv_status' => 'تمت الموافقة',
                ]
            );
            $cvMazen->skills()->syncWithoutDetaching([$siteSupervisionSkill->id, $concreteWorkSkill->id, $safetySkill->id]);
        } else {
             $this->command->error('CvSeeder DEBUG: Could not create CV for Mazen due to missing dependencies.');
        }

        // CV for Samir (Structural Engineer)
        if ($samir && $structuralAnalysisSkill && $cadSkill && $siteSupervisionSkill) { // إضافة فحص لوجود المهارات
            $cvSamir = Cv::firstOrCreate(
                ['user_id' => $samir->id],
                [
                    'profile_details' => 'مهندس إنشائي بخبرة في تصميم وتحليل الهياكل المعقدة.',
                    'experience' => '8 سنوات في التصميم والتحليل الإنشائي.',
                    'education' => 'ماجستير في الهندسة الإنشائية.',
                    'cv_status' => 'تمت الموافقة',
                ]
            );
            $cvSamir->skills()->syncWithoutDetaching([$structuralAnalysisSkill->id, $cadSkill->id, $siteSupervisionSkill->id]);
        } else {
             $this->command->error('CvSeeder DEBUG: Could not create CV for Samir due to missing dependencies.');
        }


        // ... بقية الأكواد للعمال الآخرين إذا كانو موجودين

        $this->command->info('CVs seeded successfully and skills attached!');
    }
} 