<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$url = 'https://script.google.com/macros/s/AKfycbzcqahW0zz10p1VqClGORtZdZxO37toNF-VSG0Z4A3DYJ6IuZnqxtBJ1iKYV5ISZzEY/exec';

// Test 1: Send payload with string NIM vs integer NIM vs email vs nama
$testPayloads = [
    'Test NIM String' => [
        'nim' => '20240801150',
        'status' => 'valid',
    ],
    'Test NIM Int' => [
        'nim' => 20240801150,
        'status' => 'valid',
    ],
    'Test Email' => [
        'email' => 'Dilanfajar06@gmail.com',
        'status' => 'valid',
    ],
    'Test Nama' => [
        'nama' => 'Admin 1',
        'status' => 'valid',
    ],
    'Test All' => [
        'nim'   => '20240801150',
        'NIM'   => '20240801150',
        'email' => 'Dilanfajar06@gmail.com',
        'Email' => 'Dilanfajar06@gmail.com',
        'nama'  => 'Admin 1',
        'Nama Lengkap' => 'Admin 1',
        'status' => 'valid',
    ]
];

foreach ($testPayloads as $label => $payload) {
    echo "--- {$label} ---\n";
    $res = Illuminate\Support\Facades\Http::timeout(20)->withoutVerifying()->post($url, $payload);
    echo "Status: " . $res->status() . "\n";
    echo "Response: " . $res->body() . "\n";
    
    // Check sheet state
    $resGet = Illuminate\Support\Facades\Http::withoutVerifying()->get($url . '?action=peserta');
    $row0 = $resGet->json()[0] ?? [];
    echo "Row 0 (Admin 1) state: Status='{$row0['Status Pembayaran']}', ID='{$row0['ID Unik']}', Mail='{$row0['Status Email']}'\n\n";
}
