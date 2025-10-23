<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;
use App\Models\ProjectInvestorLink;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class ProjectInvestorLinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jasim = User::where('username', 'jasim.investor')->first();
        $project1 = Project::where('name', 'مجمع الواحة السكني')->first();
        $project2 = Project::where('name', 'برج الأفق التجاري')->first();
        $project4 = Project::where('name', 'مركز اللياقة البدنية')->first();

        if (!$jasim || !$project1 || !$project2 || !$project4) {
            $this->command->error('Investor or Projects not found for ProjectInvestorLinkSeeder! Please run UserSeeder and ProjectSeeder first.');
            return;
        }

        $linksData = [
            ['project_id' => $project1->id, 'investor_user_id' => $jasim->id, 'investment_amount' => 5000000.00],
            ['project_id' => $project2->id, 'investor_user_id' => $jasim->id, 'investment_amount' => 10000000.00],
            ['project_id' => $project4->id, 'investor_user_id' => $jasim->id, 'investment_amount' => 2000000.00],
        ];

        // Add another investor for more data
        $anotherInvestor = User::firstOrCreate(
            ['email' => 'investor.new@app.com'],
            [
                'first_name' => 'ليلى', 'last_name' => 'المستثمرة', 'username' => 'layla.investor',
                'password' => Hash::make('password'), 'gender' => 'female', 'nationality' => 'بحرينية',
                'profile_details' => 'مستثمرة عقارية.'
            ]
        );
        $anotherInvestorRole = Role::where('name', 'Investor')->first();
        if ($anotherInvestorRole) {
            $anotherInvestor->roles()->syncWithoutDetaching([$anotherInvestorRole->id]);
        }

        if ($anotherInvestor && $project1 && $project2) {
            $linksData[] = ['project_id' => $project1->id, 'investor_user_id' => $anotherInvestor->id, 'investment_amount' => 3000000.00];
            $linksData[] = ['project_id' => $project2->id, 'investor_user_id' => $anotherInvestor->id, 'investment_amount' => 4000000.00];
        }

        foreach ($linksData as $linkData) {
            ProjectInvestorLink::firstOrCreate(
                ['project_id' => $linkData['project_id'], 'investor_user_id' => $linkData['investor_user_id']],
                $linkData
            );
        }
        $this->command->info('ProjectInvestorLinks seeded successfully!');
    }
}