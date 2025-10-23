<?php

namespace App\Filament\Widgets;

use App\Models\Cv;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('إجمالي المستخدمين', User::count())
                ->description('عدد المستخدمين المسجلين في النظام')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info'),
            Stat::make('إجمالي المشاريع', Project::count())
                ->description('عدد المشاريع المسجلة')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
            Stat::make('السير الذاتية', Cv::count())
                ->description('عدد السير الذاتية المقدمة')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('primary'),
            Stat::make('المهام الإجمالية', Task::count())
                ->description('جميع المهام المخطط لها والمنفذة')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('warning'),
            // يمكنك إضافة المزيد من الإحصائيات هنا
            // Stat::make('إجمالي الورشات', \App\Models\Workshop::count())
            //     ->description('عدد الورشات العاملة')
            //     ->color('info'),
        ];
    }
}