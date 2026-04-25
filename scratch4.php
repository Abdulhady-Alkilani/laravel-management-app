<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$svc = new \App\Services\AiCvScoringService();
$ref = new ReflectionMethod($svc, 'extractScoreAndReason');
$ref->setAccessible(true);

$content = '{
  "score": 90,
  "reason": "نعم يوجد "اقتباس" غير مهرب في النص"}';

var_dump($ref->invoke($svc, $content));
