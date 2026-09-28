<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$mapelId = $argv[1] ?? 18;
echo "Checking mapelId: $mapelId\n";
$testIds = DB::table('tr_guru_tes')->where('id_mapel', $mapelId)->pluck('id')->toArray();
if (empty($testIds)) {
    echo "No tests found for mapelId $mapelId\n";
    exit(0);
}
echo "Found tests: " . implode(',', $testIds) . "\n";
$rows = DB::table('tr_ikut_ujian')
    ->join('m_siswa','tr_ikut_ujian.id_user','=','m_siswa.id')
    ->whereIn('tr_ikut_ujian.id_tes', $testIds)
    ->select('m_siswa.id as siswa_id','m_siswa.nama', DB::raw('COUNT(tr_ikut_ujian.id) as tests_count'), DB::raw('AVG(tr_ikut_ujian.nilai) as avg_nilai'), DB::raw('SUM(tr_ikut_ujian.jml_benar) as total_benar'))
    ->groupBy('m_siswa.id','m_siswa.nama')
    ->orderByDesc('avg_nilai')
    ->limit(10)
    ->get();

if ($rows->isEmpty()) {
    echo "No ranking rows\n";
    exit(0);
}

foreach ($rows as $r) {
    echo "{$r->siswa_id} | {$r->nama} | avg: " . number_format($r->avg_nilai,2) . " | tests: {$r->tests_count} | total_benar: {$r->total_benar}\n";
}
