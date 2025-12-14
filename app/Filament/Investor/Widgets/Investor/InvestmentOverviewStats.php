<?php

namespace App\Filament\Investor\Widgets;

use App\Models\Project;
use App\Models\ProjectInvestorLink;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class InvestmentOverviewStats extends BaseWidget
{
    protected function getStats(): array
    {
        $investorId = Auth::id();

        $investedProjectsCount = ProjectInvestorLink::where('investor_user_id', $investorId)->count(); // <== هنا التعديل
        $totalInvestmentAmount = ProjectInvestorLink::where('investor_user_id', $investorId)->sum('investment_amount'); // <== هنا التعديل

        $activeInvestments = ProjectInvestorLink::where('investor_user_id', $investorId) // <== هنا التعديل
            ->whereHas('project', fn ($query) => $query->where('status', 'قيد التنفيذ'))
            ->count();
        
        $completedInvestments = ProjectInvestorLink::where('investor_user_id', $investorId) // <== هنا التعديل
            ->whereHas('project', fn ($query) => $query->where('status', 'مكتمل'))
            ->count();

        return [
            Stat::make('إجمالي المشاريع المستثمر بها', $investedProjectsCount)
                ->description('عدد المشاريع التي قمت بتمويلها')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('primary'),
            Stat::make('إجمالي مبلغ الاستثمار', number_format($totalInvestmentAmount, 2) . 'SYP')
                ->description('إجمالي استثماراتك في المشاريع')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
            Stat::make('استثمارات نشطة', $activeInvestments)
                ->description('مشاريعك قيد التنفيذ')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('info'),
            Stat::make('استثمارات مكتملة', $completedInvestments)
                ->description('مشاريعك التي تم إنجازها')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('teal'),
        ];
    }
}