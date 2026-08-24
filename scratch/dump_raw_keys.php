<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$url = 'https://script.google.com/macros/s/AKfycbzcqahW0zz10p1VqClGORtZdZxO37toNF-VSG0Z4A3DYJ6IuZnqxtBJ1iKYV5ISZzEY/exec?action=peserta';
$res = Illuminate\Support\Facades\Http::withoutVerifying()->get($url);
$data = $res->json();

echo "=== RAW DUMP OF ROWS 0, 1, 2, 3 (Sheet Rows 2, 3, 4, 5) ===\n\n";

for ($i = 0; $i <= 4; $i++) {
    echo "--- Index {$i} (Sheet Row " . ($i + 2) . ") ---\n";
    print_r($data[$i] ?? []);
    echo "\n";
}
