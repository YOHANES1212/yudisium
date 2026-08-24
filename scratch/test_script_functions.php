<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$url = 'https://script.google.com/macros/s/AKfycbzcqahW0zz10p1VqClGORtZdZxO37toNF-VSG0Z4A3DYJ6IuZnqxtBJ1iKYV5ISZzEY/exec';

$actions = [
    'prosesValidasiManual',
    'prosesSatuPeserta',
    'kirimEmailQR',
    'validasi',
    'proses',
    'sendEmail',
    'mail',
];

echo "=== TESTING GET REQUESTS ===\n";
foreach ($actions as $action) {
    $res = Illuminate\Support\Facades\Http::timeout(10)->withoutVerifying()->get($url, [
        'action' => $action,
        'email'  => 'Dilanfajar05@gmail.com',
        'nama'   => 'Admin 4',
        'nim'    => '20240801150',
    ]);
    echo "GET action={$action} -> Status: {$res->status()}, Body: " . substr(strip_tags($res->body()), 0, 100) . "\n";
}

echo "\n=== TESTING POST REQUESTS ===\n";
foreach ($actions as $action) {
    $res = Illuminate\Support\Facades\Http::timeout(10)->withoutVerifying()->post($url . '?action=' . $action, [
        'action' => $action,
        'email'  => 'Dilanfajar05@gmail.com',
        'nama'   => 'Admin 4',
        'nim'    => '20240801150',
        'status' => 'valid',
    ]);
    echo "POST action={$action} -> Status: {$res->status()}, Body: " . substr(strip_tags($res->body()), 0, 100) . "\n";
}
