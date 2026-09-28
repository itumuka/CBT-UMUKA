<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SiswaTryoutController extends Controller
{
    // 🟢 Tampilkan semua data
    public function index(Request $request)
    {
        // Mulai query builder
        $query = DB::table('m_siswa_tryout');

        // Jika ada parameter search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                ->orWhere('sekolah', 'like', "%{$search}%");
            });
        }

        // Ambil data dengan paginasi 10
        $siswas = $query->orderBy('id', 'desc')->paginate(10);

        // Loop data siswa, ambil nama mapel dari ID
        foreach ($siswas as $siswa) {
            // Ubah ID mapel wajib menjadi array
            $mapelWajibIds = explode(',', $siswa->mapel_wajib);
            $mapelPilihanIds = explode(',', $siswa->mapel_pilihan);

            // Ambil nama mapel dari tabel m_mapel_tryout
            $siswa->mapel_wajib_nama = DB::table('m_mapel_tryout')
                ->whereIn('id', $mapelWajibIds)
                ->pluck('nama')
                ->toArray();

            $siswa->mapel_pilihan_nama = DB::table('m_mapel_tryout')
                ->whereIn('id', $mapelPilihanIds)
                ->pluck('nama')
                ->toArray();
        }

        // Kirim data ke view
        return view('siswa_tryout.index', compact('siswas'));
    }

    // 🟡 Tampilkan form edit
    public function edit($id)
    {
        $siswa = DB::table('m_siswa_tryout')->where('id', $id)->first();

        // Ubah string "1,2,3" menjadi array [1, 2, 3]
        $mapelWajibSiswa = $siswa && $siswa->mapel_wajib
            ? explode(',', $siswa->mapel_wajib)
            : [];

        $mapelPilihanSiswa = $siswa && $siswa->mapel_pilihan
            ? explode(',', $siswa->mapel_pilihan)
            : [];

        // Ambil daftar mapel dari tabel
        $mapelWajib = DB::table('m_mapel_tryout')->where('kategori', 'wajib')->get();
        $mapelPilihan = DB::table('m_mapel_tryout')->where('kategori', 'pilihan')->get();

        return view('siswa_tryout.edit', compact('siswa', 'mapelWajibSiswa', 'mapelPilihanSiswa', 'mapelWajib', 'mapelPilihan'));
    }

    // 🟢 Update data
    public function update(Request $request, $id)
    {
        $totalMapelWajib = DB::table('m_mapel_tryout')->where('kategori', 'wajib')->count();

        $request->validate([
            'nama' => 'required|string|max:100',
            'sekolah' => 'required|string|max:50',
            'no_hp' => 'required|string|max:50',
            'mapel_wajib' => 'required|array',
            'mapel_pilihan' => 'required|array|size:2',
        ]);

        // Validasi tambahan: pastikan semua mapel wajib dicentang
        if (count($request->mapel_wajib) < $totalMapelWajib) {
            return back()
                ->withErrors(['mapel_wajib' => 'Semua mapel wajib harus dipilih.'])
                ->withInput();
        }

        // Gabungkan array id wajib menjadi string dipisah koma
        $mapelWajibString = implode(',', $request->mapel_wajib);
        $mapelPilihanString = implode(',', $request->mapel_pilihan);
    
        DB::table('m_siswa_tryout')->where('id', $id)->update([
            'nama' => $request->nama,
            'sekolah' => $request->sekolah,
            'no_hp' => $request->no_hp,
            'mapel_wajib' => $mapelWajibString,
            'mapel_pilihan' => $mapelPilihanString,
        ]);
        DB::table('m_admin')->where('level','siswa_tryout')->where('kon_id', $id)->update([
            'username' => $request->nama,
        ]);

        return redirect()->route('siswa_tryout.index')->with('success', 'Data berhasil diperbarui!');
    }

    // 🔴 Hapus data
    public function destroy($id)
    {
        DB::transaction(function () use ($id) {
            DB::table('m_siswa_tryout')->where('id', $id)->delete();
            DB::table('m_admin')->where('level', 'siswa_tryout')->where('kon_id', $id)->delete();
        });

        return redirect()->route('siswa_tryout.index')->with('success', 'Data berhasil dihapus!');
    }
}
