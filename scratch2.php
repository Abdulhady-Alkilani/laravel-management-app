<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new \App\Services\AiCvScoringService();
$cvData = [
    'skills' => 'بناء، هندسة معمارية',
    'experience' => '10 سنوات في مجال البناء',
    'education' => 'بكالوريوس هندسة',
    'profile_details' => 'مهندس مبدع',
];
$result = $service->scoreCv($cvData);
echo "Result:\n";
var_dump($result);
