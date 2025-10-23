<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ServiceRequest;
use App\Models\Service;
use App\Models\User;
use Carbon\Carbon;

class ServiceRequestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::where('username', 'admin.sys')->first();
        $jasimInvestor = User::where('username', 'jasim.investor')->first();
        $fahadManager = User::where('username', 'fahad.manager')->first();
        $lanaArchitect = User::where('username', 'lana.architect')->first();

        $electricalService = Service::where('name', 'خدمة صيانة كهربائية عامة')->first();
        $plumbingService = Service::where('name', 'خدمة تركيب شبكات مياه')->first();
        $architecturalDesignService = Service::where('name', 'خدمة تصميم داخلي وخارجي')->first();
        $soilTestingService = Service::where('name', 'خدمة فحص التربة والموقع')->first();

        if (!$admin || !$jasimInvestor || !$fahadManager || !$lanaArchitect || !$electricalService || !$plumbingService || !$architecturalDesignService || !$soilTestingService) {
            $this->command->error('Some dependencies for ServiceRequestSeeder not found! Please run UserSeeder and ServiceSeeder first.');
            return;
        }

        $requestsData = [
            // Request 1: Admin needs electrical maintenance
            [
                'service_id' => $electricalService->id,
                'user_id' => $admin->id,
                'details' => 'صيانة عاجلة للوحة التحكم الرئيسية في المكتب.',
                'request_date' => Carbon::parse('2024-06-10'),
                'status' => 'قيد الانتظار',
                'response_details' => null,
            ],
            // Request 2: Investor needs plumbing for new property
            [
                'service_id' => $plumbingService->id,
                'user_id' => $jasimInvestor->id,
                'details' => 'طلب تركيب شبكة سباكة لفيلا جديدة قيد الإنشاء.',
                'request_date' => Carbon::parse('2024-05-20'),
                'status' => 'تمت الموافقة',
                'response_details' => 'تم تحديد موعد للمعاينة الأولية بتاريخ 25/05/2024.',
            ],
            // Request 3: Manager needs architectural design
            [
                'service_id' => $architecturalDesignService->id,
                'user_id' => $fahadManager->id,
                'details' => 'تصميم داخلي لمكتب المدير العام الجديد.',
                'request_date' => Carbon::parse('2024-06-01'),
                'status' => 'قيد التنفيذ',
                'response_details' => 'فريق التصميم يعمل على المقترحات الأولية، سيتم التواصل خلال أسبوع.',
            ],
            // Request 4: Architect needs soil testing for a client
            [
                'service_id' => $soilTestingService->id,
                'user_id' => $lanaArchitect->id,
                'details' => 'فحص تربة لموقع مشروع سكني جديد لعميل.',
                'request_date' => Carbon::parse('2024-06-15'),
                'status' => 'مكتمل',
                'response_details' => 'تم إرسال تقرير فحص التربة عبر البريد الإلكتروني.',
            ],
            // Request 5: Admin needs another electrical service
            [
                'service_id' => $electricalService->id,
                'user_id' => $admin->id,
                'details' => 'فحص دوري لأنظمة الإضاءة في صالة الاستقبال.',
                'request_date' => Carbon::parse('2024-07-01'),
                'status' => 'قيد الانتظار',
                'response_details' => null,
            ],
            // Request 6: Investor needs another plumbing service
            [
                'service_id' => $plumbingService->id,
                'user_id' => $jasimInvestor->id,
                'details' => 'صيانة شاملة لشبكة المياه في منزل قديم.',
                'request_date' => Carbon::parse('2024-07-05'),
                'status' => 'مرفوض',
                'response_details' => 'الخدمة غير متوفرة حالياً في منطقتكم.',
            ],
        ];

        foreach ($requestsData as $requestData) {
            ServiceRequest::create($requestData);
        }
        $this->command->info('ServiceRequests seeded successfully!');
    }
}