<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$url = env('SHEETDB_URL', 'https://script.google.com/macros/s/AKfycbzcqahW0zz10p1VqClGORtZdZxO37toNF-VSG0Z4A3DYJ6IuZnqxtBJ1iKYV5ISZzEY/exec');

$payload = [
    'email'       => 'Dilanfajar06@gmail.com',
    'nama'        => 'Admin 1',
    'status'      => 'valid',
    'nomor_kursi' => 'TI-01',
];

echo "Sending payload to Apps Script: " . json_encode($payload) . "\n";
$res = Illuminate\Support\Facades\Http::timeout(15)->withoutVerifying()->post($url, $payload);
echo "Status: " . $res->status() . "\n";
echo "Response: " . $res->body() . "\n";
