<?php

namespace App\Filament\WorkshopSupervisor\Widgets;

use App\Models\Task;
use App\Models\Workshop; // استيراد Workshop model
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WorkerProductivityChart extends ChartWidget
{
    protected static ?string $heading = 'إنتاجية العمال (آخر 30 يوماً)';
    protected static ?int $sort = 3;

    protected function getType(): string
    {
        return 'line'; // أو 'bar'
    }

    protected function getData(): array
    {
        $supervisorId = Auth::id();
        $startDate = Carbon::today()->subDays(30);

        // جلب الورشات التي يشرف عليها المستخدم
        $myWorkshopsIds = Workshop::where('supervisor_user_id', $supervisorId)->pluck('id');

        // جلب المهام المكتملة في هذه الورشات خلال آخر 30 يوماً
        $completedTasksByWorker = Task::whereIn('workshop_id', $myWorkshopsIds)
            ->where('status', 'مكتملة')
            ->where('actual_end_date', '>=', $startDate)
            ->join('users', 'tasks.assigned_to_user_id', '=', 'users.id')
            ->select(DB::raw('CONCAT(users.first_name, " ", users.last_name) as worker_name'), DB::raw('count(*) as completed_tasks'))
            ->groupBy('worker_name')
            ->get();

        $labels = $completedTasksByWorker->pluck('worker_name')->toArray();
        $data = $completedTasksByWorker->pluck('completed_tasks')->toArray();

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'عدد المهام المكتملة',
                    'data' => $data,
                    'backgroundColor' => '#4CAF50', // أخضر
                    'borderColor' => '#388E3C',
                    'borderWidth' => 1,
                ],
            ],
        ];
    }
}