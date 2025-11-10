<?php

namespace App\Filament\Manager\Widgets;

use App\Models\Project;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProjectProgressChart extends ChartWidget
{
    protected static ?string $heading = 'متوسط تقدم المشاريع';
    protected static ?int $sort = 2; // لترتيب ظهور الـ Widget

    protected function getType(): string
    {
        return 'bar'; // أو 'line'
    }

    protected function getData(): array
    {
        $managerId = auth()->id();

        // جلب المشاريع التي يديرها المدير
        $projects = Project::where('manager_user_id', $managerId)->get();

        $labels = $projects->pluck('name')->toArray();
        $progressData = [];

        foreach ($projects as $project) {
            // حساب متوسط التقدم للمهام التابعة للمشروع
            $avgProgress = $project->tasks()->avg('progress') ?? 0;
            $progressData[] = round($avgProgress, 0);
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'متوسط نسبة الإنجاز (%)',
                    'data' => $progressData,
                    'backgroundColor' => '#3B82F6', // لون أزرق
                    'borderColor' => '#2563EB',
                    'borderWidth' => 1,
                ],
            ],
        ];
    }
}