<?php

namespace App\Http\Controllers;

use App\Models\GrupUjian;
use App\Models\GrupUjianProgress;
use App\Models\HasilUjian;
use App\Models\Soal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class GrupUjianSiswaController extends Controller
{
    public function index()
    {
        $siswa = Auth::guard('siswa')->user();
        $grupUjians = GrupUjian::where('is_active', true)
            ->where('waktu_mulai', '<=', now())
            ->where('waktu_selesai', '>=', now())
            ->with(['mapels', 'progress' => function($q) use ($siswa) {
                $q->where('siswa_id', $siswa->id);
            }])
            ->get();

        return view('siswa.grup-ujian.index', compact('grupUjians'));
    }

    public function show(GrupUjian $grupUjian)
    {
        $siswa = Auth::guard('siswa')->user();
        
        if (!$grupUjian->is_active || now() < $grupUjian->waktu_mulai || now() > $grupUjian->waktu_selesai) {
            return back()->with('error', 'Grup ujian tidak tersedia');
        }

        $progress = $grupUjian->progress()
            ->with('mapel')
            ->where('siswa_id', $siswa->id)
            ->get();

        $mapels = $grupUjian->mapels;
        
        return view('siswa.grup-ujian.show', compact('grupUjian', 'progress', 'mapels'));
    }

    public function startMapel(GrupUjian $grupUjian, $mapelId)
    {
        $siswa = Auth::guard('siswa')->user();
        
        // Validasi waktu ujian
        if (!$grupUjian->is_active || now() < $grupUjian->waktu_mulai || now() > $grupUjian->waktu_selesai) {
            return back()->with('error', 'Grup ujian tidak tersedia');
        }

        // Cek apakah mapel ada dalam grup
        if (!$grupUjian->mapels()->where('m_mapel.id', $mapelId)->exists()) {
            return back()->with('error', 'Mata pelajaran tidak tersedia dalam grup ini');
        }

        try {
            DB::beginTransaction();

            // Cek atau buat progress
            $progress = GrupUjianProgress::firstOrCreate([
                'grup_ujian_id' => $grupUjian->id,
                'mapel_id' => $mapelId,
                'siswa_id' => $siswa->id
            ]);

            // Jika belum mulai, set waktu mulai
            if (!$progress->waktu_mulai) {
                $progress->waktu_mulai = now();
                $progress->save();
            }

            // Ambil soal untuk ujian
            $soals = Soal::where('mapel_id', $mapelId)
                ->inRandomOrder()
                ->take(50) // Sesuaikan dengan kebutuhan
                ->get();

            DB::commit();

            return view('siswa.grup-ujian.ujian', compact('grupUjian', 'progress', 'soals'));

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat memulai ujian');
        }
    }

    public function submitMapel(Request $request, GrupUjian $grupUjian, $mapelId)
    {
        $siswa = Auth::guard('siswa')->user();
        
        $progress = GrupUjianProgress::where([
            'grup_ujian_id' => $grupUjian->id,
            'mapel_id' => $mapelId,
            'siswa_id' => $siswa->id
        ])->first();

        if (!$progress || $progress->is_finished) {
            return back()->with('error', 'Ujian tidak ditemukan atau sudah selesai');
        }

        try {
            DB::beginTransaction();

            // Proses jawaban
            $benar = 0;
            $salah = 0;
            $nilai = 0;
            $totalSoal = count($request->jawaban ?? []);

            if ($totalSoal > 0) {
                foreach ($request->jawaban as $soalId => $jawaban) {
                    $soal = Soal::find($soalId);
                    if ($soal && $soal->kunci_jawaban === $jawaban) {
                        $benar++;
                    } else {
                        $salah++;
                    }
                }

                $nilai = ($benar / $totalSoal) * 100;
            }

            // Simpan hasil ujian
            HasilUjian::create([
                'siswa_id' => $siswa->id,
                'mapel_id' => $mapelId,
                'nilai' => $nilai,
                'benar' => $benar,
                'salah' => $salah,
                'total_soal' => $totalSoal
            ]);

            // Update progress
            $progress->update([
                'waktu_selesai' => now(),
                'is_finished' => true
            ]);

            DB::commit();

            return redirect()
                ->route('siswa.grup-ujian.show', $grupUjian)
                ->with('success', 'Ujian berhasil diselesaikan');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat menyelesaikan ujian');
        }
    }
}