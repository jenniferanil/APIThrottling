<?php

$referrer = $_SERVER['HTTP_REFERER'];
$maxRequestsPerSecond = 1; // Change this value as per requirement

function exceedsRateLimit($referrer, $maxRequestsPerSecond) {
    $filename = 'counts.txt';
    $requestCounts = [];

    if (file_exists($filename)) {
        $requestCounts = json_decode(file_get_contents($filename), true);
    }

    $currentTime = time();
    $requestCounts[$referrer] = isset($requestCounts[$referrer]) ? $requestCounts[$referrer] : [];

    foreach ($requestCounts[$referrer] as $timestamp => $count) {
        if ($currentTime - $timestamp > 1) {
            unset($requestCounts[$referrer][$timestamp]);
        }
    }

    $requestCount = count($requestCounts[$referrer]);
    if ($requestCount >= $maxRequestsPerSecond) {
        return true;
    }

    $requestCounts[$referrer][$currentTime] = $requestCount + 1;
    file_put_contents($filename, json_encode($requestCounts));

    return false;
}

if (exceedsRateLimit($referrer, $maxRequestsPerSecond)) {
    header('HTTP/1.1 429 Too Many Requests');
    echo "Request limit exceeded. Please try again later.";
    exit;
}

echo "Successfully submitted the request.";
