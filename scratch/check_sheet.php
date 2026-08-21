<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$res = Illuminate\Support\Facades\Http::withoutVerifying()->get('https://sheetdb.io/api/v1/71445zve8u6f7');
$data = $res->json();

echo "SheetDB total rows: " . count($data) . "\n";
if (!empty($data)) {
    echo "Row 0 keys:\n";
    print_r(array_keys($data[0]));
    echo "\nSample rows:\n";
    print_r($data);
}
