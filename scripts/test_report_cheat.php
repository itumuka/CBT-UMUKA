<?php
// Skrip tes lokal untuk memanggil controller reportCheat sebanyak 3 kali
// Jalankan dari root projek: php scripts/test_report_cheat.php

require __DIR__ . '/../vendor/autoload.php';

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use App\Models\IkutUjian;
use Illuminate\Http\Request;
use App\Http\Controllers\UjianController;

// Bootstrapping minimal Laravel
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Create an IkutUjian test record
$k = IkutUjian::create([
    'id_tes' => 9999,
    'id_user' => 1,
    'list_soal' => '',
    'list_jawaban' => '',
    'jml_benar' => 0,
    'nilai' => 0,
    'nilai_bobot' => 0,
    'status_lulus' => '',
    'tgl_mulai' => now(),
    'tgl_selesai' => now(),
]);

// Set session key used by reportCheat via Laravel session helper
session()->put('ikut_ujian_id', $k->id);

$c = new UjianController();

for ($i = 1; $i <= 3; $i++) {
    $req = Request::create('/ujian/report-cheat', 'POST', []);
    $res = $c->reportCheat($req);
    echo "Report #$i: ";
    if ($res instanceof \Illuminate\Http\JsonResponse) {
        echo $res->getContent() . PHP_EOL;
    } else {
        echo json_encode($res) . PHP_EOL;
    }
    sleep(1);
}

$out = IkutUjian::find($k->id);
print_r($out->toArray());

// cleanup: delete test record
IkutUjian::where('id', $k->id)->delete();

echo "Done\n";
