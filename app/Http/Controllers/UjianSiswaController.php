<?php

namespace App\Http\Controllers;

use App\Models\GrupUjian;
use App\Models\Siswa;
use Illuminate\Http\Request;

class UjianSiswaController extends Controller
{
    public function verifikasiToken(Request $request)
    {
        $request->validate([
            'token' => 'required|string'
        ]);

        // Cari siswa berdasarkan token
        $siswa = Siswa::where('token', $request->token)->first();
        
        if (!$siswa) {
            return back()->with('error', 'Token tidak valid!');
        }

        // Cari grup ujian yang aktif untuk siswa tersebut
        $grupUjian = GrupUjian::where('siswa_id', $siswa->id)
            ->where('aktif', true)
            ->where('waktu_mulai', '<=', now())
            ->where('waktu_selesai', '>=', now())
            ->first();

        if (!$grupUjian) {
            return back()->with('error', 'Tidak ada ujian yang aktif untuk token ini!');
        }

        // Simpan informasi ujian ke session
        session([
            'ujian_aktif' => true,
            'grup_ujian_id' => $grupUjian->id,
            'siswa_id' => $siswa->id,
            'waktu_mulai_ujian' => now(),
            'durasi_menit' => $grupUjian->durasi_menit
        ]);

        return redirect()->route('ujian.mulai');
    }

    public function mulaiUjian()
    {
        if (!session('ujian_aktif')) {
            return redirect()->route('ujian.token');
        }

        $grupUjian = GrupUjian::with(['ujians.soal'])->find(session('grup_ujian_id'));
        
        return view('ujian.mulai', compact('grupUjian'));
    }

    public function showTokenForm()
    {
        return view('ujian.token');
    }
}