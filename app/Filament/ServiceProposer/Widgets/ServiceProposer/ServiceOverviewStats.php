<?php

namespace App\Filament\ServiceProposer\Widgets;

use App\Models\ServiceRequest;
use App\Models\NewServiceProposal;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class ServiceOverviewStats extends BaseWidget
{
    protected function getStats(): array
    {
        $userId = Auth::id();

        $totalRequests = ServiceRequest::where('user_id', $userId)->count();
        $pendingRequests = ServiceRequest::where('user_id', $userId)->where('status', 'قيد الانتظار')->count();
        $totalProposals = NewServiceProposal::where('user_id', $userId)->count();
        $pendingProposals = NewServiceProposal::where('user_id', $userId)->where('status', 'قيد المراجعة')->count();

        return [
            Stat::make('إجمالي طلبات الخدمات', $totalRequests)
                ->description('عدد طلبات الخدمة التي قدمتها')
                ->descriptionIcon('heroicon-m-hand-raised')
                ->color('primary'),
            Stat::make('طلبات قيد الانتظار', $pendingRequests)
                ->description('طلباتك التي لم تتم معالجتها بعد')
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),
            Stat::make('إجمالي اقتراحات الخدمات', $totalProposals)
                ->description('عدد اقتراحات الخدمات التي قدمتها')
                ->descriptionIcon('heroicon-m-light-bulb')
                ->color('info'),
            Stat::make('اقتراحات قيد المراجعة', $pendingProposals)
                ->description('اقتراحاتك التي تنتظر المراجعة')
                ->descriptionIcon('heroicon-m-magnifying-glass')
                ->color('teal'),
        ];
    }
}