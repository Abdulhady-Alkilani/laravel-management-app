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
        $admin = User::where('username', 'admin.sys')->first();
        $fahadManager = User::where('username', 'fahad.manager')->first();
        $alyaReviewer = User::where('username', 'alya.reviewer')->first();
        $lanaArchitect = User::where('username', 'lana.architect')->first();

        if (!$admin || !$fahadManager || !$alyaReviewer || !$lanaArchitect) {
            $this->command->error('Some proposer or reviewer users not found for NewServiceProposalSeeder! Please run UserSeeder first.');
            return;
        }

        $proposalsData = [
            // Proposal 1: From Admin
            [
                'proposed_service_name' => 'خدمة إدارة المرافق',
                'service_details' => 'إدارة شاملة للمرافق بعد البناء (صيانة، نظافة، أمن).',
                'user_id' => $admin->id,
                'proposal_date' => Carbon::parse('2024-03-01'),
                'status' => 'تمت الموافقة',
                'reviewer_user_id' => $alyaReviewer->id,
                'review_comments' => 'خدمة ضرورية وتوسعية، ينصح بالتنفيذ.',
            ],
            // Proposal 2: From Manager
            [
                'proposed_service_name' => 'خدمة التقييم العقاري',
                'service_details' => 'تقديم خدمات تقييم عقاري احترافية للممتلكات.',
                'user_id' => $fahadManager->id,
                'proposal_date' => Carbon::parse('2024-04-10'),
                'status' => 'قيد المراجعة',
                'reviewer_user_id' => null, // لم تتم مراجعتها بعد
                'review_comments' => null,
            ],
            // Proposal 3: From Architect
            [
                'proposed_service_name' => 'ورش عمل تصميم معماري',
                'service_details' => 'تنظيم ورش عمل ودورات تدريبية في التصميم المعماري للطلاب والمهندسين الجدد.',
                'user_id' => $lanaArchitect->id,
                'proposal_date' => Carbon::parse('2024-05-01'),
                'status' => 'مرفوض',
                'reviewer_user_id' => $alyaReviewer->id,
                'review_comments' => 'فكرة جيدة لكنها خارج نطاق تركيز الشركة الحالي.',
            ],
            // Proposal 4: From Admin
            [
                'proposed_service_name' => 'خدمة الطاقة المتجددة للمباني',
                'service_details' => 'تصميم وتركيب أنظمة الطاقة الشمسية للمباني السكنية والتجارية.',
                'user_id' => $admin->id,
                'proposal_date' => Carbon::parse('2024-06-01'),
                'status' => 'قيد المراجعة',
                'reviewer_user_id' => null,
                'review_comments' => null,
            ],
            // Proposal 5: From Manager
            [
                'proposed_service_name' => 'خدمة الاستدامة البيئية',
                'service_details' => 'تقديم استشارات لدمج الممارسات المستدامة في مشاريع البناء.',
                'user_id' => $fahadManager->id,
                'proposal_date' => Carbon::parse('2024-06-20'),
                'status' => 'قيد المراجعة',
                'reviewer_user_id' => $alyaReviewer->id,
                'review_comments' => 'تحتاج لدراسة جدوى أعمق قبل الموافقة.',
            ],
            // Proposal 6: From Architect
            [
                'proposed_service_name' => 'خدمة النمذجة ثلاثية الأبعاد للمباني (BIM)',
                'service_details' => 'إنشاء نماذج معلومات البناء ثلاثية الأبعاد للمشاريع.',
                'user_id' => $lanaArchitect->id,
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