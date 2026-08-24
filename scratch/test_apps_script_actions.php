<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$baseUrl = 'https://script.google.com/macros/s/AKfycbzcqahW0zz10p1VqClGORtZdZxO37toNF-VSG0Z4A3DYJ6IuZnqxtBJ1iKYV5ISZzEY/exec';

// Test various query parameters on GET and POST
$queries = [
    'action=prosesValidasiManual',
    'action=prosesSatuPeserta&email=Dilanfajar05@gmail.com',
    'action=prosesSatuPeserta&nim=20240801150',
    'action=kirimEmailQR&email=Dilanfajar05@gmail.com',
    'action=validasi&email=Dilanfajar05@gmail.com',
    'action=update&email=Dilanfajar05@gmail.com&status=valid',
];

echo "=== TESTING GET QUERY PARAMS ===\n";
foreach ($queries as $q) {
    $url = $baseUrl . '?' . $q;
    $res = Illuminate\Support\Facades\Http::timeout(25)->withoutVerifying()->get($url);
    echo "GET {$q} -> Status: {$res->status()}, Body: " . substr(strip_tags($res->body()), 0, 150) . "\n";

    // Check Sheet Row 5 (Admin 4) state
    $resSheet = Illuminate\Support\Facades\Http::withoutVerifying()->get($baseUrl . '?action=peserta');
    $row4 = $resSheet->json()[3] ?? []; // Row 5 is index 3
    echo "   Admin 4 State: Status='{$row4['Status Pembayaran']}', ID='{$row4['ID Unik']}', Mail='{$row4['Status Email']}'\n\n";
    
    if (!empty($row4['Status Pembayaran']) || !empty($row4['ID Unik'])) {
        echo "SUCCESS! Admin 4 updated using GET {$q}!\n";
        break;
    }
}
