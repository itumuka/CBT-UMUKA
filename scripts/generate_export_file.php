<?php
// Script to generate an XLSX export file using the same logic as SiswaController::export
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
// Boot DB / facades
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

$status = 'real';
$onlyAnomalies = false;

$rows = DB::table('m_siswa')->select('id','nama','nim','jurusan','sekolah','no_hp','email','mapel_wajib','mapel_pilihan','status_akun')
    ->when($status === 'real' || $status === 'test', function($q) use ($status){ return $q->where('status_akun', $status); })
    ->get();

$mapelLookup = DB::table('m_mapel')->pluck('nama','id')->toArray();

$anomalies = [];
$rowsMap = [];
foreach ($rows as $r) {
    $anom = [];
    if (filter_var($r->nama, FILTER_VALIDATE_EMAIL)) $anom[] = 'name_is_email';
    if (preg_match('/@|\d{3,}/', $r->nama)) $anom[] = 'name_suspicious';
    $school = strtoupper(trim((string)$r->sekolah));
    if ($school !== '') {
        $allowed = ['SMA','SMAN','SMK','SMKN','MAN','PONDOK','MA'];
        $found = false;
        foreach ($allowed as $k) { if (strpos($school, $k) !== false) { $found = true; break; } }
        if (!$found) $anom[] = 'school_anomaly';
    }
    $parse = function($raw) {
        $raw = trim((string)$raw);
        if ($raw === '') return [];
        $d = json_decode($raw, true);
        if (is_array($d)) return $d;
        return array_filter(preg_split('/[\s,]+/', $raw), function($v){ return $v !== ''; });
    };
    $wids = $parse($r->mapel_wajib);
    $pids = $parse($r->mapel_pilihan);

    $anomalies[$r->id] = $anom;
    $rowsMap[$r->id] = [
        'id' => $r->id,
        'nama' => $r->nama,
        'nim' => $r->nim,
        'jurusan' => $r->jurusan,
        'sekolah' => $r->sekolah,
        'no_hp' => $r->no_hp,
        'email' => $r->email,
        'mapel_wajib_nama' => implode(', ', array_map(function($id) use ($mapelLookup){ return $mapelLookup[$id] ?? ('Mapel #'.$id); }, $wids)),
        'mapel_pilihan_nama' => implode(', ', array_map(function($id) use ($mapelLookup){ return $mapelLookup[$id] ?? ('Mapel #'.$id); }, $pids)),
        'status_akun' => $r->status_akun
    ];
}

// Build spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$headersRow = ['ID','Nama','NIM','Jurusan','Sekolah','No HP','Email','Mapel Wajib','Mapel Pilihan','Status Akun','Anomalies'];
$col = 1;
foreach ($headersRow as $h) {
    $coord = Coordinate::stringFromColumnIndex($col) . '1';
    $sheet->setCellValue($coord, $h);
    $col++;
}

$rowNum = 2;
foreach ($rowsMap as $id => $r) {
    if ($onlyAnomalies && empty($anomalies[$id])) continue;
    $col = 1;
    $values = [
        $r['id'],$r['nama'],$r['nim'],$r['jurusan'],$r['sekolah'],$r['no_hp'],$r['email'],$r['mapel_wajib_nama'],$r['mapel_pilihan_nama'],$r['status_akun'],implode(';', $anomalies[$id])
    ];
    foreach ($values as $v) {
        $coord = Coordinate::stringFromColumnIndex($col) . $rowNum;
        $sheet->setCellValue($coord, $v);
        if (!empty($anomalies[$id])) {
            $an = $anomalies[$id];
            if (in_array('name_is_email', $an) || in_array('name_suspicious', $an)) {
                $c = Coordinate::stringFromColumnIndex(2) . $rowNum;
                $sheet->getStyle($c)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFC7CE');
            }
            if (in_array('school_anomaly', $an)) {
                $c = Coordinate::stringFromColumnIndex(5) . $rowNum;
                $sheet->getStyle($c)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFEB9C');
            }
            $cAn = Coordinate::stringFromColumnIndex(11) . $rowNum;
            $sheet->getStyle($cAn)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9D2E9');
        }
        $col++;
    }
    $rowNum++;
}
for ($i = 1; $i <= 11; $i++) {
    $colLetter = Coordinate::stringFromColumnIndex($i);
    $sheet->getColumnDimension($colLetter)->setAutoSize(true);
}

$dir = __DIR__ . '/../storage/exports';
if (!is_dir($dir)) mkdir($dir, 0777, true);
$filename = $dir . '/sample_export_' . date('Ymd_His') . '.xlsx';
$writer = new Xlsx($spreadsheet);
$writer->save($filename);

echo "Wrote file: $filename\n";
