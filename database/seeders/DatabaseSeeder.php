<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            SkillSeeder::class,
            UserSeeder::class,
            CvSeeder::class,
            ServiceSeeder::class,
            ProjectSeeder::class,
            WorkshopSeeder::class,
            WorkerWorkshopLinkSeeder::class,
            ProjectInvestorLinkSeeder::class,
            TaskSeeder::class,
            ReportSeeder::class,
            ServiceRequestSeeder::class,      // <== تأكد من تشغيله هنا
            NewServiceProposalSeeder::class,  // <== وتأكد من تشغيله هنا
        ]);
    }
}