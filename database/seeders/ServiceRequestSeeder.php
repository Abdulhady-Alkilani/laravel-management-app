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
        $fouad = User::where('username', 'fouad.service')->first(); // <== جلب المستخدم الجديد
        $hana = User::where('username', 'hana.request')->first();     // <== جلب المستخدم الجديد
        $electricalService = Service::where('name', 'خدمة صيانة كهربائية عامة')->first();
        $plumbingService = Service::where('name', 'خدمة تركيب شبكات مياه')->first();
        $designService = Service::where('name', 'خدمة تصميم داخلي وخارجي')->first();

        if (!$fouad || !$hana || !$electricalService || !$plumbingService || !$designService) {
            $this->command->error('Some dependencies for ServiceRequestSeeder not found! Please run UserSeeder and ServiceSeeder first.');
            return;
        }

        $requestsData = [
            // طلبات من Fouad (Service Proposer)
            [
                'service_id' => $electricalService->id,
                'user_id' => $fouad->id,
                'details' => 'صيانة نظام إضاءة المنزل الرئيسي، يوجد عطل متكرر.',
                'request_date' => Carbon::parse('2024-07-10'),
                'status' => 'قيد الانتظار',
                'response_details' => null,
            ],
            [
                'service_id' => $plumbingService->id,
                'user_id' => $fouad->id,
                'details' => 'طلب فحص شامل لشبكة المياه في العقار التجاري الجديد.',
                'request_date' => Carbon::parse('2024-06-25'),
                'status' => 'تمت الموافقة',
                'response_details' => 'تم تحديد موعد زيارة فنية يوم 28/06/2024.',
            ],
            // طلبات من Hana (Service Proposer)
            [
                'service_id' => $designService->id,
                'user_id' => $hana->id,
                'details' => 'تصميم داخلي لمساحة مكتبية جديدة، أرغب في طابع عصري.',
                'request_date' => Carbon::parse('2024-07-01'),
                'status' => 'قيد التنفيذ',
                'response_details' => 'فريق التصميم يعمل على المقترحات الأولية.',
            ],
            [
                'service_id' => $electricalService->id,
                'user_id' => $hana->id,
                'details' => 'تركيب نقاط كهرباء إضافية في غرفة المعيشة.',
                'request_date' => Carbon::parse('2024-07-15'),
                'status' => 'قيد الانتظار',
                'response_details' => null,
            ],
        ];

        foreach ($requestsData as $requestData) {
            ServiceRequest::firstOrCreate(
                [
                    'service_id' => $requestData['service_id'],
                    'user_id' => $requestData['user_id'],
                    'details' => $requestData['details']
                ],
                $requestData
            );
        }
        $this->command->info('ServiceRequests seeded successfully!');
    }
}