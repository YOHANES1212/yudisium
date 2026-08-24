<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = new App\Http\Controllers\AdminController();

$req = new Illuminate\Http\Request([
    'email'  => 'Dilanfajar06@gmail.com',
    'nama'   => 'Admin 1',
    'status' => 'valid',
]);

echo "Testing updatePembayaran controller action for Admin 1...\n";
$response = $controller->updatePembayaran($req);
echo "Redirect status: " . $response->getStatusCode() . "\n";
echo "Session success message: " . session('success') . "\n";
