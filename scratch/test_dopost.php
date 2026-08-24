<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$url = 'https://script.google.com/macros/s/AKfycbzcqahW0zz10p1VqClGORtZdZxO37toNF-VSG0Z4A3DYJ6IuZnqxtBJ1iKYV5ISZzEY/exec';

// Test 1: POST as form-params / json with nim/email/nama + status
$testPayloads = [
    [
        'nim' => '20240801150',
        'status' => 'valid',
        'nomor_kursi' => 'TI-01',
    ],
    [
        'email' => 'Dilanfajar06@gmail.com',
        'status' => 'valid',
        'nomor_kursi' => 'TI-01',
    ],
    [
        'action' => 'update',
        'nim' => '20240801150',
        'status' => 'valid',
    ],
    [
        'nim' => '20240801150',
        'updates' => [
            'Status Pembayaran' => 'valid',
            'Nomor Kursi' => 'TI-01'
        ]
    ]
];

foreach ($testPayloads as $idx => $payload) {
    echo "--- Test {$idx} ---\n";
    $res = Illuminate\Support\Facades\Http::timeout(10)->withoutVerifying()->post($url, $payload);
    echo "Status: " . $res->status() . "\n";
    echo "Body: " . $res->body() . "\n\n";
}
