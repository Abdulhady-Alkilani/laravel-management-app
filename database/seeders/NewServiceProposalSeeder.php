<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NewServiceProposal;
use App\Models\User;
use Carbon\Carbon;

class NewServiceProposalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fouad = User::where('username', 'fouad.service')->first(); // <== جلب المستخدم الجديد
        $hana = User::where('username', 'hana.request')->first();     // <== جلب المستخدم الجديد
        $alyaReviewer = User::where('username', 'alya.reviewer')->first();

        if (!$fouad || !$hana || !$alyaReviewer) {
            $this->command->error('Some proposer or reviewer users not found for NewServiceProposalSeeder! Please run UserSeeder first.');
            return;
        }

        $proposalsData = [
            // اقتراحات من Fouad (Service Proposer)
            [
                'proposed_service_name' => 'خدمة تنظيف ما بعد البناء',
                'service_details' => 'تنظيف شامل للمباني بعد الانتهاء من أعمال التشييد والتشطيبات.',
                'user_id' => $fouad->id,
                'proposal_date' => Carbon::parse('2024-06-01'),
                'status' => 'قيد المراجعة',
                'reviewer_user_id' => $alyaReviewer->id,
                'review_comments' => 'فكرة ممتازة، تحتاج لدراسة جدوى السوق.',
            ],
            [
                'proposed_service_name' => 'خدمة عزل الصوت للمباني',
                'service_details' => 'تركيب مواد عازلة للصوت في الجدران والأسقف للمباني السكنية والتجارية.',
                'user_id' => $fouad->id,
                'proposal_date' => Carbon::parse('2024-05-15'),
                'status' => 'مرفوض',
                'reviewer_user_id' => $alyaReviewer->id,
                'review_comments' => 'الطلب لا يتوافق مع استراتيجية الشركة الحالية.',
            ],
            // اقتراحات من Hana (Service Proposer)
            [
                'proposed_service_name' => 'خدمة تصميم لاندسكيب',
                'service_details' => 'تصميم وتنفيذ تنسيق حدائق ومساحات خضراء للمنازل والفيلات.',
                'user_id' => $hana->id,
                'proposal_date' => Carbon::parse('2024-07-05'),
                'status' => 'قيد المراجعة',
                'reviewer_user_id' => null,
                'review_comments' => null,
            ],
        ];

        foreach ($proposalsData as $proposalData) {
            NewServiceProposal::firstOrCreate(
                [
                    'proposed_service_name' => $proposalData['proposed_service_name'],
                    'user_id' => $proposalData['user_id']
                ],
                $proposalData
            );
        }
        $this->command->info('NewServiceProposals seeded successfully!');
    }
}