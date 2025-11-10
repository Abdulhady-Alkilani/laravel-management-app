<?php

namespace App\Filament\Reviewer\Widgets;

use App\Models\Cv;
use App\Models\NewServiceProposal;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class ReviewOverviewStats extends BaseWidget
{
    protected function getStats(): array
    {
        $pendingCvs = Cv::where('cv_status', 'قيد الانتظار')->count();
        $pendingProposals = NewServiceProposal::where('status', 'قيد المراجعة')->count();
        $approvedCvs = Cv::where('cv_status', 'تمت الموافقة')->count();
        $approvedProposals = NewServiceProposal::where('status', 'تمت الموافقة')->count();

        return [
            Stat::make('سير ذاتية تنتظر المراجعة', $pendingCvs)
                ->description('عدد السير الذاتية التي تحتاج قراراً')
                ->descriptionIcon('heroicon-m-document-magnifying-glass')
                ->color('warning'),
            Stat::make('مقترحات خدمات تنتظر المراجعة', $pendingProposals)
                ->description('عدد المقترحات الجديدة')
                ->descriptionIcon('heroicon-m-light-bulb')
                ->color('info'),
            Stat::make('سير ذاتية تمت الموافقة عليها', $approvedCvs)
                ->description('إجمالي السير الذاتية المقبولة')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
            Stat::make('مقترحات تمت الموافقة عليها', $approvedProposals)
                ->description('إجمالي المقترحات التي تم قبولها')
                ->descriptionIcon('heroicon-m-star')
                ->color('teal'),
        ];
    }
}