<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GrupUjian;
use App\Models\Mapel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GrupUjianController extends Controller
{
    public function index()
    {
        $grupUjians = GrupUjian::with('mapels')->get();
        return view('admin.grup-ujian.index', compact('grupUjians'));
    }

    public function create()
    {
        $mapels = Mapel::all();
        return view('admin.grup-ujian.create', compact('mapels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_grup' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'waktu_mulai' => 'required|date',
            'waktu_selesai' => 'required|date|after:waktu_mulai',
            'durasi_menit' => 'required|integer|min:1',
            'mapel_ids' => 'required|array|min:1',
            'mapel_ids.*' => 'exists:m_mapel,id'
        ]);

        try {
            DB::beginTransaction();

            $grupUjian = GrupUjian::create([
                'nama_grup' => $request->nama_grup,
                'deskripsi' => $request->deskripsi,
                'waktu_mulai' => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
                'durasi_menit' => $request->durasi_menit,
                'is_active' => true
            ]);

            $grupUjian->mapels()->attach($request->mapel_ids);

            DB::commit();

            return redirect()
                ->route('admin.grup-ujian.index')
                ->with('success', 'Grup ujian berhasil dibuat');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat membuat grup ujian');
        }
    }

    public function edit(GrupUjian $grupUjian)
    {
        $mapels = Mapel::all();
        $selectedMapels = $grupUjian->mapels->pluck('id')->toArray();
        
        return view('admin.grup-ujian.edit', compact('grupUjian', 'mapels', 'selectedMapels'));
    }

    public function update(Request $request, GrupUjian $grupUjian)
    {
        $request->validate([
            'nama_grup' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'waktu_mulai' => 'required|date',
            'waktu_selesai' => 'required|date|after:waktu_mulai',
            'durasi_menit' => 'required|integer|min:1',
            'mapel_ids' => 'required|array|min:1',
            'mapel_ids.*' => 'exists:m_mapel,id'
        ]);

        try {
            DB::beginTransaction();

            $grupUjian->update([
                'nama_grup' => $request->nama_grup,
                'deskripsi' => $request->deskripsi,
                'waktu_mulai' => $request->waktu_mulai,
                'waktu_selesai' => $request->waktu_selesai,
                'durasi_menit' => $request->durasi_menit
            ]);

            $grupUjian->mapels()->sync($request->mapel_ids);

            DB::commit();

            return redirect()
                ->route('admin.grup-ujian.index')
                ->with('success', 'Grup ujian berhasil diperbarui');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat memperbarui grup ujian');
        }
    }

    public function destroy(GrupUjian $grupUjian)
    {
        try {
            DB::beginTransaction();

            // Hapus semua relasi dan data terkait
            $grupUjian->mapels()->detach();
            $grupUjian->progress()->delete();
            $grupUjian->hasil()->delete();
            $grupUjian->delete();

            DB::commit();

            return redirect()
                ->route('admin.grup-ujian.index')
                ->with('success', 'Grup ujian berhasil dihapus');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan saat menghapus grup ujian');
        }
    }

    public function toggleActive(GrupUjian $grupUjian)
    {
        $grupUjian->update(['is_active' => !$grupUjian->is_active]);
        
        return redirect()
            ->route('admin.grup-ujian.index')
            ->with('success', 'Status grup ujian berhasil diperbarui');
    }
}