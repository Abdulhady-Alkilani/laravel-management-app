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
            RoleSeeder::class,                // 1
            SkillSeeder::class,               // 2
            UserSeeder::class,                // 3 (يحتاج أدوارًا من RoleSeeder)
            CvSeeder::class,                  // 4 (يحتاج مستخدمين من UserSeeder ومهارات من SkillSeeder)
            ServiceSeeder::class,             // 5
            ProjectSeeder::class,             // 6 (يحتاج مديري مستخدمين من UserSeeder)
            WorkshopSeeder::class,            // 7 (يمكن أن يرتبط بمشاريع من ProjectSeeder)
            WorkerWorkshopLinkSeeder::class,  // 8 (يحتاج مستخدمين من UserSeeder وورشات من WorkshopSeeder)
            ProjectInvestorLinkSeeder::class, // 9 (يحتاج مستخدمين من UserSeeder ومشاريع من ProjectSeeder)
            TaskSeeder::class,                // 10 (يحتاج مشاريع وورشات وعمال من Seeders سابقة)
            ReportSeeder::class,              // 11 (يحتاج كل شيء تقريبًا من Seeders سابقة)
            ServiceRequestSeeder::class,      // 12 (يحتاج مستخدمين وخدمات من Seeders سابقة)
            NewServiceProposalSeeder::class,  // 13 (يحتاج مستخدمين من UserSeeder)
        ]);
    }
}