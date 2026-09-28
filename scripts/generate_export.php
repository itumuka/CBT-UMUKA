<?php
// Quick script to invoke SiswaController::export and save output to storage/exports/sample.xlsx
use Illuminate\Http\Request;
require __DIR__ . '/../vendor/autoload.php';

// Boot Laravel
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

// Build a request similar to hitting /siswa/export?status=real&only_anomalies=1
// but we will call controller directly to capture output buffer
$request = Request::create('/siswa/export', 'GET', ['status' => 'real', 'only_anomalies' => 0]);

// Run the application to allow facades/providers to register
$response = $kernel->handle($request);

// If response is a StreamedResponse it may have sent headers and output already.
// We'll instead call controller directly as simpler approach.

$controller = new App\Http\Controllers\SiswaController();

// capture output
ob_start();
$result = $controller->export($request);
$contents = ob_get_clean();

$dir = __DIR__ . '/../storage/exports';
if (!is_dir($dir)) mkdir($dir, 0777, true);
$file = $dir . '/sample_export_' . date('Ymd_His') . '.xlsx';
file_put_contents($file, $contents);

echo "Wrote export to: $file\n"; 
