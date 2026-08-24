<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$url = 'https://script.google.com/macros/s/AKfycbzcqahW0zz10p1VqClGORtZdZxO37toNF-VSG0Z4A3DYJ6IuZnqxtBJ1iKYV5ISZzEY/exec?action=peserta';
$res = Illuminate\Support\Facades\Http::withoutVerifying()->get($url);
$data = $res->json();

echo "Total Rows from Google Apps Script: " . count($data) . "\n\n";

foreach ($data as $i => $r) {
    $rowNum = $i + 2; // Row number in Google Sheet (header is row 1)
    $nama    = $r['Nama Lengkap'] ?? $r['Nama Lengkap '] ?? $r['nama'] ?? '';
    $nim     = $r['NIM'] ?? '';
    $email   = $r['Email Address'] ?? $r['Email Address '] ?? $r['Email'] ?? $r['Email '] ?? '';
    $pay     = $r['Status Pembayaran'] ?? $r['Status Pembayaran '] ?? '';
    $id      = $r['ID Unik'] ?? '';
    $mail    = $r['Status Email'] ?? '';
    $pemilik = $r['Nama Pemilik Rekening'] ?? '';

    // Print first 10 rows and any row with Admin or dilan
    if ($i < 10 || str_contains(strtolower($nama), 'admin') || str_contains(strtolower($pemilik), 'dilan') || str_contains(strtolower($email), 'dilan')) {
        echo "Sheet Row {$rowNum}: Nama='{$nama}', Pemilik='{$pemilik}', NIM='{$nim}', Email='{$email}', Status='{$pay}', ID='{$id}', Mail='{$mail}'\n";
    }
}
