<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$url2 = 'https://script.google.com/macros/s/AKfycbyhDXOel6JVLB5GvVk4poOpo3WfO_Xwn0z08tv5KGymb/exec';

echo "Testing Deployment 2 GET {$url2}...\n";
$resGet = Illuminate\Support\Facades\Http::timeout(10)->withoutVerifying()->get($url2);
echo "Status: " . $resGet->status() . "\n";
echo "Body snippet: " . substr(strip_tags($resGet->body()), 0, 200) . "\n\n";

echo "Testing Deployment 2 POST with email=Dilanfajar05@gmail.com, status=valid...\n";
$resPost = Illuminate\Support\Facades\Http::timeout(15)->withoutVerifying()->post($url2, [
    'email'  => 'Dilanfajar05@gmail.com',
    'status' => 'valid',
    'nim'    => '20240801150',
]);
echo "Status: " . $resPost->status() . "\n";
echo "Body snippet: " . substr(strip_tags($resPost->body()), 0, 200) . "\n\n";
