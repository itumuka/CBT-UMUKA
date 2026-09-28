<?php

namespace App\Http\Controllers;

use App\Models\Mapel;
use App\Models\Siswa;
use App\Models\Soal;
use App\Models\GuruTes;
use App\Models\HasilUjian;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(\Illuminate\Http\Request $request)
    {
        $totalSiswa = Siswa::count();
        $totalMapel = Mapel::count();
        $totalSoal = Soal::count();
        $totalUjian = GuruTes::count();
        $totalHasil = HasilUjian::count();

        // Recent datasets for dashboard widgets
        $recentExams = GuruTes::orderBy('created_at', 'desc')->limit(6)->get();
    // m_siswa does not use timestamps; order by primary key to get recent entries
    $recentStudents = Siswa::orderBy('id', 'desc')->limit(6)->get();
        $recentResults = HasilUjian::orderBy('created_at', 'desc')->limit(6)->get();

        // Exams per mapel for chart — aggregate via left join
        // Use actual table name and column (m_mapel.nama) to avoid alias/column mismatches
    $examsPerMapel = Mapel::leftJoin('tr_guru_tes as g', 'm_mapel.id', '=', 'g.id_mapel')
            ->select('m_mapel.nama as mapel', DB::raw('count(g.id) as count'))
            ->groupBy('m_mapel.id', 'm_mapel.nama')
            ->get()
            ->map(function ($row) {
                return ['mapel' => $row->mapel ?? 'Mapel', 'count' => (int) $row->count];
            });

        // Popular mapel among participants filtered by status (default: real)
        // Acceptable values: 'real', 'test', 'all'
        $mapelStatus = $request->get('mapel_status', 'real');

        // m_siswa may store mapel ids in CSV or JSON format; normalize by extracting ids via SQL and PHP
        $rawQuery = DB::table('m_siswa')->select('mapel_wajib', 'mapel_pilihan');
        if ($mapelStatus !== 'all') {
            $rawQuery->where('status_akun', $mapelStatus);
        }
        $rawStudents = $rawQuery->get();

        $mapelCounts = []; // id => ['wajib' => int, 'pilihan' => int]

        foreach ($rawStudents as $row) {
            foreach (['mapel_wajib' => 'wajib', 'mapel_pilihan' => 'pilihan'] as $col => $type) {
                $raw = trim((string) ($row->{$col} ?? ''));
                if ($raw === '') continue;

                // try JSON
                $decoded = json_decode($raw, true);
                if (is_array($decoded)) {
                    $ids = $decoded;
                } else {
                    // split by comma or whitespace
                    $parts = preg_split('/[\s,]+/', $raw);
                    $ids = array_filter($parts, function($v) { return $v !== ''; });
                }

                foreach ($ids as $id) {
                    $id = trim((string)$id);
                    if ($id === '') continue;
                    if (!isset($mapelCounts[$id])) $mapelCounts[$id] = ['wajib' => 0, 'pilihan' => 0];
                    $mapelCounts[$id][$type]++;
                }
            }
        }

        // Build array with mapel name and totals
        $popularMapel = [];
        if (!empty($mapelCounts)) {
            $mapelIds = array_keys($mapelCounts);
            $mapelLookup = DB::table('m_mapel')->whereIn('id', $mapelIds)->pluck('nama', 'id')->toArray();

            foreach ($mapelCounts as $id => $counts) {
                $w = $counts['wajib'] ?? 0;
                $p = $counts['pilihan'] ?? 0;
                $total = $w + $p;
                $popularMapel[] = [
                    'id' => $id,
                    'nama' => $mapelLookup[$id] ?? ('Mapel #' . $id),
                    'wajib' => $w,
                    'pilihan' => $p,
                    'total' => $total,
                ];
            }

            usort($popularMapel, function($a, $b) {
                return $b['total'] <=> $a['total'];
            });
        }

        // Ringkasan per mode (CBT vs Tryout) + status sesi + soal bermasalah
        $totalSiswaCbt = Siswa::segment('cbt')->count();
        $totalSiswaTryout = Siswa::segment('tryout')->count();
        $totalUjianCbt = GuruTes::mode('cbt')->count();
        $totalUjianTryout = GuruTes::mode('tryout')->count();
        $sesiAktif = DB::table('cbt_sessions')->where('active', true)->first();
        $attemptsToday = DB::table('tr_ikut_ujian')->whereDate('created_at', \Carbon\Carbon::today())->count();
        try {
            $soalBermasalah = \App\Http\Controllers\SoalController::countBermasalah();
        } catch (\Throwable $e) {
            $soalBermasalah = 0;
        }

        return view('dashboard', compact(
            'totalSiswa', 'totalMapel', 'totalSoal', 'totalUjian', 'totalHasil',
            'recentExams', 'recentStudents', 'recentResults', 'examsPerMapel', 'popularMapel', 'mapelStatus',
            'totalSiswaCbt', 'totalSiswaTryout', 'totalUjianCbt', 'totalUjianTryout',
            'sesiAktif', 'soalBermasalah', 'attemptsToday'
        ));

    }

    public function dashboard_user()
    {
        // Deprecated: students are now redirected to the Ujian list
        return redirect()->route('daftar_ujian');
    }

    public function dashboard_user_tryout()
    {
        return view('dashboard_user_tryout'); 
    }
    
}
