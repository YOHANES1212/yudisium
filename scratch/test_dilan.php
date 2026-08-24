<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$url = 'https://script.google.com/macros/s/AKfycbzcqahW0zz10p1VqClGORtZdZxO37toNF-VSG0Z4A3DYJ6IuZnqxtBJ1iKYV5ISZzEY/exec';

$testPayloads = [
    'Nama Pemilik Rekening' => [
        'Nama Pemilik Rekening' => 'dilan',
        'nama_pemilik_rekening' => 'dilan',
        'status' => 'valid',
    ],
    'Nomor Rekening' => [
        'Nomor Rekening' => '89989',
        'nomor_rekening' => '89989',
        'status' => 'valid',
    ],
    'Email Dilan' => [
        'Email Address' => 'Dilanfajar06@gmail.com',
        'Email' => 'Dilanfajar06@gmail.com',
        'email' => 'Dilanfajar06@gmail.com',
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
    echo "Row 0 (dilan) state: Status='{$row0['Status Pembayaran']}', ID='{$row0['ID Unik']}', Mail='{$row0['Status Email']}'\n\n";
}
