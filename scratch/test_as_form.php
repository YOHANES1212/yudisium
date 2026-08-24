<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$url = 'https://script.google.com/macros/s/AKfycbzcqahW0zz10p1VqClGORtZdZxO37toNF-VSG0Z4A3DYJ6IuZnqxtBJ1iKYV5ISZzEY/exec';

// Test Admin 4 with asForm()
$payloadAdmin4 = [
    'email'       => 'Dilanfajar05@gmail.com',
    'Email'       => 'Dilanfajar05@gmail.com',
    'Email Address' => 'Dilanfajar05@gmail.com',
    'nama'        => 'Admin 4',
    'Nama Lengkap' => 'Admin 4',
    'status'      => 'valid',
    'Status Pembayaran' => 'valid',
    'nomor_kursi' => 'TI-24',
];

echo "1. Posting Admin 4 via Http::asForm()->post()...\n";
$res4 = Illuminate\Support\Facades\Http::timeout(30)->withoutVerifying()->asForm()->post($url, $payloadAdmin4);
echo "Status Code: " . $res4->status() . "\n";
echo "Response Body Snippet: " . substr(strip_tags($res4->body()), 0, 200) . "\n\n";

// Test Admin 1 with asForm()
$payloadAdmin1 = [
    'nim'         => '20240801150',
    'NIM'         => '20240801150',
    'email'       => 'Dilanfajar06@gmail.com',
    'Email'       => 'Dilanfajar06@gmail.com',
    'Email Address' => 'Dilanfajar06@gmail.com',
    'nama'        => 'Admin 1',
    'Nama Lengkap' => 'Admin 1',
    'status'      => 'valid',
    'Status Pembayaran' => 'valid',
    'nomor_kursi' => 'TI-21',
];

echo "2. Posting Admin 1 via Http::asForm()->post()...\n";
$res1 = Illuminate\Support\Facades\Http::timeout(30)->withoutVerifying()->asForm()->post($url, $payloadAdmin1);
echo "Status Code: " . $res1->status() . "\n";
echo "Response Body Snippet: " . substr(strip_tags($res1->body()), 0, 200) . "\n\n";

// Inspect Sheet State
$resGet = Illuminate\Support\Facades\Http::withoutVerifying()->get($url . '?action=peserta');
$data = $resGet->json();
$row0 = $data[0] ?? []; // Admin 1
$row3 = $data[3] ?? []; // Admin 4

echo "=== RESULT IN GOOGLE SHEETS ===\n";
echo "Admin 1 (Row 2): Status='{$row0['Status Pembayaran']}', ID='{$row0['ID Unik']}', Mail='{$row0['Status Email']}'\n";
echo "Admin 4 (Row 5): Status='{$row3['Status Pembayaran']}', ID='{$row3['ID Unik']}', Mail='{$row3['Status Email']}'\n";
