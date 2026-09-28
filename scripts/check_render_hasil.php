<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$controller = new App\Http\Controllers\HasilUjianController();
$request = Illuminate\Http\Request::create('/hasil-ujian','GET',[]);
$view = $controller->index($request);
$html = (string)$view->render();
if (strpos($html, 'btn-mapel-detail') !== false) echo "OK: button present\n";
if (strpos($html, "fetch('/hasil-ujian/student/") !== false) echo "OK: ajax present\n";
file_put_contents(__DIR__ . '/out.html', $html);
echo "Wrote out.html\n";
