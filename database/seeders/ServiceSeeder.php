<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $servicesData = [
            [
                'name' => 'خدمة صيانة كهربائية عامة',
                'description' => 'صيانة وإصلاح الأعطال الكهربائية للمنازل والشركات.',
                'status' => 'نشطة',
            ],
            [
                'name' => 'خدمة تركيب شبكات مياه',
                'description' => 'تركيب شبكات مياه جديدة للمباني السكنية والتجارية.',
                'status' => 'نشطة',
            ],
            [
                'name' => 'خدمة استشارات هندسية إنشائية',
                'description' => 'تقديم استشارات متخصصة في التصميم والتحليل الإنشائي.',
                'status' => 'نشطة',
            ],
            [
                'name' => 'خدمة تصميم داخلي وخارجي',
                'description' => 'تصميم وتنسيق الديكورات الداخلية والخارجية للمشاريع.',
                'status' => 'نشطة',
            ],
            [
                'name' => 'خدمة فحص التربة والموقع',
                'description' => 'إجراء فحوصات جيوتقنية للمواقع وتقديم التقارير اللازمة.',
                'status' => 'نشطة',
            ],
            [
                'name' => 'خدمة تأجير معدات ثقيلة',
                'description' => 'تأجير رافعات، جرافات، وحفارات لمشاريع البناء.',
                'status' => 'معلقة',
            ],
        ];

        foreach ($servicesData as $serviceData) {
            Service::firstOrCreate(['name' => $serviceData['name']], $serviceData);
        }
        $this->command->info('Services seeded successfully!');
    }
}