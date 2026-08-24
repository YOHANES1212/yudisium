<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$url = 'https://sheetdb.io/api/v1/71445zve8u6f7';
echo "Testing SheetDB API GET {$url}...\n";
$res = Illuminate\Support\Facades\Http::withoutVerifying()->get($url);
echo "Status Code: " . $res->status() . "\n";
$data = $res->json();
if (is_array($data)) {
    echo "Total rows in SheetDB: " . count($data) . "\n";
    if (count($data) > 0) {
        echo "Row 0 sample: " . json_encode($data[0]) . "\n";
    }
} else {
    echo "Response: " . $res->body() . "\n";
}
