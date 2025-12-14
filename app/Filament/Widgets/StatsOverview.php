<?php

namespace App\Filament\Widgets;

use App\Models\Cv;
use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\Workshop; // <== استيراد Model الورشة
use App\Models\ServiceRequest; // <== استيراد Model طلبات الخدمات
use App\Models\NewServiceProposal; // <== استيراد Model اقتراحات الخدمات
use App\Models\Report; // <== استيراد Model التقارير
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon; // لاستخدام التواريخ في الإحصائيات

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        // إحصائيات أساسية
        $totalUsers = User::count();
        $totalProjects = Project::count();
        $totalCvs = Cv::count();
        $totalTasks = Task::count();

        // إحصائيات إضافية للمشاريع
        $projectsInProgress = Project::where('status', 'قيد التنفيذ')->count();
        $overdueProjects = Project::where('end_date_planned', '<', Carbon::today())->where('status', '!=', 'مكتمل')->count();

        // إحصائيات للورشات
        $totalWorkshops = Workshop::count();
        $activeWorkshops = Workshop::whereNotNull('project_id')->count(); // الورشات المرتبطة بمشاريع

        // إحصائيات للمهام
        $overdueTasks = Task::where('end_date_planned', '<', Carbon::today())->whereNotIn('status', ['مكتملة', 'متوقفة'])->count();
        $completedTasksToday = Task::where('actual_end_date', Carbon::today())->where('status', 'مكتملة')->count();

        // إحصائيات لطلبات الخدمات
        $totalServiceRequests = ServiceRequest::count();
        $pendingServiceRequests = ServiceRequest::where('status', 'قيد الانتظار')->count();

        // إحصائيات لاقتراحات الخدمات
        $totalProposals = NewServiceProposal::count();
        $pendingProposals = NewServiceProposal::where('status', 'قيد المراجعة')->count();

        // إحصائيات للتقارير
        $totalReports = Report::count();
        $pendingReports = Report::where('report_status', 'معلقة')->count();


        return [
            Stat::make('إجمالي المستخدمين', $totalUsers)
                ->description('عدد المستخدمين المسجلين في النظام')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),

            Stat::make('إجمالي المشاريع', $totalProjects)
                ->description('عدد المشاريع المسجلة في النظام')
                ->descriptionIcon('heroicon-m-folder-open')
                ->color('primary'),

            Stat::make('مشاريع قيد التنفيذ', $projectsInProgress)
                ->description('المشاريع التي يتم العمل عليها حالياً')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('blue'),

            Stat::make('مشاريع متأخرة', $overdueProjects)
                ->description('المشاريع التي تجاوزت تاريخ الانتهاء المخطط')
                ->descriptionIcon('heroicon-m-calendar-days')
                ->color('danger'),

            Stat::make('إجمالي الورشات', $totalWorkshops)
                ->description('عدد ورشات العمل المسجلة')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('purple'),

            Stat::make('ورشات مرتبطة بمشاريع', $activeWorkshops)
                ->description('الورشات العاملة حالياً في المشاريع')
                ->descriptionIcon('heroicon-m-link')
                ->color('teal'),

            Stat::make('إجمالي المهام', $totalTasks)
                ->description('جميع المهام المخطط لها')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('warning'),

            Stat::make('مهام متأخرة', $overdueTasks)
                ->description('المهام التي تجاوزت تاريخ الانتهاء')
                ->descriptionIcon('heroicon-m-clock')
                ->color('danger'),

            Stat::make('مهام مكتملة اليوم', $completedTasksToday)
                ->description('المهام التي اكتملت في هذا اليوم')
                ->descriptionIcon('heroicon-m-check-badge')
                ->color('success'),
            
            Stat::make('إجمالي السير الذاتية', $totalCvs)
                ->description('عدد السير الذاتية المقدمة')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('gray'),
            
            Stat::make('طلبات خدمات جديدة', $totalServiceRequests)
                ->description('إجمالي طلبات المستخدمين للخدمات')
                ->descriptionIcon('heroicon-m-hand-raised')
                ->color('orange'),
            
            Stat::make('طلبات خدمات قيد الانتظار', $pendingServiceRequests)
                ->description('طلبات الخدمات التي تنتظر المعالجة')
                ->descriptionIcon('heroicon-m-question-mark-circle')
                ->color('yellow'),
            
            Stat::make('مقترحات خدمات جديدة', $totalProposals)
                ->description('إجمالي مقترحات الخدمات المقدمة')
                ->descriptionIcon('heroicon-m-light-bulb')
                ->color('cyan'),
            
            Stat::make('مقترحات قيد المراجعة', $pendingProposals)
                ->description('مقترحات الخدمات التي تنتظر المراجعة')
                ->descriptionIcon('heroicon-m-magnifying-glass')
                ->color('lime'),

            Stat::make('إجمالي التقارير', $totalReports)
                ->description('جميع التقارير المقدمة في النظام')
                ->descriptionIcon('heroicon-m-clipboard-document-list')
                ->color('red'),

            Stat::make('تقارير معلقة للمراجعة', $pendingReports)
                ->description('التقارير التي لم تتم مراجعتها بعد')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('rose'),
        ];
    }
}