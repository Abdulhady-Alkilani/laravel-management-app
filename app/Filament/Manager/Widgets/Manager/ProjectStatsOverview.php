<?php

namespace App\Filament\Manager\Widgets;

use App\Models\Project;
use App\Models\Task;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class ProjectStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $managerId = auth()->id();

        // إجمالي المشاريع التي يديرها المدير
        $totalProjects = Project::where('manager_user_id', $managerId)->count();
        $activeProjects = Project::where('manager_user_id', $managerId)->where('status', 'قيد التنفيذ')->count();
        $completedProjects = Project::where('manager_user_id', $managerId)->where('status', 'مكتمل')->count();

        // المهام المتأخرة ضمن مشاريع المدير
        $overdueTasks = Task::whereHas('project', fn ($query) => $query->where('manager_user_id', $managerId))
                            ->where('end_date_planned', '<', Carbon::today())
                            ->whereNotIn('status', ['مكتملة', 'متوقفة'])
                            ->count();

        // إجمالي الميزانية للمشاريع قيد التنفيذ
        $totalBudget = Project::where('manager_user_id', $managerId)
                                ->where('status', 'قيد التنفيذ')
                                ->sum('budget');

        return [
            Stat::make('إجمالي المشاريع', $totalProjects)
                ->description('جميع المشاريع التي تديرها')
                ->descriptionIcon('heroicon-m-folder-open')
                ->color('primary'),
            Stat::make('مشاريع قيد التنفيذ', $activeProjects)
                ->description('المشاريع التي ما زالت تحت العمل')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info'),
            Stat::make('مشاريع مكتملة', $completedProjects)
                ->description('المشاريع التي تم إنجازها')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('مهام متأخرة', $overdueTasks)
                ->description('مهام تجاوزت تاريخ الانتهاء')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),
            Stat::make('إجمالي الميزانية (قيد التنفيذ)', number_format($totalBudget, 2) . ' SYP')
                ->description('للمشاريع التي تديرها حالياً')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning'),
        ];
    }
}