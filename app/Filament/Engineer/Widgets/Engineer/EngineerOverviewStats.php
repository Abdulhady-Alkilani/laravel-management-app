<?php

namespace App\Filament\Engineer\Widgets;

use App\Models\Task;
use App\Models\Report;
use App\Models\Project;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class EngineerOverviewStats extends BaseWidget
{
    protected function getStats(): array
    {
        $engineerId = Auth::id();

        $totalAssignedTasks = Task::where('assigned_to_user_id', $engineerId)->count();
        $tasksInProgress = Task::where('assigned_to_user_id', $engineerId)->where('status', 'قيد التنفيذ')->count();
        $completedTasks = Task::where('assigned_to_user_id', $engineerId)->where('status', 'مكتملة')->count();
        
        $overdueTasks = Task::where('assigned_to_user_id', $engineerId)
                            ->where('end_date_planned', '<', Carbon::today())
                            ->whereNotIn('status', ['مكتملة', 'متوقفة'])
                            ->count();

        $projectsInvolved = Project::whereHas('tasks', fn (Builder $query) => $query->where('assigned_to_user_id', $engineerId))
                                ->distinct()->count();

        return [
            Stat::make('إجمالي المهام', $totalAssignedTasks)
                ->description('جميع المهام المعينة لك')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),
            Stat::make('مهام قيد التنفيذ', $tasksInProgress)
                ->description('المهام التي تعمل عليها حالياً')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning'),
            Stat::make('مهام مكتملة', $completedTasks)
                ->description('المهام التي أنجزتها')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('مهام متأخرة', $overdueTasks)
                ->description('مهام تجاوزت تاريخ الانتهاء')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),
            Stat::make('مشاريع مشارك بها', $projectsInvolved)
                ->description('المشاريع التي تساهم فيها')
                ->descriptionIcon('heroicon-m-folder-open')
                ->color('info'),
        ];
    }
}