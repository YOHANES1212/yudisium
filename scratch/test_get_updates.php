<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$baseUrl = 'https://script.google.com/macros/s/AKfycbzcqahW0zz10p1VqClGORtZdZxO37toNF-VSG0Z4A3DYJ6IuZnqxtBJ1iKYV5ISZzEY/exec';

$tests = [
    'GET nim + status' => '?nim=20240801150&status=valid',
    'GET email + status' => '?email=Dilanfajar05@gmail.com&status=valid',
    'GET action=update & nim' => '?action=update&nim=20240801150&status=valid',
    'GET action=update & email' => '?action=update&email=Dilanfajar05@gmail.com&status=valid',
    'GET action=validasi & nim' => '?action=validasi&nim=20240801150',
    'GET action=validasi & email' => '?action=validasi&email=Dilanfajar05@gmail.com',
    'GET action=proses & nim' => '?action=proses&nim=20240801150',
    'GET action=prosesSatuPeserta & nim' => '?action=prosesSatuPeserta&nim=20240801150',
    'GET action=kirimEmailQR & email' => '?action=kirimEmailQR&email=Dilanfajar05@gmail.com',
];

foreach ($tests as $label => $qs) {
    $url = $baseUrl . $qs;
    echo "--- Testing {$label} ({$url}) ---\n";
    $res = Illuminate\Support\Facades\Http::timeout(15)->withoutVerifying()->get($url);
    echo "Status: " . $res->status() . "\n";
    echo "Body snippet: " . substr(strip_tags($res->body()), 0, 150) . "\n";

    // Check Sheet state for Admin 1 (Row 2) and Admin 4 (Row 5)
    $resGet = Illuminate\Support\Facades\Http::withoutVerifying()->get($baseUrl . '?action=peserta');
    $data = $resGet->json();
    $r0 = $data[0] ?? []; // Admin 1
    $r3 = $data[3] ?? []; // Admin 4
    echo "Admin 1: Status='{$r0['Status Pembayaran']}', ID='{$r0['ID Unik']}', Mail='{$r0['Status Email']}'\n";
    echo "Admin 4: Status='{$r3['Status Pembayaran']}', ID='{$r3['ID Unik']}', Mail='{$r3['Status Email']}'\n\n";

    if (!empty($r0['Status Pembayaran']) || !empty($r3['Status Pembayaran'])) {
        echo "🎉 SUCCESS FOUND! Parameter {$qs} updated Google Sheets!\n";
        break;
    }
}
