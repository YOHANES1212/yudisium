<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$req = Illuminate\Http\Request::create('/', 'GET');
$res = $kernel->handle($req);

echo "GET / status code: " . $res->getStatusCode() . "\n";
echo "Redirect target: " . $res->headers->get('Location') . "\n";
