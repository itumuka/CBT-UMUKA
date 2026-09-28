<?php

namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use App\Models\HasilUjian;
use App\Models\Guru;
use App\Models\Mapel;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Concerns\ResolvesMode;

class HasilUjianController extends Controller
{
    use ResolvesMode;

    /**
     * Menampilkan daftar hasil ujian.
     */
    public function index(Request $request)
    {
        $scoped = $this->scopedMode($request);
        // Build per-student aggregated ranking across all mapel/tests
        // total_benar = SUM(jml_benar) across all tests per student
        // total_nilai = we will use SUM(jml_benar) as the primary score (user requirement)

        $perPage = 20;

        $query = DB::table('tr_ikut_ujian')
            ->join('m_siswa', 'tr_ikut_ujian.id_user', '=', 'm_siswa.id');
        // Halaman mode di-scope ke ujian modenya; halaman lama menampilkan semua
        if ($scoped) {
            $query->join('tr_guru_tes', 'tr_ikut_ujian.id_tes', '=', 'tr_guru_tes.id')
                ->where('tr_guru_tes.mode', $scoped);
        }
        $query->select(
                'm_siswa.id as siswa_id',
                'm_siswa.nama',
                'm_siswa.sekolah',
                DB::raw('SUM(tr_ikut_ujian.jml_benar) as total_benar'),
                DB::raw('SUM(tr_ikut_ujian.nilai) as total_nilai'),
                DB::raw('COUNT(tr_ikut_ujian.id) as attempts')
            )
            ->groupBy('m_siswa.id','m_siswa.nama','m_siswa.sekolah')
            ->orderByDesc('total_benar')
            ->orderByDesc('total_nilai');

        // optional search by student name
        if ($request->filled('search')) {
            $query->where('m_siswa.nama', 'like', '%' . $request->get('search') . '%');
        }

        $rankings = $query->paginate($perPage)->appends($request->query());

        // Recompute totals based on per-question answers (more accurate than summing jml_benar)
    $siswaIds = collect($rankings->items())->pluck('siswa_id')->toArray();
        $computedTotals = [];
        if (!empty($siswaIds)) {
            $rows = DB::table('tr_ikut_ujian')->whereIn('id_user', $siswaIds)->whereNotNull('list_soal')->get(['id_user','list_soal','list_jawaban']);

            // collect soal ids
            $allSoalIds = [];
            $perStudentRows = [];
            foreach ($rows as $r) {
                $perStudentRows[$r->id_user][] = ['list_soal' => $r->list_soal, 'list_jawaban' => $r->list_jawaban];
                if ($r->list_soal) {
                    $ids = array_filter(array_map('trim', explode(',', $r->list_soal)));
                    foreach ($ids as $id) $allSoalIds[(int)$id] = (int)$id;
                }
            }

            if (!empty($allSoalIds)) {
                $soals = DB::table('m_soal')->whereIn('id', array_values($allSoalIds))->select('id','jawaban','id_mapel')->get()->keyBy('id');
                $mapelIds = $soals->pluck('id_mapel')->unique()->filter()->values()->toArray();
                $mapels = [];
                if (!empty($mapelIds)) {
                    $mapels = DB::table('m_mapel')->whereIn('id', $mapelIds)->pluck('nama','id')->toArray();
                }

                foreach ($perStudentRows as $sid => $rows2) {
                    $totalBenar = 0;
                    $totalSalah = 0;
                    foreach ($rows2 as $r) {
                        if (!$r['list_soal']) continue;
                        $soalIds = array_filter(array_map('trim', explode(',', $r['list_soal'])));
                        // parse answers mapping (formats like "607:A,608:B" or "607:A")
                        $answers = [];
                        if ($r['list_jawaban']) {
                            $parts = array_filter(array_map('trim', explode(',', $r['list_jawaban'])));
                            foreach ($parts as $p) {
                                if (strpos($p, ':') !== false) {
                                    list($sidSoal, $ans) = explode(':', $p, 2);
                                    $answers[trim($sidSoal)] = trim($ans);
                                } else {
                                    // fallback: if answers come without soal id, try to map by order
                                }
                            }
                        }

                        foreach ($soalIds as $index => $soalId) {
                            $soalId = (int)$soalId;
                            $given = isset($answers[(string)$soalId]) ? $answers[(string)$soalId] : null;
                            $correct = isset($soals[$soalId]) ? ($soals[$soalId]->jawaban ?? null) : null;
                            if ($given !== null && $correct !== null) {
                                if (trim(strtoupper($given)) === trim(strtoupper($correct))) $totalBenar++;
                                else $totalSalah++;
                            } else {
                                // if we can't determine, don't count it
                            }
                        }
                    }
                    $computedTotals[$sid] = ['total_benar_questions' => $totalBenar, 'total_salah_questions' => $totalSalah];
                }
            }
        }

        // For modal detail (per-student per-mapel breakdown), we'll prepare an endpoint to fetch details via AJAX.
        return view('hasil_ujian.index', array_merge(
            compact('rankings', 'computedTotals'),
            $this->modeMeta($scoped)
        ));
    }

    /**
     * Menampilkan form tambah hasil ujian.
     */
    // public function create()
    // {
    //     $gurus = Guru::all();
    //     $mapels = Mapel::all();
    //     return view('hasil_ujian.create', compact('gurus', 'mapels'));
    // }

    /**
     * Menyimpan data hasil ujian baru.
     */
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'id_guru' => 'required',
    //         'id_mapel' => 'required',
    //         'list_soal' => 'required',
    //         'list_jawaban' => 'required',
    //         'jml_benar' => 'required|integer',
    //         'nilai' => 'required|numeric',
    //         'nilai_bobot' => 'required|numeric',
    //         'tgl_mulai' => 'required|date',
    //         'tgl_selesai' => 'required|date',
    //         'status' => 'required|string'
    //     ]);

    //     HasilUjian::create($request->all());
    //     return redirect()->route('hasil_ujian.index')->with('success', 'Hasil ujian berhasil ditambahkan.');
    // }

    /**
     * Menampilkan detail hasil ujian.
     */
    public function show($id, Request $request)
    {
        // Show participants for a specific test (id = tr_guru_tes.id)
        // Order by nilai (score) desc to represent ranking
        $perPage = 15;
        $query = DB::table('tr_ikut_ujian')
                    ->join('m_siswa', 'tr_ikut_ujian.id_user', '=', 'm_siswa.id')
                    ->where('tr_ikut_ujian.id_tes', $id)
                    ->select('tr_ikut_ujian.*', 'm_siswa.nama')
                    ->orderBy('tr_ikut_ujian.nilai', 'desc')
                    ->orderBy('tr_ikut_ujian.jml_benar', 'desc');

        // optional search by name
        if ($request->has('search') && trim($request->get('search')) !== '') {
            $s = $request->get('search');
            $query->where('m_siswa.nama', 'like', "%$s%");
        }

        $hasilUjian = $query->paginate($perPage)->appends($request->query());

        // Also provide test meta (mapel name, guru) to the view
        $testMeta = DB::table('tr_guru_tes')->where('id', $id)->first();
        $mapelName = null;
        if ($testMeta) {
            $mapel = DB::table('m_mapel')->where('id', $testMeta->id_mapel)->first();
            $mapelName = $mapel->nama ?? null;
        }

        $testMode = $testMeta->mode ?? 'cbt';

        return view('hasil_ujian.detail', array_merge(
            compact('hasilUjian', 'mapelName', 'testMeta'),
            ['mode' => $testMode, 'rp' => '', 'modeLabel' => $testMode === 'cbt' ? 'CBT' : 'Tryout', 'modeBadge' => $testMode === 'cbt' ? 'primary' : 'success']
        ));
    }

    /**
     * Reset attempt peserta (hapus hasil + buka kunci) agar bisa mengulang.
     * Dipakai untuk akun demo/tester dan koreksi admin.
     */
    public function batalkan($id, $tesId)
    {
        $deleted = DB::table('tr_ikut_ujian')
            ->where('id_user', $id)
            ->where('id_tes', $tesId)
            ->delete();

        // Buka kunci partisipasi jika tidak tersisa attempt lain
        $remaining = DB::table('tr_ikut_ujian')->where('id_user', $id)->count();
        if ($remaining === 0) {
            DB::table('m_siswa')->where('id', $id)->update(['has_participated' => 0]);
        }

        return redirect()->back()->with('success', "Reset berhasil: {$deleted} attempt dihapus, akun dapat mengulang ujian.");
    }

    /**
     * Ranking aggregated per selected mapel (across tests of that mapel).
     * Shows average score per student for the given mapel.
     */
    public function mapelRanking($mapelId, Request $request)
    {
        $perPage = 20;

        // Find all test IDs for this mapel
        $testIds = DB::table('tr_guru_tes')->where('id_mapel', $mapelId)->pluck('id')->toArray();

        // Aggregate average score per student across those tests
        $aggQuery = DB::table('tr_ikut_ujian')
            ->join('m_siswa', 'tr_ikut_ujian.id_user', '=', 'm_siswa.id')
            ->whereIn('tr_ikut_ujian.id_tes', $testIds)
            ->select('m_siswa.id as siswa_id', 'm_siswa.nama', DB::raw('COUNT(tr_ikut_ujian.id) as tests_count'), DB::raw('AVG(tr_ikut_ujian.nilai) as avg_nilai'), DB::raw('SUM(tr_ikut_ujian.jml_benar) as total_benar'))
            ->groupBy('m_siswa.id','m_siswa.nama')
            ->orderByDesc('avg_nilai');

        // optional search
        if ($request->filled('search')) {
            $aggQuery->where('m_siswa.nama', 'like', '%' . $request->get('search') . '%');
        }

        $rankings = $aggQuery->paginate($perPage)->appends($request->query());

        $mapel = DB::table('m_mapel')->where('id', $mapelId)->first();

        return view('hasil_ujian.detail_mapel', compact('rankings','mapel'));
    }

    /**
     * Export overall rankings as CSV
     */
    public function export(Request $request)
    {
        $scoped = $this->scopedMode($request);
        $query = DB::table('tr_ikut_ujian')
            ->join('m_siswa', 'tr_ikut_ujian.id_user', '=', 'm_siswa.id');
        if ($scoped) {
            $query->join('tr_guru_tes', 'tr_ikut_ujian.id_tes', '=', 'tr_guru_tes.id')
                ->where('tr_guru_tes.mode', $scoped);
        }
        $query->select(
                'm_siswa.id as siswa_id',
                'm_siswa.nama',
                'm_siswa.sekolah',
                DB::raw('SUM(tr_ikut_ujian.jml_benar) as total_benar'),
                DB::raw('SUM(tr_ikut_ujian.nilai) as total_nilai'),
                DB::raw('COUNT(tr_ikut_ujian.id) as attempts')
            )
            ->groupBy('m_siswa.id','m_siswa.nama','m_siswa.sekolah')
            ->orderByDesc('total_benar')
            ->orderByDesc('total_nilai');

        if ($request->filled('search')) {
            $query->where('m_siswa.nama', 'like', '%' . $request->get('search') . '%');
        }

        $rows = $query->get();

        $fileName = 'ranking_overall_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function() use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Rank','Nama','Sekolah','Total Benar','Total Nilai','Attempts']);
            $rank = 1;
            foreach ($rows as $r) {
                fputcsv($out, [$rank, $r->nama, $r->sekolah, $r->total_benar, $r->total_nilai, $r->attempts]);
                $rank++;
            }
            fclose($out);
        };

        return response()->streamDownload($callback, $fileName, $headers);
    }

    /**
     * Export per-mapel rankings as CSV
     */
    public function exportMapel($mapelId, Request $request)
    {
        $testIds = DB::table('tr_guru_tes')->where('id_mapel', $mapelId)->pluck('id')->toArray();

        $aggQuery = DB::table('tr_ikut_ujian')
            ->join('m_siswa', 'tr_ikut_ujian.id_user', '=', 'm_siswa.id')
            ->whereIn('tr_ikut_ujian.id_tes', $testIds)
            ->select('m_siswa.id as siswa_id', 'm_siswa.nama', DB::raw('COUNT(tr_ikut_ujian.id) as tests_count'), DB::raw('AVG(tr_ikut_ujian.nilai) as avg_nilai'), DB::raw('SUM(tr_ikut_ujian.jml_benar) as total_benar'))
            ->groupBy('m_siswa.id','m_siswa.nama')
            ->orderByDesc('avg_nilai');

        if ($request->filled('search')) {
            $aggQuery->where('m_siswa.nama', 'like', '%' . $request->get('search') . '%');
        }

        $rows = $aggQuery->get();
        $mapel = DB::table('m_mapel')->where('id', $mapelId)->first();

        $fileName = 'ranking_mapel_' . ($mapel->nama ?? $mapelId) . '_' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function() use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Rank','Nama','Tests Count','Avg Nilai','Total Benar']);
            $rank = 1;
            foreach ($rows as $r) {
                fputcsv($out, [$rank, $r->nama, $r->tests_count, round($r->avg_nilai,2), $r->total_benar]);
                $rank++;
            }
            fclose($out);
        };

        return response()->streamDownload($callback, $fileName, $headers);
    }

    /**
     * Return per-student per-mapel breakdown: for a given siswa id, list mapel taken with total benar/salah and total nilai
     */
    public function studentMapelBreakdown($siswaId)
    {
        // We'll parse per-attempt answer lists to compute correct/wrong counts per mapel
        $attempts = DB::table('tr_ikut_ujian')->where('id_user', $siswaId)->whereNotNull('list_soal')->get(['id_tes','list_soal','list_jawaban']);

        $mapelStats = []; // mapel_id => ['mapel_nama'=>..., 'benar'=>int, 'salah'=>int, 'attempts'=>int]
        $allSoalIds = [];
        foreach ($attempts as $a) {
            // gather soal ids
            $soalIds = array_filter(array_map('trim', explode(',', $a->list_soal)));
            foreach ($soalIds as $sid) $allSoalIds[(int)$sid] = (int)$sid;
        }

        $soalAnswers = [];
        if (!empty($allSoalIds)) {
            $soalRows = DB::table('m_soal')->whereIn('id', array_values($allSoalIds))->select('id','jawaban','id_mapel')->get();
            foreach ($soalRows as $s) {
                $soalAnswers[$s->id] = ['jawaban' => $s->jawaban, 'id_mapel' => $s->id_mapel];
            }
        }

        foreach ($attempts as $a) {
            $soalIds = array_filter(array_map('trim', explode(',', $a->list_soal)));
            $answers = [];
            if ($a->list_jawaban) {
                $parts = array_filter(array_map('trim', explode(',', $a->list_jawaban)));
                foreach ($parts as $p) {
                    if (strpos($p, ':') !== false) {
                        list($soalId, $ans) = explode(':', $p, 2);
                        $answers[trim($soalId)] = trim($ans);
                    }
                }
            }

            foreach ($soalIds as $sid) {
                $sid = (int)$sid;
                $info = $soalAnswers[$sid] ?? null;
                if (!$info) continue; // unknown soal
                $mapelId = $info['id_mapel'];
                $correct = $info['jawaban'];
                $given = isset($answers[(string)$sid]) ? $answers[(string)$sid] : null;
                if (!isset($mapelStats[$mapelId])) {
                    $mapelNameRow = DB::table('m_mapel')->where('id', $mapelId)->first();
                    $mapelStats[$mapelId] = ['mapel_nama' => $mapelNameRow->nama ?? (string)$mapelId, 'benar' => 0, 'salah' => 0, 'attempts' => 0];
                }
                $mapelStats[$mapelId]['attempts']++;
                if ($given !== null && $correct !== null) {
                    if (trim(strtoupper($given)) === trim(strtoupper($correct))) $mapelStats[$mapelId]['benar']++;
                    else $mapelStats[$mapelId]['salah']++;
                }
            }
        }

        // Fallback: if no detailed attempts or some mapels missing, use original aggregation
        if (empty($mapelStats)) {
            $rows = DB::table('tr_ikut_ujian')
                ->join('tr_guru_tes', 'tr_ikut_ujian.id_tes', '=', 'tr_guru_tes.id')
                ->join('m_mapel', 'tr_guru_tes.id_mapel', '=', 'm_mapel.id')
                ->where('tr_ikut_ujian.id_user', $siswaId)
                ->select('m_mapel.id as mapel_id','m_mapel.nama as mapel_nama', DB::raw('SUM(tr_ikut_ujian.jml_benar) as total_benar'), DB::raw('COUNT(tr_ikut_ujian.id) as attempts'), DB::raw('SUM(tr_ikut_ujian.nilai) as total_nilai'))
                ->groupBy('m_mapel.id','m_mapel.nama')
                ->orderByDesc('total_benar')
                ->get();

            $out = [];
            foreach ($rows as $r) {
                $out[] = ['mapel_id' => $r->mapel_id, 'mapel_nama' => $r->mapel_nama, 'total_benar' => (int)$r->total_benar, 'total_salah' => null, 'attempts' => (int)$r->attempts, 'total_nilai' => $r->total_nilai];
            }
            return response()->json(['data' => $out]);
        }

        $out = [];
        foreach ($mapelStats as $mid => $s) {
            $out[] = ['mapel_id' => $mid, 'mapel_nama' => $s['mapel_nama'], 'total_benar' => (int)$s['benar'], 'total_salah' => (int)$s['salah'], 'attempts' => (int)$s['attempts']];
        }

        return response()->json(['data' => array_values($out)]);
    }

    /**
     * Menampilkan form edit hasil ujian.
     */
    // public function edit(HasilUjian $hasilUjian)
    // {
    //     $gurus = Guru::all();
    //     $mapels = Mapel::all();
    //     return view('hasil_ujian.edit', compact('hasilUjian', 'gurus', 'mapels'));
    // }

    /**
     * Mengupdate hasil ujian.
     */
    // public function update(Request $request, HasilUjian $hasilUjian)
    // {
    //     $request->validate([
    //         'id_guru' => 'required',
    //         'id_mapel' => 'required',
    //         'list_soal' => 'required',
    //         'list_jawaban' => 'required',
    //         'jml_benar' => 'required|integer',
    //         'nilai' => 'required|numeric',
    //         'nilai_bobot' => 'required|numeric',
    //         'tgl_mulai' => 'required|date',
    //         'tgl_selesai' => 'required|date',
    //         'status' => 'required|string'
    //     ]);

    //     $hasilUjian->update($request->all());
    //     return redirect()->route('hasil_ujian.index')->with('success', 'Hasil ujian berhasil diperbarui.');
    // }

    // /**
    //  * Menghapus hasil ujian.
    //  */
    // public function destroy(HasilUjian $hasilUjian)
    // {
    //     $hasilUjian->delete();
    //     return redirect()->route('hasil_ujian.index')->with('success', 'Hasil ujian berhasil dihapus.');
    // }
}

