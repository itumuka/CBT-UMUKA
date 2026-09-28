<?php

namespace App\Http\Controllers;

use App\Models\Admin;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Log;
// Optional: PhpSpreadsheet for styled Excel exports
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use App\Http\Controllers\Concerns\ResolvesMode;

class SiswaController extends Controller
{
    use ResolvesMode;


    public function index(Request $request)
    {
        $scoped = $this->scopedMode($request);
        // Halaman mode selalu di-scope ke segmennya (CBT = bernim, Tryout = session tryout).
        // Halaman lama tanpa prefix menampilkan semua (scoped = null).
        $query = Siswa::query()->segment($scoped);

        // Filter status akun jika ada
        $status = $request->get('status');
        if ($status === 'real' || $status === 'test') {
            $query->where('status_akun', $status);
        } elseif ($status === 'cbt') {
            // Filter siswa yang memiliki No Register (NIM)
            $query->whereNotNull('nim')->where('nim', '!=', '');
        }

        // Jika ada pencarian
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%$search%")
                  ->orWhere('nim', 'like', "%$search%")
                  ->orWhere('jurusan', 'like', "%$search%");
            });
        }

        // Sorting: support sort_by (nama|id) and sort_dir (asc|desc)
        $allowedSortBy = ['nama', 'id'];
        $sortBy = $request->get('sort_by', 'nama');
        $sortDir = strtolower($request->get('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        if (!in_array($sortBy, $allowedSortBy)) {
            $sortBy = 'nama';
        }

        if ($sortBy === 'nama') {
            $query->orderBy('nama', $sortDir);
        } else {
            // sortBy == 'id'
            // When listing all statuses (no status filter), keep 'real' and 'test' grouped
            if (!($status === 'real' || $status === 'test')) {
                // group by status_akun (real first, test second, others last), then by id
                $query->orderByRaw("CASE WHEN status_akun='real' THEN 0 WHEN status_akun='test' THEN 1 ELSE 2 END ASC");
            }
            $query->orderBy('id', $sortDir);
        }

        // Ambil data dengan pagination (10 data per halaman)
        $siswas = $query->paginate(10);

        // Prepare lookup array for mapel id => nama so the view can render names
        $mapelLookup = DB::table('m_mapel')->pluck('nama', 'id')->toArray();

        // Build anomalies summary across all matched students (not only paginated)
        $allQuery = Siswa::query()->segment($scoped);
        if ($status === 'real' || $status === 'test') {
            $allQuery->where('status_akun', $status);
        } elseif ($status === 'cbt') {
            $allQuery->whereNotNull('nim')->where('nim', '!=', '');
        }
        $allStudents = $allQuery->get(['id','nama','sekolah','mapel_wajib','mapel_pilihan']);

        $nameCounts = [];
        $nameEmails = [];
        $schoolAnomalies = [];

        foreach ($allStudents as $a) {
            $name = trim((string)$a->nama);
            if ($name === '') continue;
            $lower = strtolower($name);
            $nameCounts[$lower] = ($nameCounts[$lower] ?? 0) + 1;
            if (filter_var($name, FILTER_VALIDATE_EMAIL) || preg_match('/@/', $name)) {
                $nameEmails[] = $a;
            }

            $school = strtoupper(trim((string)$a->sekolah));
            if ($school !== '') {
                $allowed = ['SMA','SMAN','SMK','SMKN','MAN','PONDOK','MA'];
                $found = false;
                foreach ($allowed as $k) { if (strpos($school, $k) !== false) { $found = true; break; } }
                if (!$found) {
                    $schoolAnomalies[] = $a;
                }
            }
        }

        $duplicateNames = [];
        foreach ($nameCounts as $n => $c) {
            if ($c > 1) $duplicateNames[$n] = $c;
        }

        return view('siswa.index', array_merge(
            compact('siswas', 'mapelLookup', 'status', 'nameEmails', 'duplicateNames', 'schoolAnomalies'),
            $this->modeMeta($scoped)
        ));
    }
    
    /**
     * Export siswa data as CSV. Supports ?status=real|test|all and ?only_anomalies=1
     */
    public function export(Request $request)
    {
        $status = $request->get('status', 'all');
        $onlyAnomalies = $request->get('only_anomalies', false);

        $query = DB::table('m_siswa')->select('id','nama','nim','jurusan','sekolah','no_hp','email','mapel_wajib','mapel_pilihan','status_akun');
        // Export mengikuti segmen halaman mode (CBT = bernim/non-tryout, Tryout = session tryout)
        $exportMode = $this->scopedMode($request);
        if ($exportMode === 'cbt') {
            $query->where(function ($q) {
                $q->whereNotNull('nim')->where('nim', '!=', '')
                    ->orWhereNull('session_key')
                    ->orWhere('session_key', 'not like', 'tryout%');
            });
        } elseif ($exportMode === 'tryout') {
            $query->where('session_key', 'like', 'tryout%');
        }
        if ($status === 'real' || $status === 'test') {
            $query->where('status_akun', $status);
        } elseif ($status === 'cbt') {
            $query->whereNotNull('nim')->where('nim', '!=', '');
        }

        $rows = $query->get();

        // prepare mapel lookup
        $mapelLookup = DB::table('m_mapel')->pluck('nama','id')->toArray();

        // build anomalies per row
        $anomalies = [];
        foreach ($rows as $r) {
            $anom = [];
            // name contains email
            if (filter_var($r->nama, FILTER_VALIDATE_EMAIL)) {
                $anom[] = 'name_is_email';
            }
            // name contains numeric only or includes @
            if (preg_match('/@|\d{3,}/', $r->nama)) {
                $anom[] = 'name_suspicious';
            }
            // school anomaly: missing common keywords
            $school = strtoupper(trim((string)$r->sekolah));
            if ($school !== '') {
                $allowed = ['SMA','SMAN','SMK','SMKN','MAN','PONDOK','MA'];
                $found = false;
                foreach ($allowed as $k) { if (strpos($school, $k) !== false) { $found = true; break; } }
                if (!$found) $anom[] = 'school_anomaly';
            }

            // parse mapel ids
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

        // If PhpSpreadsheet is available, produce an XLSX with highlighted anomalous cells.
        if (class_exists('\PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Header row
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

                    // If this row has anomalies, color the relevant cells
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
                        if (in_array('mapel_mismatch', $an)) {
                            $c8 = Coordinate::stringFromColumnIndex(8) . $rowNum;
                            $c9 = Coordinate::stringFromColumnIndex(9) . $rowNum;
                            $sheet->getStyle($c8)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFBDD7EE');
                            $sheet->getStyle($c9)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFBDD7EE');
                        }
                        $cAn = Coordinate::stringFromColumnIndex(11) . $rowNum;
                        $sheet->getStyle($cAn)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9D2E9');
                    }

                    $col++;
                }

                $rowNum++;
            }

            // Auto size columns for readability
            for ($i = 1; $i <= 11; $i++) {
                $colLetter = Coordinate::stringFromColumnIndex($i);
                $sheet->getColumnDimension($colLetter)->setAutoSize(true);
            }

            $writer = new Xlsx($spreadsheet);
            $filename = 'siswa_export_'.date('Ymd_His').'.xlsx';

            // Stream the file to browser
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');
            $writer->save('php://output');
            exit;
        }

        // Fallback: Build CSV
        $filename = 'siswa_export_'.date('Ymd_His').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($rowsMap, $anomalies, $onlyAnomalies) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['id','nama','nim','jurusan','sekolah','no_hp','email','mapel_wajib','mapel_pilihan','status_akun','anomalies']);

            foreach ($rowsMap as $id => $r) {
                if ($onlyAnomalies && empty($anomalies[$id])) continue;
                fputcsv($out, [
                    $r['id'],$r['nama'],$r['nim'],$r['jurusan'],$r['sekolah'],$r['no_hp'],$r['email'],$r['mapel_wajib_nama'],$r['mapel_pilihan_nama'],$r['status_akun'],implode(';', $anomalies[$id])
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

public function create()
{
    return view('siswa.create', $this->modeMeta($this->scopedMode()));
}


public function store(Request $request)
{
    $mode = $this->currentMode($request);

    // Validasi Input (mode tryout: tanpa NIM, pakai email sebagai akun login)
    $rules = [
        'nama' => 'required|string|max:255',
        'jurusan' => 'required|string|max:100',
    ];
    if ($mode === 'cbt') {
        $rules['nim'] = 'required|unique:m_siswa,nim|max:20';
    } else {
        $rules['nim'] = 'nullable|string|max:20|unique:m_siswa,nim';
        $rules['email'] = 'required|email|max:255|unique:m_siswa,email|unique:m_admin,email';
    }
    $request->validate($rules);

    $activeKey = DB::table('cbt_sessions')->where('active', true)->value('key');

    // Simpan Data ke Database
    $siswa = Siswa::create([
        'nama' => $request->nama,
        'nim' => $request->nim,
        'jurusan' => $request->jurusan,
        'email' => $mode === 'tryout' ? strtolower($request->email) : null,
        'session_key' => $mode === 'tryout' ? ($activeKey ?? 'tryout') : null,
    ]);

    $idSiswa = $siswa->id;

    $login = $mode === 'cbt' ? $request->nim : strtolower($request->email);
    Admin::create([
        'username' => $login,
        'email' => $mode === 'tryout' ? strtolower($request->email) : null,
        'password' => Hash::make($login),
        'level' => 'siswa',
        'kon_id' => $idSiswa
    ]);

    // Redirect ke halaman daftar siswa dengan pesan sukses
    return $this->modeRedirect($mode, 'siswa.index')->with('success', 'Siswa berhasil ditambahkan!');
}



    public function edit($id)
    {
        $siswa = Siswa::find($id);
        if (!$siswa) {
            abort(404); // Jika data tidak ditemukan, tampilkan halaman 404
        }

        return view('siswa.edit', array_merge(compact('siswa'), $this->modeMeta($this->scopedMode())));
    }

    public function update(Request $request, $id)
    {
        $mode = $this->currentMode($request);
        $request->validate([
            'nama' => 'required|string|max:255',
            'nim' => 'nullable|string|max:20|unique:m_siswa,nim,' . $id,
            'jurusan' => 'required|string|max:255',
        ]);

        $siswa = Siswa::findOrFail($id);
        $siswa->update([
            'nama' => $request->nama,
            'nim' => $request->nim,
            'jurusan' => $request->jurusan,
        ]);

        if ($request->filled('nim')) {
            DB::table('m_admin')->where('kon_id', $id)->update([
                'username' => $request->nim,
                'password' => Hash::make($request->nim),
            ]);
        }

        return $this->modeRedirect($mode, 'siswa.index')->with('success', 'Data siswa berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $mode = $this->currentMode();
        $siswa = Siswa::find($id);

        if (!$siswa) {
            return $this->modeRedirect($mode, 'siswa.index')->with('error', 'Data tidak ditemukan.');
        }

        $siswa->delete();
        return $this->modeRedirect($mode, 'siswa.index')->with('success', 'Data berhasil dihapus.');
    }

    /**
     * Reset password for a siswa admin account and return the new plain password once.
     */
    public function resetPassword(Request $request, $id)
    {
        $siswa = Siswa::find($id);
        if (! $siswa) {
            return response()->json(['error' => 'Siswa tidak ditemukan'], 404);
        }

        $admin = DB::table('m_admin')->where('kon_id', $id)->where('level', 'siswa')->first();
        if (! $admin) {
            return response()->json(['error' => 'Akun admin tidak ditemukan'], 404);
        }

        // If admin provided a custom password in request, use it (validate length >= 6)
        $custom = $request->input('password');
        if ($custom !== null && $custom !== '') {
            $custom = trim((string)$custom);
            if (strlen($custom) < 6) {
                return response()->json(['error' => 'Password harus minimal 6 karakter'], 422);
            }
            $plain = $custom;
            $hashed = Hash::make($plain);
        } else {
            // Generate random password (8 characters) - letters + numbers
            $plain = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789'), 0, 8);
            $hashed = Hash::make($plain);
        }

        try {
            DB::table('m_admin')->where('kon_id', $id)->where('level', 'siswa')->update(['password' => $hashed]);
            return response()->json(['success' => true, 'password' => $plain]);
        } catch (\Exception $ex) {
            Log::error('Reset password failed for siswa id '.$id.': '.$ex->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan pada server saat mereset password.'], 500);
        }
    }

    // Bulk delete selected siswa
    public function bulkDestroy(Request $request)
    {
        $mode = $this->currentMode($request);
        $ids = $request->input('ids', []);

        if (empty($ids) || !is_array($ids)) {
            return $this->modeRedirect($mode, 'siswa.index')->with('error', 'Tidak ada data yang dipilih.');
        }

        // Hapus siswa dan admin terkait
        foreach ($ids as $id) {
            $s = Siswa::find($id);
            if ($s) {
                $s->delete();
                DB::table('m_admin')->where('kon_id', $id)->where('level','siswa')->delete();
            }
        }

        return $this->modeRedirect($mode, 'siswa.index')->with('success', 'Data terpilih berhasil dihapus.');
    }

    /**
     * Tampilkan halaman profil untuk siswa yang sedang login
     */
    public function profile()
    {
        $konId = session('kon_id');
        $level = session('level');

        if (!$konId || $level !== 'siswa') {
            // jika bukan siswa atau tidak login sebagai siswa
            return redirect('/')->with('error', 'Akses ditolak.');
        }

        $siswa = Siswa::findOrFail($konId);

        // Ambil lookup mapel (id => nama)
        $mapelLookup = DB::table('m_mapel')->pluck('nama', 'id')->toArray();

        // Helper untuk parse field mapel yang bisa berupa JSON atau CSV
        $parseIds = function ($raw) {
            $raw = trim((string) $raw);
            if ($raw === '') {
                return [];
            }

            // Coba decode JSON terlebih dahulu
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return array_map('strval', $decoded);
            }

            // Jika bukan JSON, anggap comma/space separated
            $parts = preg_split('/[\s,]+/', $raw);
            $out = [];
            foreach ($parts as $p) {
                $p = trim($p);
                if ($p !== '') {
                    $out[] = (string) $p;
                }
            }
            return $out;
        };

        $mapIdsWajib = $parseIds($siswa->mapel_wajib ?? '');
        $mapIdsPilihan = $parseIds($siswa->mapel_pilihan ?? '');

        $mapNames = function ($ids) use ($mapelLookup) {
            $names = [];
            foreach ($ids as $id) {
                // beberapa DB menyimpan id sebagai angka, jadi coba cast ke int juga
                if (isset($mapelLookup[$id])) {
                    $names[] = $mapelLookup[$id];
                } elseif (isset($mapelLookup[(int) $id])) {
                    $names[] = $mapelLookup[(int) $id];
                } else {
                    // fallback tampilkan id jika tidak ditemukan
                    $names[] = 'Mapel #' . $id;
                }
            }
            return implode(', ', $names);
        };

        $mapelWajibNames = $mapNames($mapIdsWajib);
        $mapelPilihanNames = $mapNames($mapIdsPilihan);

        // Determine whether token should appear as active for this student.
        // Rules:
        // - If there is no active session, token is not active.
        // - If active session has `token_status` use it as authoritative:
        //     0 = off, 1 = real (peserta), 2 = tester
        //   A student's `status_akun` should match required account type for token to be visible.
        // - Legacy fallback: if `token_status` not present, fall back to `token_active` boolean.
        $activeSession = DB::table('cbt_sessions')->where('active', true)->first();
        $tokenActive = false;
        if ($activeSession) {
            if (isset($activeSession->token_status)) {
                $ts = (int) $activeSession->token_status;
                if ($ts === 0) {
                    $tokenActive = false;
                } elseif ($ts === 1) {
                    // real participants allowed: require siswa.status_akun == 'real' (if present)
                    if (isset($siswa->status_akun)) {
                        $tokenActive = ($siswa->status_akun === 'real');
                    } else {
                        // when student record has no status_akun, assume real
                        $tokenActive = true;
                    }
                } elseif ($ts === 2) {
                    // tester allowed: require siswa.status_akun == 'test' (if present)
                    if (isset($siswa->status_akun)) {
                        $tokenActive = ($siswa->status_akun === 'test');
                    } else {
                        // if missing, be conservative and hide token
                        $tokenActive = false;
                    }
                } else {
                    // unknown code: fallback to off
                    $tokenActive = false;
                }
            } elseif (isset($activeSession->token_active)) {
                // Legacy boolean column: treat as simple on/off
                $tokenActive = (bool) $activeSession->token_active;
            }
        }

        // Provide both variable names used in views: legacy `$tokenActive` and `$tokenEnabled`.
        $tokenEnabled = $tokenActive;

        return view('siswa.profile', compact('siswa', 'mapelWajibNames', 'mapelPilihanNames', 'tokenActive', 'tokenEnabled'));
    }

    /**
     * Verifikasi password singkat untuk menampilkan token penuh
     */
    public function revealToken(Request $request)
    {
        $request->validate([
            'password' => 'required|string'
        ]);

        $konId = session('kon_id');
        $level = session('level');

        if (!$konId || $level !== 'siswa') {
            return response()->json(['error' => 'Akses ditolak'], 403);
        }

        // Ambil admin record untuk siswa ini
        $admin = DB::table('m_admin')->where('level', 'siswa')->where('kon_id', $konId)->first();
        if (!$admin) {
            return response()->json(['error' => 'Akun admin tidak ditemukan'], 404);
        }

        if (!Hash::check($request->password, $admin->password)) {
            return response()->json(['error' => 'Password salah'], 422);
        }

        // Get siswa record
        $siswa = DB::table('m_siswa')->where('id', $konId)->first();
        if (!$siswa || !$siswa->token) {
            return response()->json(['error' => 'Token tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'token' => $siswa->token
        ]);

        $siswa = Siswa::find($konId);
        if (!$siswa) {
            return response()->json(['error' => 'Data siswa tidak ditemukan'], 404);
        }

        return response()->json(['token' => $siswa->token]);
    }

    /**
     * Set Mapel Pilihan secara acak (1 IPA & 1 IPS) untuk siswa CBT yang belum memiliki mapel.
     */
    public function setMapelCbt()
    {
        // Ambil ID mapel IPA dan IPS di rentang 100-108
        $ipaIds = DB::table('m_mapel')
            ->whereBetween('id', [100, 108])
            ->where('nama', 'like', '%IPA%')
            ->pluck('id')
            ->toArray();

        $ipsIds = DB::table('m_mapel')
            ->whereBetween('id', [100, 108])
            ->where('nama', 'like', '%IPS%')
            ->pluck('id')
            ->toArray();

        if (empty($ipaIds) || empty($ipsIds)) {
            return redirect()->back()->with('error', 'Gagal: Master Mapel IPA atau IPS dalam rentang ID 100-108 tidak ditemukan.');
        }

        // Cari siswa CBT yang mapel pilihannya masih kosong
        $siswas = Siswa::whereNotNull('nim')
            ->where('nim', '!=', '')
            ->where(function($q) {
                $q->whereNull('mapel_pilihan')->orWhere('mapel_pilihan', '');
            })->get();

        foreach ($siswas as $siswa) {
            // Pilih 1 acak dari IPA dan 1 acak dari IPS
            $randomPilihan = $ipaIds[array_rand($ipaIds)] . ',' . $ipsIds[array_rand($ipsIds)];
            $siswa->update(['mapel_pilihan' => $randomPilihan]);
        }

        return redirect()->back()->with('success', count($siswas) . ' Siswa CBT berhasil di-set mapel pilihannya secara acak (1 IPA, 1 IPS).');
    }
}
