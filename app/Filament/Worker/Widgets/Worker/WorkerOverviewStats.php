<?php

namespace App\Filament\Worker\Widgets;

use App\Models\Task;
use App\Models\Workshop;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class WorkerOverviewStats extends BaseWidget
{
    protected function getStats(): array
    {
        $workerId = Auth::id();

        $totalTasks = Task::where('assigned_to_user_id', $workerId)->count();
        $pendingTasks = Task::where('assigned_to_user_id', $workerId)->where('status', 'قيد التنفيذ')->count();
        $completedTasks = Task::where('assigned_to_user_id', $workerId)->where('status', 'مكتملة')->count();
        
        $myWorkshop = Auth::user()->workerWorkshopLinks()->with('workshop')->first();
        $myWorkshopName = $myWorkshop->workshop->name ?? 'غير معين لورشة';

        return [
            Stat::make('ورشتي الحالية', $myWorkshopName)
                ->description('الورشة المعين بها حالياً')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('info'),
            Stat::make('إجمالي المهام', $totalTasks)
                ->description('جميع المهام المعينة لك')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('primary'),
            Stat::make('مهام قيد التنفيذ', $pendingTasks)
                ->description('المهام التي تعمل عليها حالياً')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning'),
            Stat::make('مهام مكتملة', $completedTasks)
                ->description('المهام التي أنجزتها')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }
}