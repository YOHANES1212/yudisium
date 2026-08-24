<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

Illuminate\Support\Facades\Cache::forget('sheetdb_peserta');

$controller = new App\Http\Controllers\AdminController();
$req = new Illuminate\Http\Request();
$res = $controller->peserta($req);
$pagination = $res->getData()['pagination'];

echo "Web App Live Participants Data:\n";
foreach ($pagination['data'] as $idx => $p) {
    if ($idx < 10) {
        $nama  = $p['Nama Lengkap'] ?? $p['nama'] ?? '-';
        $nim   = $p['NIM'] ?? '-';
        $email = $p['Email Address'] ?? $p['Email'] ?? '-';
        $pay   = $p['Status Pembayaran'] ?? '-';
        $kursi = $p['Nomor Kursi'] ?? '-';
        echo ($idx + 1) . ". Nama='{$nama}', NIM='{$nim}', Email='{$email}', Status='{$pay}', Kursi='{$kursi}'\n";
    }
}
