<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$url = 'https://script.google.com/macros/s/AKfycbzcqahW0zz10p1VqClGORtZdZxO37toNF-VSG0Z4A3DYJ6IuZnqxtBJ1iKYV5ISZzEY/exec';

// Test Admin 4
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

echo "1. Posting Admin 4 to Apps Script...\n";
$res4 = Illuminate\Support\Facades\Http::timeout(30)->withoutVerifying()->post($url, $payloadAdmin4);
echo "Status Code: " . $res4->status() . "\n";
echo "Response Body: " . $res4->body() . "\n\n";

// Test Admin 1
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

echo "2. Posting Admin 1 to Apps Script...\n";
$res1 = Illuminate\Support\Facades\Http::timeout(30)->withoutVerifying()->post($url, $payloadAdmin1);
echo "Status Code: " . $res1->status() . "\n";
echo "Response Body: " . $res1->body() . "\n\n";
