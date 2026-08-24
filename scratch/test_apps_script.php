<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$url = env('SHEETDB_URL');
echo "SHEETDB_URL: {$url}\n";

$res = Illuminate\Support\Facades\Http::withoutVerifying()->get($url);
echo "Status code: " . $res->status() . "\n";
echo "Response body sample:\n";
echo substr($res->body(), 0, 1000) . "\n";
