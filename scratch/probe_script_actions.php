<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$baseUrl = 'https://script.google.com/macros/s/AKfycbzcqahW0zz10p1VqClGORtZdZxO37toNF-VSG0Z4A3DYJ6IuZnqxtBJ1iKYV5ISZzEY/exec';

$actions = [
    'none' => '',
    'peserta' => '?action=peserta',
    'update' => '?action=update',
    'valid' => '?action=valid',
    'pembayaran' => '?action=pembayaran',
    'verifikasi' => '?action=verifikasi',
    'admin' => '?action=admin',
    'edit' => '?action=edit',
    'scan' => '?action=scan',
];

$payload = [
    'email' => 'Dilanfajar05@gmail.com',
    'nama'  => 'Admin 4',
    'status' => 'valid',
    'status_pembayaran' => 'valid',
];

foreach ($actions as $label => $queryString) {
    $url = $baseUrl . $queryString;
    echo "--- Testing POST {$label} ({$url}) ---\n";
    $res = Illuminate\Support\Facades\Http::timeout(10)->withoutVerifying()->post($url, $payload);
    echo "Status: " . $res->status() . "\n";
    $body = $res->body();
    if (str_contains($body, 'Buka dari hasil scan')) {
        echo "Response: [SCAN WARNING] ⚠️ Buka dari hasil scan QR Code ya!\n\n";
    } elseif (str_contains($body, 'success')) {
        echo "Response: " . substr($body, 0, 300) . "\n\n";
    } else {
        echo "Response Body Snippet: " . substr(strip_tags($body), 0, 150) . "\n\n";
    }
}
