<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$url = 'https://script.google.com/macros/s/AKfycbzcqahW0zz10p1VqClGORtZdZxO37toNF-VSG0Z4A3DYJ6IuZnqxtBJ1iKYV5ISZzEY/exec?action=peserta';
$res = Illuminate\Support\Facades\Http::withoutVerifying()->get($url);
$data = $res->json();

foreach ($data as $i => $r) {
    $nama  = $r['Nama Lengkap'] ?? $r['Nama Lengkap '] ?? '-';
    $nim   = $r['NIM'] ?? '-';
    $email = $r['Email Address'] ?? $r['Email Address '] ?? $r['Email'] ?? $r['Email '] ?? '-';
    $pay   = $r['Status Pembayaran'] ?? '-';
    $id    = $r['ID Unik'] ?? '-';
    $mail  = $r['Status Email'] ?? '-';

    echo ($i+1) . ". {$nama} | NIM: {$nim} | Email: {$email} | Status: {$pay} | ID: {$id} | Mail: {$mail}\n";
}
