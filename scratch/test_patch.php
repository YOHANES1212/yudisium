<?php
require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = new App\Http\Controllers\AdminController();

// Clear cache to trigger fetchAll
Illuminate\Support\Facades\Cache::forget('sheetdb_peserta');

$req = new Illuminate\Http\Request();
$res = $controller->peserta($req);
$pagination = $res->getData()['pagination'];

echo "Total valid participants in system: " . $pagination['total'] . "\n";
foreach ($pagination['data'] as $idx => $p) {
    echo ($idx + 1) . ". NIM=" . ($p['NIM'] ?? '-') . " Nama=" . ($p['nama'] ?? $p['Nama Lengkap'] ?? '-') . "\n";
}
