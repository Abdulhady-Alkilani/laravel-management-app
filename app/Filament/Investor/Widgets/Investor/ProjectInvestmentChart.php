<?php

namespace App\Filament\Investor\Widgets;

use App\Models\ProjectInvestorLink;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProjectInvestmentChart extends ChartWidget
{
    protected static ?string $heading = 'مبالغ الاستثمار حسب المشروع';
    protected static ?int $sort = 2;

    protected function getType(): string
    {
        return 'pie';
    }

    protected function getData(): array
    {
        $investorId = Auth::id();

        $investments = ProjectInvestorLink::where('investor_user_id', $investorId) // <== هنا التعديل
            ->with('project')
            ->get();

        $labels = $investments->pluck('project.name')->toArray();
        $data = $investments->pluck('investment_amount')->toArray();

        $colors = [
            '#4F46E5', '#10B981', '#F59E0B', '#EF4444', '#6B7280', '#06B6D4', '#EAB308', '#F472B6', '#8B5CF6',
        ];
        $backgroundColors = [];
        foreach ($labels as $index => $label) {
            $backgroundColors[] = $colors[$index % count($colors)];
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'مبلغ الاستثمار (SAR)',
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                    'hoverOffset' => 4,
                ],
            ],
        ];
    }
}