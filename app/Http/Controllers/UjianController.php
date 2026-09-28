<?php

namespace App\Http\Controllers;

use App\Models\GuruTes;
use App\Models\IkutUjian;
use App\Models\Mapel;
use App\Models\Siswa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use App\Http\Controllers\Concerns\ResolvesMode;

class UjianController extends Controller
{
    use ResolvesMode;

    public function daftar_ujian()
    {
        // Try to resolve the current siswa from session (kon_id) so the view can display the student's name
        $siswa = null;
        $konId = session('kon_id');
        if ($konId) {
            $siswa = Siswa::find($konId);
        }

        // Segmen halaman mengikuti data siswa: bernim = CBT, session tryout = Tryout
        $segment = $siswa ? $siswa->segment : 'cbt';
        if (!in_array($segment, ['cbt', 'tryout'], true)) {
            $segment = 'cbt';
        }

        return view('ujian.ujian', array_merge(
            compact('siswa'),
            ['mode' => $segment, 'rp' => '', 'modeLabel' => $segment === 'cbt' ? 'CBT' : 'Tryout', 'modeBadge' => $segment === 'cbt' ? 'primary' : 'success']
        ));
    }

    public function konfirmasi_ujian(Request $request)
    {
        $tokenInput = strtoupper(trim($request->get('token')));

        // Check CBT session status: there must be an active session and token must be enabled
        $activeSession = \App\Models\CbtSession::where('active', true)->first();
        if (! $activeSession) {
            return redirect()->route('daftar_ujian')
                ->withErrors(['token' => 'Belum ada sesi CBT yang aktif saat ini.']);
        }

        // Determine token status: prefer token_status (int) then fallback to token_active (bool)
        // token_status: 0 = off, 1 = real (only real accounts), 2 = tester (only tester accounts)
        $tokenStatus = null;
        if ($activeSession) {
            if (isset($activeSession->token_status)) {
                $tokenStatus = (int) $activeSession->token_status;
            } elseif (isset($activeSession->token_active)) {
                // legacy boolean: treat true as 'real'
                $tokenStatus = ((bool) $activeSession->token_active) ? 1 : 0;
            } else {
                $tokenStatus = 0;
            }
        }

        if ($tokenStatus === 0) {
            return redirect()->route('daftar_ujian')
                ->withErrors(['token' => 'Token akun saat ini dinonaktifkan oleh administrator.']);
        }

        // If tokenStatus restricts to a specific account type, remember it for later checks
        $requiredAccountStatus = null; // null = any, otherwise 'real' or 'test'
        if ($tokenStatus === 1) {
            $requiredAccountStatus = 'real';
        } elseif ($tokenStatus === 2) {
            $requiredAccountStatus = 'test';
        }

        // First, check if token belongs to the logged-in siswa (student-token flow)
        $siswa = null;
        $konId = session('kon_id');
        if ($konId) {
            $siswa = Siswa::find($konId);
        }

        if ($siswa && $siswa->token && strtoupper($siswa->token) === $tokenInput) {
            // If the CBT session requires a specific account status (real/test), enforce it here
            if ($requiredAccountStatus && isset($siswa->status_akun)) {
                // normalize
                $acct = strtolower(trim($siswa->status_akun));
                if ($acct !== $requiredAccountStatus) {
                    return redirect()->route('daftar_ujian')
                        ->withErrors(['token' => 'Akun Anda tidak diizinkan pada sesi token ini.']);
                }
            }
            // Build allowed mapel IDs from student's registration
            $mapelWajib = $siswa->mapel_wajib ?? '';
            $mapelPilihan = $siswa->mapel_pilihan ?? '';

            $parseIds = function ($csv) {
                if (!$csv) return [];
                return array_filter(array_map('intval', array_map('trim', explode(',', $csv))));
            };

            $mapelIds = array_unique(array_merge($parseIds($mapelWajib), $parseIds($mapelPilihan)));

            if (empty($mapelIds)) {
                return redirect()->route('daftar_ujian')
                    ->withErrors(['token' => 'Akun Anda belum dipetakan ke mata pelajaran ujian. Hubungi admin untuk pengaturan mapel.']);
            }

            // Find active ujian (by time window) using Jakarta timezone.
            // Prefer ujian whose mode matches the student's segment (cbt/tryout),
            // fallback to any active ujian (legacy behavior).
            $now = Carbon::now('Asia/Jakarta');
            $nowStr = $now->format('Y-m-d H:i:s');
            $segment = $siswa->segment === 'tryout' ? 'tryout' : 'cbt';

            // Pilihan eksplisit dari halaman pilih ujian (bila >1 kandidat)
            $ujian = null;
            if ($request->filled('ujian_id')) {
                $ujian = GuruTes::where('id', (int) $request->input('ujian_id'))
                    ->where('tgl_mulai', '<=', $nowStr)
                    ->where('terlambat', '>=', $nowStr)
                    ->first();
                if (!$ujian) {
                    return redirect()->route('daftar_ujian')
                        ->withErrors(['token' => 'Ujian yang dipilih sudah tidak aktif.']);
                }
            }

            if (!$ujian) {
                $candidates = GuruTes::where('mode', $segment)
                    ->where('tgl_mulai', '<=', $nowStr)
                    ->where('terlambat', '>=', $nowStr)
                    ->orderBy('tgl_mulai', 'asc')
                    ->get();
                if ($candidates->isEmpty()) {
                    $candidates = GuruTes::where('tgl_mulai', '<=', $nowStr)
                        ->where('terlambat', '>=', $nowStr)
                        ->orderBy('tgl_mulai', 'asc')
                        ->get();
                }

                if ($candidates->isEmpty()) {
                    return redirect()->route('daftar_ujian')
                        ->withErrors(['token' => 'Belum ada ujian aktif saat ini.']);
                }

                // Lebih dari 1 kandidat: siswa memilih sendiri (anti salah ujian)
                if ($candidates->count() > 1) {
                    $token = $request->get('token');

                    return view('ujian.pilih_ujian', compact('siswa', 'candidates', 'token'));
                }

                $ujian = $candidates->first();
            }

            Session::put('allowed_mapels', $mapelIds);
            Session::put('id_ujian', $ujian->id);

            $mapel = Mapel::whereIn('id', $mapelIds)->first();
            $mapels = Mapel::whereIn('id', $mapelIds)->get();

            $mulai = Carbon::parse($ujian->tgl_mulai, 'Asia/Jakarta');
            $terlambat = Carbon::parse($ujian->terlambat, 'Asia/Jakarta');

            if ($now->between($mulai, $terlambat)) {
                $status = 'Sudah dimulai';
            } elseif ($now->greaterThan($terlambat)) {
                $status = 'Waktu Habis';
            } else {
                $status = 'Belum Dimulai';
            }

            $token = $request->get('token');

            // Check for previous attempts by this student for this ujian
            $alreadyParticipated = false;
            $ongoingAttempt = false;
            $lastAttemptTime = null;
            if ($siswa && $ujian) {
                $lastAttempt = IkutUjian::where('id_tes', $ujian->id)
                    ->where('id_user', $siswa->id)
                    ->orderBy('id', 'desc')
                    ->first();
                if ($lastAttempt) {
                    if (empty($lastAttempt->tgl_selesai) || $lastAttempt->tgl_selesai == '') {
                        // ongoing attempt
                        $ongoingAttempt = true;
                    } else {
                        // completed attempt
                        $alreadyParticipated = true;
                        $lastAttemptTime = $lastAttempt->tgl_selesai;
                    }
                }
            }

            return view('ujian.konfirmasi_ujian', compact('siswa', 'mapel', 'mapels', 'ujian', 'status', 'mulai', 'terlambat', 'token', 'alreadyParticipated', 'ongoingAttempt', 'lastAttemptTime'));
        }

    // Fallback: previous behavior (token belongs to a GuruTes)
    $ujian = GuruTes::where('token', $tokenInput)->first();

        // Jika token tidak cocok atau tidak ditemukan
        if (!$ujian) {
            return redirect()->route('daftar_ujian')
                ->withErrors(['token' => 'Token tidak ditemukan atau tidak valid.']);
        }

        // Baru setelah valid, simpan ke session
        // Instead of binding to a single mapel, store all mapel IDs the student selected during registration
        $siswa = Siswa::findOrFail(session('kon_id'));

        // Enforce session account requirement when a GuruTes token is used
        if ($requiredAccountStatus && isset($siswa->status_akun)) {
            $acct = strtolower(trim($siswa->status_akun));
            if ($acct !== $requiredAccountStatus) {
                return redirect()->route('daftar_ujian')
                    ->withErrors(['token' => 'Akun Anda tidak diizinkan pada sesi token ini.']);
            }
        }

        $mapelWajib = $siswa->mapel_wajib ?? '';
        $mapelPilihan = $siswa->mapel_pilihan ?? '';

        $parseIds = function ($csv) {
            if (!$csv) return [];
            return array_filter(array_map('intval', array_map('trim', explode(',', $csv))));
        };

        $mapelIds = array_unique(array_merge($parseIds($mapelWajib), $parseIds($mapelPilihan)));

        // Fallback: if student has no registered mapel, use ujian's mapel
        if (empty($mapelIds)) {
            $mapelIds = [$ujian->id_mapel];
        }

        Session::put('allowed_mapels', $mapelIds);
        Session::put('id_ujian', $ujian->id);

        // Provide a representative mapel (first) for backward compatibility in views
        $mapel = Mapel::whereIn('id', $mapelIds)->first();

    $now = Carbon::now('Asia/Jakarta');
    $mulai = Carbon::parse($ujian->tgl_mulai, 'Asia/Jakarta');
    $terlambat = Carbon::parse($ujian->terlambat, 'Asia/Jakarta');

        if ($now->between($mulai, $terlambat)) {
            $status = 'Sudah dimulai';
        } elseif ($now->greaterThan($terlambat)) {
            $status = 'Waktu Habis';
        } else {
            $status = 'Belum Dimulai';
        }

        $token = $request->get('token');
        // Also pass list of mapels for display
        $mapels = Mapel::whereIn('id', $mapelIds)->get();

        // Check previous attempts for this student and ujian
        $alreadyParticipated = false;
        $ongoingAttempt = false;
        $lastAttemptTime = null;
        if ($siswa && $ujian) {
            $lastAttempt = IkutUjian::where('id_tes', $ujian->id)
                ->where('id_user', $siswa->id)
                ->orderBy('id', 'desc')
                ->first();
            if ($lastAttempt) {
                if (empty($lastAttempt->tgl_selesai) || $lastAttempt->tgl_selesai == '') {
                    $ongoingAttempt = true;
                } else {
                    $alreadyParticipated = true;
                    $lastAttemptTime = $lastAttempt->tgl_selesai;
                }
            }
        }

        return view('ujian.konfirmasi_ujian', compact('siswa', 'mapel', 'mapels', 'ujian', 'status', 'mulai', 'terlambat', 'token', 'alreadyParticipated', 'ongoingAttempt', 'lastAttemptTime'));
    }


    public function generateToken($id)
    {
        $token = strtoupper(Str::random(5));
        DB::table('tr_guru_tes')->where('id', $id)->update(['token' => $token]);

        return $this->modeRedirect($this->currentMode(), 'ujian.index')->with('success', 'Token berhasil diperbarui.');
    }

    public function mulai_ujian()
    {
        // Use allowed_mapels from session (set during token confirmation)
        $allowed = session('allowed_mapels', []);
        $idUjian = session('id_ujian');

        $ujian = $idUjian ? GuruTes::find($idUjian) : null;

        if (empty($allowed) && $ujian) {
            $allowed = [$ujian->id_mapel];
        }

        $soalsQuery = DB::table('m_soal');
        if (!empty($allowed)) {
            $soalsQuery->whereIn('id_mapel', $allowed);
        }

        // get full ordered list of soal IDs for client-side completeness checks
        try {
            $allSoalIds = (clone $soalsQuery)->pluck('id')->toArray();
        } catch (\Exception $e) {
            // fallback: empty array
            $allSoalIds = [];
        }

        $soals = $soalsQuery->paginate(10)->appends(request()->query());

        $waktu_mulai = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s');

        // Get mapel objects for the allowed ids to show in the view
        $selectedMapels = [];
        if (!empty($allowed)) {
            $selectedMapels = Mapel::whereIn('id', $allowed)->get();
        }

        // Ensure there is an IkutUjian record to track this attempt
        $kon_id = session('kon_id');
        $ikutUjian = null;
        if ($kon_id && $idUjian) {
            // Try to get the latest attempt for this user and this test
            $last = \App\Models\IkutUjian::where('id_tes', $idUjian)
                ->where('id_user', $kon_id)
                ->orderBy('id', 'desc')
                ->first();

            $needCreate = true;
            if ($last) {
                // Determine if last attempt is still active: if tgl_selesai is null or in future
                try {
                    $start = Carbon::parse($last->tgl_mulai);
                    $end = Carbon::parse($last->tgl_selesai);
                } catch (\Exception $e) {
                    $start = null;
                    $end = null;
                }

                $totalSeconds = ($ujian->waktu ?? 0) * 60;
                $expired = false;
                if ($start) {
                    $elapsed = Carbon::now()->diffInSeconds($start);
                    if ($elapsed >= $totalSeconds) {
                        $expired = true; // previous attempt already expired
                    }
                }

                // If last has no tgl_selesai (still ongoing) and not expired, reuse it
                if (!$expired && ($last->tgl_selesai === null || $last->tgl_selesai == '' || $end->lessThanOrEqualTo($start) === false)) {
                    // reuse ongoing attempt
                    $ikutUjian = $last;
                    $needCreate = false;
                }
            }

            if ($needCreate) {
                // Prevent creating a new attempt when there is already a submitted/finished attempt
                $existingSubmitted = \App\Models\IkutUjian::where('id_tes', $idUjian)
                    ->where('id_user', $kon_id)
                    ->where(function($q){
                        $q->where('is_submitted', 1)->orWhereNotNull('tgl_selesai');
                    })->first();
                if ($existingSubmitted) {
                    // clearer message when student previously submitted
                    return redirect()->route('daftar_ujian')->withErrors(['token' => 'Sudah ujian: Anda telah mengumpulkan jawaban untuk ujian ini.']);
                }

                $ikutUjian = \App\Models\IkutUjian::create([
                    'id_tes' => $idUjian,
                    'id_user' => $kon_id,
                    'list_soal' => '',
                    'list_jawaban' => '',
                    'jml_benar' => 0,
                    'nilai' => 0,
                    'nilai_bobot' => 0,
                    'status_lulus' => '',
                    'tgl_mulai' => Carbon::now('Asia/Jakarta'),
                    'tgl_selesai' => null,
                    'is_submitted' => 0,
                ]);
            }

            session(['ikut_ujian_id' => $ikutUjian->id]);
        }

        // Calculate remaining seconds so timer persists across refresh
        $remaining_seconds = null;
        $waktu_mulai_value = $waktu_mulai;
        $server_time = Carbon::now('Asia/Jakarta')->timestamp;
        $start_time = null;
        $allowed_submit_until = null; // timestamp until which submit is allowed

        if ($ujian) {
            // Compute exam end timestamp (authoritative end time)
            try {
                $examEnd = Carbon::parse($ujian->terlambat, 'Asia/Jakarta');
            } catch (\Exception $ex) {
                $examEnd = null;
            }

            // compute allowed submit until (terlambat + allow_submit_after minutes)
            if ($examEnd) {
                $allowExtra = intval($ujian->allow_submit_after) > 0 ? intval($ujian->allow_submit_after) : 0;
                $allowed_submit_until = $examEnd->copy()->addMinutes($allowExtra)->timestamp;
            }

            $now = Carbon::now('Asia/Jakarta');

            // Remaining seconds should be calculated from the exam end time (terlambat)
            if ($examEnd) {
                // use timestamp difference to get signed remaining seconds (examEnd - now)
                $remaining = $examEnd->timestamp - $now->timestamp;
                $remaining_seconds = $remaining > 0 ? intval($remaining) : 0;
            } else {
                // fallback to waktu field (informational) if terlambat not parseable
                $remaining_seconds = intval($ujian->waktu) * 60;
            }

            // Ensure peserta attempt record has start_time set for logging, but timer is based on exam end
            if ($ikutUjian) {
                if (empty($ikutUjian->tgl_mulai)) {
                    $ikutUjian->tgl_mulai = Carbon::now('Asia/Jakarta');
                    $ikutUjian->save();
                }
                $start = Carbon::parse($ikutUjian->tgl_mulai, 'Asia/Jakarta');
                $waktu_mulai_value = $start->format('Y-m-d H:i:s');
                $start_time = $start->timestamp;
            }
        }

        // determine whether the current attempt is already submitted
        $currentAttemptSubmitted = false;
        if (isset($ikutUjian) && $ikutUjian) {
            $currentAttemptSubmitted = (int) ($ikutUjian->is_submitted ?? 0) === 1;
        }

    return view('ujian.mulai_ujian', compact('soals','ujian', 'waktu_mulai_value', 'selectedMapels', 'remaining_seconds', 'server_time', 'start_time', 'allowed_submit_until', 'currentAttemptSubmitted','allSoalIds'));
    }

    /** Return server timestamp for client sync */
    public function serverTime()
    {
        return response()->json(['server_time' => Carbon::now('Asia/Jakarta')->timestamp]);
    }

    /**
     * Return currently active ujian for admin dashboards (if any).
     */
    /**
     * Return currently active ujian for dashboards and student timer resync.
     * Prioritas: (1) ujian milik siswa di session (id_ujian) bila masih
     * dalam jendela, (2) se-mode segmen siswa, (3) sembarang (dashboard admin).
     */
    public function activeUjian(Request $request)
    {
        $now = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s');

        $inWindow = function ($q) use ($now) {
            return $q->where('tgl_mulai', '<=', $now)->where('terlambat', '>=', $now);
        };

        $ujian = null;

        // 1. Ujian yang sedang dikerjakan siswa (anti timer loncat ke ujian lain)
        $idUjian = session('id_ujian');
        if ($idUjian) {
            $ujian = GuruTes::where('id', $idUjian);
            $ujian = $inWindow(clone $ujian)->first();
        }

        // 2. Se-mode segmen siswa
        if (!$ujian && session('kon_id')) {
            $siswa = Siswa::find(session('kon_id'));
            $segment = ($siswa && $siswa->segment === 'tryout') ? 'tryout' : 'cbt';
            $q = GuruTes::where('mode', $segment);
            $ujian = $inWindow($q)->orderBy('tgl_mulai', 'asc')->first();
        }

        // 3. Fallback lama (dashboard admin / tanpa session)
        if (!$ujian) {
            $ujian = GuruTes::where('tgl_mulai', '<=', $now)
                ->where('terlambat', '>=', $now)
                ->orderBy('tgl_mulai', 'asc')
                ->first();
        }

        if (! $ujian) {
            return response()->json(['active' => false]);
        }

        try {
            $terl = Carbon::parse($ujian->terlambat, 'Asia/Jakarta');
            $allowExtra = intval($ujian->allow_submit_after) > 0 ? intval($ujian->allow_submit_after) : 0;
            $allowed_submit_until = $terl->copy()->addMinutes($allowExtra)->timestamp;
        } catch (\Exception $ex) {
            $allowed_submit_until = null;
        }

        return response()->json([
            'active' => true,
            'id' => $ujian->id,
            'nama_ujian' => $ujian->nama_ujian,
            'tgl_mulai' => $ujian->tgl_mulai,
            'terlambat' => $ujian->terlambat,
            'terlambat_ts' => isset($terl) ? $terl->timestamp : null,
            'allowed_submit_until' => $allowed_submit_until,
            'server_time' => Carbon::now('Asia/Jakarta')->timestamp,
        ]);
    }

    public function cekToken(Request $request)
    {
        $idTes = $request->input('id_ujian');
        $tokenInput = strtoupper(trim($request->input('token')));

        $ujian = GuruTes::find($idTes);

        return response()->json(['valid' => $ujian && $ujian->token === $tokenInput]);
    }

    // public function submitJawaban(Request $request, $id)
    // {
    //     $jawaban = $request->input('jawaban', []);
    //     $id_user = session('kon_id');
    //     $jumlah_benar = 0;
    //     $total_soal = count($jawaban);

    //     DB::table('jawaban_siswa')
    //         ->where('id_user', $id_user)
    //         ->where('id_ujian', $id)
    //         ->delete();

    //     foreach ($jawaban as $id_soal => $jawaban_siswa) {
    //         $soal = DB::table('m_soal')->where('id', $id_soal)->first();
    //         $is_benar = $soal && $soal->jawaban == $jawaban_siswa;
    //         if ($is_benar) {
    //             $jumlah_benar++;
    //         }

    //         DB::table('jawaban_siswa')->insert([
    //             'id_user' => $id_user,
    //             'id_ujian' => $id,
    //             'id_soal' => $id_soal,
    //             'jawaban' => $jawaban_siswa,
    //             'created_at' => now(),
    //         ]);
    //     }

    //     $skor = $total_soal > 0 ? round(($jumlah_benar / $total_soal) * 100) : 0;
    //     $ujian = DB::table('tr_guru_tes')->where('id', $id)->first();
    //     $nilai_minimal = $ujian->nilai_minimal ?? 0;

    //     if ($skor >= $nilai_minimal) {
    //         return view('ujian.lolos_ujian_page', compact('skor', 'jumlah_benar', 'total_soal'));
    //     } else {
    //         return view('ujian.gagal_ujian_page', compact('skor', 'jumlah_benar', 'total_soal'));
    //     }
    // }

    public function submitJawaban(Request $request)
    {
        $jawaban = $request->jawaban ?? [];

        $keys = array_keys($jawaban);
        $listSoalString = implode(',', $keys);

        $jawabanString = implode(',', array_map(
            fn($key, $value) => "$key:$value",
            array_keys($jawaban),
            $jawaban
        ));

        $allowed = session('allowed_mapels', []);
        $kon_id = session('kon_id');
        $idUjian = session('id_ujian');

        // SERVER-SIDE: prevent resubmission
        // If there is an active attempt id in session, check if it's already submitted
        $ikutId = session('ikut_ujian_id');
        if ($ikutId) {
            $attempt = DB::table('tr_ikut_ujian')->where('id', $ikutId)->first();
            if ($attempt && (isset($attempt->is_submitted) && intval($attempt->is_submitted) === 1)) {
                // already submitted -> show warning page
                return view('ujian.already_submitted');
            }
        }

        // Fallback: if no session attempt but there exists a submitted attempt for this user/test, block
        if (! $ikutId && $kon_id && $idUjian) {
            $exists = DB::table('tr_ikut_ujian')
                ->where('id_user', $kon_id)
                ->where('id_tes', $idUjian)
                ->where(function($q){ $q->where('is_submitted',1)->orWhereNotNull('tgl_selesai'); })
                ->exists();
            if ($exists) {
                return view('ujian.already_submitted');
            }
        }

        $soalsQuery = DB::table('m_soal');
        if (!empty($allowed)) {
            $soalsQuery->whereIn('id_mapel', $allowed);
        }
        $soals = $soalsQuery->get();

        $jml_benar = 0;
        foreach ($soals as $soal) {
            foreach ($jawaban as $key => $jawab) {
                if ($soal->id == $key) {
                    if ($soal->jawaban == $jawab) {
                        $jml_benar += 1;
                    }
                }
            }
        }

        if ($jml_benar >= 1 ) {
            $status = 'lulus';
        }else{
            $status = 'tidak lulus';
        }

        $waktu_selesai = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s');

        // Prefer updating the existing attempt (session 'ikut_ujian_id') if present
        $ikutId = session('ikut_ujian_id');
        if ($ikutId) {
            DB::table('tr_ikut_ujian')->where('id', $ikutId)->update([
                'list_soal' => $listSoalString,
                'list_jawaban' => $jawabanString,
                'jml_benar' => $jml_benar,
                'nilai' => 0,
                'nilai_bobot' => 0,
                'status_lulus' => $status,
                'tgl_selesai' => $waktu_selesai,
                'is_submitted' => 1,
            ]);
        } else {
            // fallback: create a new attempt record and mark it submitted
            DB::table('tr_ikut_ujian')->insert([
                'id_tes' => $idUjian,
                'id_user' => $kon_id,
                'list_soal' => $listSoalString,
                'list_jawaban' => $jawabanString,
                'jml_benar' => $jml_benar,
                'nilai' => 0,
                'nilai_bobot' => 0,
                'status_lulus' => $status,
                'tgl_mulai' => $request->get('waktu_mulai'),
                'tgl_selesai' => $waktu_selesai,
                'is_submitted' => 1,
            ]);
        }

        // Mark siswa as having participated to prevent multiple full attempts (backward-compat)
        try {
            if ($kon_id) {
                $s = \App\Models\Siswa::find($kon_id);
                if ($s) {
                    $s->markParticipated();
                }
            }
        } catch (\Exception $e) {
            // non-fatal
        }

        // Release active tab and clear session ikuit_ujian_id to prevent reuse from back-navigation
        try {
            $ikutId = session('ikut_ujian_id');
            if ($ikutId) {
                DB::table('tr_ikut_ujian')->where('id', $ikutId)->update([
                    'active_tab_id' => null,
                    'active_tab_ping' => null
                ]);
                // remove from session
                session()->forget('ikut_ujian_id');
            }
        } catch (\Exception $e) {
            // ignore
        }
            
        // Simpan hasil telah dilakukan di DB di atas. Tampilkan pesan netral.
        $siswa = $kon_id ? \App\Models\Siswa::find($kon_id) : null;
        $isCbt = ($siswa && !empty($siswa->nim));
        
        return view('ujian.thanks', compact('isCbt', 'siswa'));
    }

    public function index(Request $request)
    {
        $scoped = $this->scopedMode($request);
        $query = GuruTes::query()->mode($scoped);

        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nama_ujian', 'like', "%$search%");
        }

        $ujians = $query->paginate(10);

        // Build a lookup of mapel id => nama for all mapel ids used on this page
        $allIds = [];
        foreach ($ujians as $u) {
            if (method_exists($u, 'getMapelIdsAttribute')) {
                $ids = $u->mapel_ids;
            } else {
                // fallback: legacy single id_mapel
                $ids = $u->id_mapel ? [$u->id_mapel] : [];
            }
            if (!empty($ids)) $allIds = array_merge($allIds, $ids);
        }
        $allIds = array_unique(array_filter(array_map('intval', $allIds)));
        $mapelLookup = [];
        if (!empty($allIds)) {
            $mapelLookup = Mapel::whereIn('id', $allIds)->pluck('nama', 'id')->toArray();
        }

        // Info sesi aktif (untuk hint tombol Reset Demo)
        $sesiAktif = DB::table('cbt_sessions')->where('active', true)->first();

        return view('ujian.index', array_merge(
            compact('ujians', 'mapelLookup', 'sesiAktif'),
            $this->modeMeta($scoped)
        ));
    }

    /**
     * Bulk delete selected ujian records.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:tr_guru_tes,id',
        ]);

        $ids = $request->input('ids', []);

        // Optionally: add authorization check here (e.g., only admin can delete)
        // Delete related data if necessary (jawaban_siswa, ikut_ujian, etc.) -- for now just delete the tests
        GuruTes::whereIn('id', $ids)->delete();

        return $this->modeRedirect($this->currentMode($request), 'ujian.index')->with('success', count($ids) . ' ujian berhasil dihapus.');
    }

    /**
     * Reset Demo satu klik: hapus semua attempt akun demo (username demo%,
     * status test) pada ujian DEMO CBT + buka kunci partisipasi.
     * Opsional: aktifkan sesi DEMO sekaligus (checkbox, default ya).
     */
    public function resetDemo(Request $request)
    {
        $mode = $this->currentMode($request);

        // username tersimpan di m_admin: hanya akun demo (test + username demo%) yang disentuh
        $demoIds = DB::table('m_siswa')
            ->join('m_admin', function ($j) {
                $j->on('m_admin.kon_id', '=', 'm_siswa.id')->where('m_admin.level', 'siswa');
            })
            ->where('m_siswa.status_akun', 'test')
            ->where('m_admin.username', 'like', 'demo%')
            ->pluck('m_siswa.id')
            ->toArray();

        $ujian = GuruTes::where('nama_ujian', 'DEMO CBT')->where('mode', 'cbt')->first();
        if (!$ujian) {
            return $this->modeRedirect($mode, 'ujian.index')->with('error', 'Ujian DEMO CBT tidak ditemukan.');
        }

        $deleted = 0;
        if (!empty($demoIds)) {
            $deleted = DB::table('tr_ikut_ujian')
                ->whereIn('id_user', $demoIds)
                ->where('id_tes', $ujian->id)
                ->delete();

            $remaining = DB::table('tr_ikut_ujian')->whereIn('id_user', $demoIds)->count();
            if ($remaining === 0) {
                DB::table('m_siswa')->whereIn('id', $demoIds)->update(['has_participated' => 0]);
            }
        }

        $sesiMsg = '';
        if ($request->boolean('aktifkan_sesi', true)) {
            $demo = DB::table('cbt_sessions')->where('key', 'DEMO')->first();
            if ($demo) {
                DB::table('cbt_sessions')->update(['active' => 0]);
                DB::table('cbt_sessions')->where('id', $demo->id)->update(['active' => 1]);
                $sesiMsg = ' Sesi DEMO diaktifkan.';
            }
        }

        return $this->modeRedirect($mode, 'ujian.index')
            ->with('success', "Reset demo selesai: {$deleted} attempt dihapus, " . count($demoIds) . " akun siap dipakai ulang." . $sesiMsg);
    }

    /**
     * AJAX endpoint to report a cheating event (tab switch, minimize, etc.)
     */
    public function reportCheat(Request $request)
    {
        $ikutId = session('ikut_ujian_id');
        if (! $ikutId) {
            return response()->json(['error' => 'No active attempt found'], 422);
        }

        $ikut = \App\Models\IkutUjian::find($ikutId);
        if (! $ikut) {
            return response()->json(['error' => 'Attempt not found'], 404);
        }

        // Basic rate-limiting: avoid counting repeated rapid events from same client
        $lastReportKey = "ikut_{$ikutId}_last_report_at";
        $last = session($lastReportKey);
        $now = time();
        if ($last && ($now - $last) < 1) {
            // ignore very rapid repeated reports
            return response()->json(['cheat_count' => $ikut->cheat_count ?? 0, 'flag_cheat' => $ikut->flag_cheat ?? false]);
        }
        session([$lastReportKey => $now]);

        $ikut->cheat_count = ($ikut->cheat_count ?? 0) + 1;
        // threshold 2 => mark flag_cheat true when > 2
        if ($ikut->cheat_count > 2) {
            $ikut->flag_cheat = true;
        }
        $ikut->save();

        return response()->json(['cheat_count' => $ikut->cheat_count, 'flag_cheat' => $ikut->flag_cheat]);
    }

    /**
     * Autosave a single answer (AJAX).
     * Expects JSON: { soal_id: int, jawaban: string }
     */
    public function autosave(Request $request)
    {
        $soalId = $request->input('soal_id');
        $jawab = $request->input('jawaban');

        $ikutId = session('ikut_ujian_id');
        if (! $ikutId) {
            return response()->json(['error' => 'No active attempt'], 422);
        }

        $ikut = \App\Models\IkutUjian::find($ikutId);
        if (! $ikut) {
            return response()->json(['error' => 'Attempt not found'], 404);
        }

        // parse existing list_jawaban into array
        $current = [];
        if (!empty($ikut->list_jawaban)) {
            $pairs = explode(',', $ikut->list_jawaban);
            foreach ($pairs as $p) {
                if (strpos($p, ':') !== false) {
                    [$id, $val] = explode(':', $p, 2);
                    $current[trim($id)] = trim($val);
                }
            }
        }

        // set new value
        if ($soalId) {
            $current[(string)$soalId] = (string)$jawab;
        }

        // rebuild list_jawaban and list_soal
        $listJawabanArr = [];
        $listSoalArr = [];
        foreach ($current as $id => $val) {
            $listJawabanArr[] = $id . ':' . $val;
            $listSoalArr[] = $id;
        }

        $ikut->list_jawaban = implode(',', $listJawabanArr);
        $ikut->list_soal = implode(',', $listSoalArr);
        // update last saved timestamp
        $ikut->save();

        return response()->json(['ok' => true]);
    }

    /** Register a tab as active for the current attempt. Returns blocked=true if another active tab exists. */
    public function registerTab(Request $request)
    {
        $ikutId = session('ikut_ujian_id');
        if (! $ikutId) return response()->json(['error' => 'No active attempt'], 422);

        $tabId = $request->input('tab_id');
        if (! $tabId) return response()->json(['error' => 'tab_id required'], 422);

        $ikut = \App\Models\IkutUjian::find($ikutId);
        if (! $ikut) return response()->json(['error' => 'Attempt not found'], 404);

        // If there's an active tab recently pinging, block this new tab
        if ($ikut->active_tab_id && $ikut->active_tab_id !== $tabId) {
            $lastPing = $ikut->active_tab_ping ? strtotime($ikut->active_tab_ping) : 0;
            // consider stale if older than 12 seconds
            if (time() - $lastPing < 12) {
                return response()->json(['blocked' => true, 'active_tab_id' => $ikut->active_tab_id]);
            }
        }

        // Register this tab as active
        $ikut->active_tab_id = $tabId;
        $ikut->active_tab_ping = now();
        $ikut->save();

        return response()->json(['blocked' => false]);
    }

    /** Ping from active tab to keep it alive */
    public function pingTab(Request $request)
    {
        $ikutId = session('ikut_ujian_id');
        if (! $ikutId) return response()->json(['error' => 'No active attempt'], 422);

        $tabId = $request->input('tab_id');
        if (! $tabId) return response()->json(['error' => 'tab_id required'], 422);

        $ikut = \App\Models\IkutUjian::find($ikutId);
        if (! $ikut) return response()->json(['error' => 'Attempt not found'], 404);

        if ($ikut->active_tab_id === $tabId) {
            $ikut->active_tab_ping = now();
            $ikut->save();
            return response()->json(['ok' => true]);
        }

        // If the active_tab_ping is stale (older than 12s), allow takeover
        $lastPing = $ikut->active_tab_ping ? strtotime($ikut->active_tab_ping) : 0;
        if ($lastPing && (time() - $lastPing) > 12) {
            // allow this tab to claim active status
            $ikut->active_tab_id = $tabId;
            $ikut->active_tab_ping = now();
            $ikut->save();
            return response()->json(['ok' => true, 'taken' => true]);
        }

        return response()->json(['ok' => false, 'active_tab_id' => $ikut->active_tab_id]);
    }

    /** Release the active tab when unloading */
    public function releaseTab(Request $request)
    {
        $ikutId = session('ikut_ujian_id');
        if (! $ikutId) return response()->json(['error' => 'No active attempt'], 422);

        $tabId = $request->input('tab_id');
        $ikut = \App\Models\IkutUjian::find($ikutId);
        if (! $ikut) return response()->json(['error' => 'Attempt not found'], 404);

        // Only clear active tab if the same tab asks to release (avoid accidental clears)
        if ($tabId && $ikut->active_tab_id === $tabId) {
            $ikut->active_tab_id = null;
            $ikut->active_tab_ping = null;
            $ikut->save();
        }

        return response()->json(['ok' => true]);
    }

    /**
     * Show form to create a new ujian (admin)
     */
    public function create()
    {
        $mapels = Mapel::all();

        return view('ujian.create', array_merge(compact('mapels'), $this->modeMeta($this->scopedMode())));
    }

    /**
     * Store new ujian
     */
    public function store(Request $request)
    {
        $mode = $this->currentMode($request);
        $request->validate([
            'nama_ujian' => 'required|string|max:255',
            // Accept either id_mapel (legacy) or id_mapels[] (multiple)
            'id_mapels' => 'nullable|array',
            'id_mapels.*' => 'integer|exists:m_mapel,id',
            'jumlah_soal' => 'nullable|integer|min:0',
            'waktu' => 'nullable|integer|min:0', // optional, only for display
            'allow_submit_after' => 'nullable|integer|min:0',
            'tgl_mulai' => 'required|date',
            'terlambat' => 'required|date|after_or_equal:tgl_mulai',
            'nilai_minimal' => 'nullable|numeric|min:0',
            'mode' => 'nullable|in:cbt,tryout',
        ]);

        // Normalize datetime-local inputs to Y-m-d H:i:s
        $mulai = Carbon::parse($request->input('tgl_mulai'))->format('Y-m-d H:i:s');
        $terlambat = Carbon::parse($request->input('terlambat'))->format('Y-m-d H:i:s');

    $data = $request->only(['nama_ujian','jumlah_soal','waktu','allow_submit_after','nilai_minimal']);
        // handle multiple mapels: prefer id_mapels[] input
        $idMapels = $request->input('id_mapels', []);
        if (!empty($idMapels) && is_array($idMapels)) {
            $data['id_mapels'] = implode(',', array_map('intval', $idMapels));
            // also set id_mapel to the first selected for backward compatibility
            $data['id_mapel'] = intval($idMapels[0]);
        } else {
            // fallback to legacy single selection
            $data['id_mapel'] = $request->input('id_mapel');
            $data['id_mapels'] = $data['id_mapel'] ? (string)$data['id_mapel'] : null;
        }
        // Ensure numeric default for nilai_minimal to avoid DB NOT NULL constraint
        $data['nilai_minimal'] = $data['nilai_minimal'] !== null ? (int) $data['nilai_minimal'] : 0;
        // Ensure jumlah_soal has a sensible default
        if (!isset($data['jumlah_soal']) || $data['jumlah_soal'] === null) {
            $data['jumlah_soal'] = 0;
        }

    $data['tgl_mulai'] = $mulai;
    $data['terlambat'] = $terlambat;
    $data['allow_submit_after'] = $request->input('allow_submit_after');
    // store empty token by default (admin may generate later)
    $data['token'] = '';
    // Mode halaman (CBT/Tryout); fallback ke mode konteks bila form tidak mengirim
    $data['mode'] = $request->input('mode', $mode);
    if (!in_array($data['mode'], ['cbt', 'tryout'], true)) {
        $data['mode'] = $mode;
    }

    // Ensure id_guru is set. Prefer the currently authenticated user id if available,
    // otherwise fallback to 1 (adjust as needed for your environment).
    $data['id_guru'] = auth()->id() ?: 1;

    GuruTes::create($data);

        return $this->modeRedirect($data['mode'], 'ujian.index')->with('success', 'Ujian berhasil dibuat.');
    }

    public function edit($id)
    {
        $ujian = GuruTes::with('mapel')->where('id', $id)->firstOrFail();
        $mapels = Mapel::all();

        return view('ujian.edit', array_merge(compact('ujian', 'mapels'), $this->modeMeta($this->scopedMode())));
    }

    public function update(Request $request, $id)
    {
        $mode = $this->currentMode($request);
        $request->validate([
            'nama' => 'required|string|max:255',
            // Accept checkbox array of mapel ids
            'id_mapels' => 'required|array|min:1',
            'id_mapels.*' => 'integer|exists:m_mapel,id',
            'waktu' => 'nullable|integer|min:0',
            'allow_submit_after' => 'nullable|integer|min:0',
            'tgl_mulai' => 'required|date',
            'tgl_terlambat' => 'required|date|after_or_equal:tgl_mulai',
            'mode' => 'nullable|in:cbt,tryout',
        ]);
        
        // Process incoming mapel list
        $idMapels = $request->input('id_mapels', []);
        $idMapelSingle = null;
        if (!empty($idMapels) && is_array($idMapels)) {
            $idMapelSingle = intval($idMapels[0]);
            $idMapelsCsv = implode(',', array_map('intval', $idMapels));
        } else {
            $idMapelSingle = $request->input('id_mapel');
            $idMapelsCsv = $idMapelSingle ? (string)$idMapelSingle : null;
        }

        DB::table('tr_guru_tes')->where('id', $id)->update([
            'nama_ujian' => $request->nama,
            'id_mapel' => $idMapelSingle,
            'id_mapels' => $idMapelsCsv,
            'waktu' => $request->waktu,
            'allow_submit_after' => $request->input('allow_submit_after'),
            'tgl_mulai' => $request->tgl_mulai,
            'terlambat' => $request->tgl_terlambat,
            'mode' => $request->input('mode', $mode),
        ]);

        return $this->modeRedirect($request->input('mode', $mode), 'ujian.index')->with('success', 'Data ujian berhasil diperbarui.');
    }

}
