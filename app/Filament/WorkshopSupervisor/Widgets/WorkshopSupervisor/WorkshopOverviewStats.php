<?php

namespace App\Filament\WorkshopSupervisor\Widgets;

use App\Models\Workshop;
use App\Models\Task;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WorkshopOverviewStats extends BaseWidget
{
    protected function getStats(): array
    {
        $supervisorId = Auth::id();

        // الورشات التي يشرف عليها المستخدم
        $myWorkshops = Workshop::where('supervisor_user_id', $supervisorId)->get();
        $myWorkshopsIds = $myWorkshops->pluck('id');
        $totalWorkshops = $myWorkshops->count();

        // المهام قيد التنفيذ في ورشاته
        $tasksInProgress = Task::whereIn('workshop_id', $myWorkshopsIds)
            ->where('status', 'قيد التنفيذ')
            ->count();

        // عدد العمال المرتبطين بهذه الورشات (عمال فريدون)
        $totalWorkers = User::whereHas('workerWorkshopLinks', fn ($query) => $query->whereIn('workshop_id', $myWorkshopsIds))
            ->distinct()
            ->count();
        
        // المهام المتأخرة في ورشاته
        $overdueWorkshopTasks = Task::whereIn('workshop_id', $myWorkshopsIds)
            ->where('end_date_planned', '<', Carbon::today())
            ->whereNotIn('status', ['مكتملة', 'متوقفة'])
            ->count();

        return [
            Stat::make('ورشاتي', $totalWorkshops)
                ->description('عدد الورشات التي تشرف عليها')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('primary'),
            Stat::make('مهام قيد التنفيذ', $tasksInProgress)
                ->description('المهام النشطة في ورشاتك')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info'),
            Stat::make('عمال مرتبطون', $totalWorkers)
                ->description('إجمالي العمال في ورشاتك')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),
            Stat::make('مهام ورشة متأخرة', $overdueWorkshopTasks)
                ->description('مهام ورشاتك تجاوزت تاريخ الانتهاء')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),
        ];
    }
}