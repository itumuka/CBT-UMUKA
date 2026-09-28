<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$uj = DB::table('tr_guru_tes')->where('nama_ujian','TESTER')->get();
echo "tr_guru_tes:\n";
print_r($uj->toArray());

$idTes = $uj->first()->id ?? null;
$ikut = [];
if ($idTes) {
    $ikut = DB::table('tr_ikut_ujian')->where('id_tes', $idTes)->orderBy('id','desc')->limit(5)->get();
}

echo "tr_ikut_ujian:\n";
print_r($ikut->toArray());
