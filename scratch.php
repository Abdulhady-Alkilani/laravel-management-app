<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$svc = new \App\Services\AiCvScoringService();
$ref = new ReflectionMethod($svc, 'extractScoreAndReason');
$ref->setAccessible(true);

$contents = [
    '{ "score": 90, "reason": "مؤهلات وخبرات ممتازة في مجال البناء" }',
    '{ "score": 90, "reason": "تعليق يحتوي على "اقتباسات" داخلية" }',
    "{\n  \"score\": 90,\n  \"reason\": \"تعليق آخر\"}"
];

foreach ($contents as $content) {
    echo "Content: " . $content . "\n";
    var_dump($ref->invoke($svc, $content));
    echo "-------\n";
}
