<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MapelTryoutController extends Controller
{
    // 🟢 Tampilkan semua data
    public function index(Request $request)
    {
        // Mulai query builder
        $query = DB::table('m_mapel_tryout');

        // Jika ada parameter search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('nama', 'like', "%{$search}%")
                ->orWhere('kategori', 'like', "%{$search}%");
        }

        // Ambil data dengan paginasi 10
        $mapels = $query->orderBy('id', 'desc')->paginate(10);

        // Kirim data ke view
        return view('mapel_tryout.index', compact('mapels'));
    }

    // 🟡 Tampilkan form tambah data
    public function create()
    {
        return view('mapel_tryout.create');
    }

    // 🟢 Simpan data baru
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'kategori' => 'required|string|max:50',
        ]);

        DB::table('m_mapel_tryout')->insert([
            'nama' => $request->nama,
            'kategori' => $request->kategori,
        ]);

        return redirect()->route('mapel_tryout.index')->with('success', 'Data berhasil ditambahkan!');
    }

    // 🟡 Tampilkan form edit
    public function edit($id)
    {
        $mapel = DB::table('m_mapel_tryout')->where('id', $id)->first();
        return view('mapel_tryout.edit', compact('mapel'));
    }

    // 🟢 Update data
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'kategori' => 'required|string|max:50',
        ]);

        DB::table('m_mapel_tryout')->where('id', $id)->update([
            'nama' => $request->nama,
            'kategori' => $request->kategori,
        ]);

        return redirect()->route('mapel_tryout.index')->with('success', 'Data berhasil diperbarui!');
    }

    // 🔴 Hapus data
    public function destroy($id)
    {
        DB::table('m_mapel_tryout')->where('id', $id)->delete();

        return redirect()->route('mapel_tryout.index')->with('success', 'Data berhasil dihapus!');
    }
}
