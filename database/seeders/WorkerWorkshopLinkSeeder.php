<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WorkerWorkshopLink;
use App\Models\User;
use App\Models\Workshop;
use Carbon\Carbon;

class WorkerWorkshopLinkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $khalid = User::where('username', 'khalid.worker')->first();
        $nora = User::where('username', 'nora.tech')->first();
        $yousef = User::where('username', 'yousef.plumber')->first();
        $saeed = User::where('username', 'saeed.supervisor')->first(); // مشرف ورشة

        $concreteWorkshop = Workshop::where('name', 'ورشة الهياكل الخرسانية')->first();
        $electricalWorkshop = Workshop::where('name', 'ورشة التمديدات الكهربائية')->first();
        $plumbingWorkshop = Workshop::where('name', 'ورشة أنظمة السباكة')->first();
        $finishingWorkshop = Workshop::where('name', 'ورشة التشطيبات الداخلية')->first();
        $metalWorkWorkshop = Workshop::where('name', 'ورشة الحدادة واللحام')->first();

        // Ensure all dependencies exist
        if (!$khalid || !$nora || !$yousef || !$saeed || !$concreteWorkshop || !$electricalWorkshop || !$plumbingWorkshop || !$finishingWorkshop || !$metalWorkWorkshop) {
            $this->command->error('Some users or workshops not found for WorkerWorkshopLinkSeeder! Please run UserSeeder and WorkshopSeeder first.');
            return;
        }

        $linksData = [
            // Khalid (Worker)
            ['worker_id' => $khalid->id, 'workshop_id' => $concreteWorkshop->id, 'assigned_date' => Carbon::parse('2024-01-20')],
            ['worker_id' => $khalid->id, 'workshop_id' => $finishingWorkshop->id, 'assigned_date' => Carbon::parse('2024-05-10')],
            // Nora (Technician - Electrical)
            ['worker_id' => $nora->id, 'workshop_id' => $electricalWorkshop->id, 'assigned_date' => Carbon::parse('2023-09-01')],
            // Yousef (Plumber)
            ['worker_id' => $yousef->id, 'workshop_id' => $plumbingWorkshop->id, 'assigned_date' => Carbon::parse('2024-02-15')],
            // Saeed (Supervisor)
            ['worker_id' => $saeed->id, 'workshop_id' => $metalWorkWorkshop->id, 'assigned_date' => Carbon::parse('2024-03-01')],
            ['worker_id' => $saeed->id, 'workshop_id' => $concreteWorkshop->id, 'assigned_date' => Carbon::parse('2024-01-10')],
        ];

        foreach ($linksData as $linkData) {
            WorkerWorkshopLink::firstOrCreate(
                ['worker_id' => $linkData['worker_id'], 'workshop_id' => $linkData['workshop_id']],
                $linkData
            );
        }
        $this->command->info('WorkerWorkshopLinks seeded successfully!');
    }
}