<?php
$cleaned = '{ "score": 88, "reason": "الخبرة ممتازة وفيها "اقتباس" داخلي" }';
if (preg_match('/["\']?(?:reason|التعليق|السبب)["\']?\s*:\s*(.*)/is', $cleaned, $match)) {
    $reason = $match[1];
    $reason = preg_replace('/^["\']/', '', ltrim($reason));
    $reason = preg_replace('/["\']?\s*\}?\s*$/', '', rtrim($reason));
    echo "Extracted reason: " . $reason . "\n";
} else {
    echo "Failed to extract reason.\n";
}
