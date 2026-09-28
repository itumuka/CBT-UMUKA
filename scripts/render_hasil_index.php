<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$controller = new App\Http\Controllers\HasilUjianController();
$request = Illuminate\Http\Request::create('/hasil-ujian','GET',[]);
$resp = $controller->index($request);
echo get_class($resp) . "\n";
