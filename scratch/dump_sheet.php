<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$url = 'https://script.google.com/macros/s/AKfycbzcqahW0zz10p1VqClGORtZdZxO37toNF-VSG0Z4A3DYJ6IuZnqxtBJ1iKYV5ISZzEY/exec?action=peserta';
$res = Illuminate\Support\Facades\Http::withoutVerifying()->get($url);
$data = $res->json();

echo "Total rows in Google Sheet: " . count($data) . "\n\n";

foreach ($data as $i => $r) {
    $nama  = $r['Nama Lengkap'] ?? $r['Nama Lengkap '] ?? $r['nama'] ?? '-';
    $nim   = $r['NIM'] ?? '-';
    $email = $r['Email Address'] ?? $r['Email Address '] ?? $r['Email'] ?? $r['Email '] ?? '-';
    $pay   = $r['Status Pembayaran'] ?? $r['Status Pembayaran '] ?? '-';
    $id    = $r['ID Unik'] ?? '-';
    $mail  = $r['Status Email'] ?? '-';
    $pemilik = $r['Nama Pemilik Rekening'] ?? '-';

    if ($i < 15 || !empty($pay) || !empty($id)) {
        echo "Row " . ($i + 2) . ": Nama='{$nama}', Pemilik='{$pemilik}', NIM='{$nim}', Email='{$email}', Status='{$pay}', ID='{$id}', Mail='{$mail}'\n";
    }
}
