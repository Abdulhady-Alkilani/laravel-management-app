<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Skill;

class SkillSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $skills = [
            ['name' => 'إدارة مشاريع البناء', 'description' => 'تخطيط، تنفيذ، ومراقبة مشاريع البناء.'],
            ['name' => 'تصميم معماري', 'description' => 'إعداد الرسومات والتصاميم المعمارية للمباني.'],
            ['name' => 'تحليل إنشائي', 'description' => 'تحليل وتصميم العناصر الإنشائية للمباني.'],
            ['name' => 'تمديدات كهربائية صناعية', 'description' => 'تركيب وصيانة أنظمة الكهرباء ذات الجهد العالي.'],
            ['name' => 'أنظمة HVAC', 'description' => 'تركيب وصيانة أنظمة التدفئة والتهوية وتكييف الهواء.'],
            ['name' => 'فحوصات التربة', 'description' => 'إجراء الاختبارات لخصائص التربة وتوصيات الأساسات.'],
            ['name' => 'حساب الكميات والتكاليف', 'description' => 'تقدير المواد والعمالة والتكاليف الإجمالية للمشروع.'],
            ['name' => 'إشراف ميداني', 'description' => 'الإشراف على عمليات البناء اليومية في الموقع.'],
            ['name' => 'تصوير مساحي', 'description' => 'إجراء المسوحات الطبوغرافية وتحديد النقاط.'],
            ['name' => 'صحة وسلامة مهنية', 'description' => 'تطبيق معايير السلامة في مواقع العمل.'],
            ['name' => 'أعمال الخرسانة المسلحة', 'description' => 'صب وتجهيز الخرسانة المسلحة.'],
            ['name' => 'تشطيبات داخلية وخارجية', 'description' => 'أعمال الدهان، البلاط، والديكورات.'],
            ['name' => 'برامج CAD', 'description' => 'المهارة في استخدام برامج التصميم بمساعدة الحاسوب.'],
            ['name' => 'لحام معادن', 'description' => 'تقنيات لحام مختلفة لهياكل معدنية.'],
            ['name' => 'قراءة مخططات هندسية', 'description' => 'فهم وتطبيق كافة أنواع المخططات الهندسية.'],
            ['name' => 'أعمال سباكة', 'description' => 'فهم وتطبيق كافة أنواع خدمات الصرف.'],
        ];

        foreach ($skills as $skillData) {
            Skill::firstOrCreate(['name' => $skillData['name']], $skillData);
        }
        $this->command->info('Skills seeded successfully!');
    }
}