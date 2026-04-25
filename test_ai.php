<?php

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "=== Testing AI CV Scoring Service ===\n\n";

$service = new \App\Services\AiCvScoringService();

$cvData = [
    'skills' => 'AutoCAD, Revit, Project Management',
    'experience' => '5 سنوات في الهندسة المدنية',
    'education' => 'بكالوريوس هندسة مدنية',
    'profile_details' => 'مهندس موقع',
];

echo "Sending request...\n";
$result = $service->scoreCv($cvData);

if ($result !== null) {
    echo "\n✅ SUCCESS!\n";
    echo "Score: {$result['score']}/100\n";
    echo "Reason: {$result['reason']}\n";
} else {
    echo "\n❌ FAILED - Check storage/logs/laravel.log for details\n";
}
