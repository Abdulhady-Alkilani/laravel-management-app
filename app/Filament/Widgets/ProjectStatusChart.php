<?php

namespace App\Filament\Widgets;

use App\Models\Project;
use Filament\Widgets\ChartWidget;
use Flowbite\Flowbite; // للتلوين

class ProjectStatusChart extends ChartWidget
{
    protected static ?string $heading = 'حالة المشاريع';
    protected static ?int $sort = 2; // لترتيب ظهور الـ Widget

    protected function getType(): string
    {
        return 'pie'; // أو 'bar'
    }

    protected function getData(): array
    {
        $projectStatuses = Project::selectRaw('status, count(*) as count')
                                ->groupBy('status')
                                ->pluck('count', 'status')
                                ->toArray();

        $labels = array_keys($projectStatuses);
        $data = array_values($projectStatuses);

        // يمكنك تخصيص الألوان حسب حالتك
        $colors = [
            'قيد التنفيذ' => '#3B82F6', // Blue
            'مكتمل' => '#10B981',      // Green
            'مخطط' => '#F59E0B',      // Amber
            'متوقف' => '#EF4444',      // Red
            'ملغى' => '#6B7280',      // Gray
        ];

        $backgroundColors = [];
        foreach ($labels as $label) {
            $backgroundColors[] = $colors[$label] ?? '#CCCCCC'; // لون افتراضي
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'عدد المشاريع',
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                    'hoverOffset' => 4, // تأثير عند التمرير
                ],
            ],
        ];
    }
}