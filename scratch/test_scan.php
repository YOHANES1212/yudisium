<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = new App\Http\Controllers\AdminController();

$req = Illuminate\Http\Request::create('/admin/absensi/scan', 'POST', [
    'nim' => 'https://script.google.com/macros/s/AKfycbyhDXOel6JVLB5GvVk4poOpo3WfO_Xwn0z08tv5KGymb/exec?id=YDS-3-4916'
]);

$res = $controller->scanQr($req);
echo "Status code: " . $res->getStatusCode() . "\n";
echo "Response content:\n" . $res->getContent() . "\n";
