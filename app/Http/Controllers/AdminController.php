<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    // show form for root impersonation
    public function showRootLogin()
    {
        return view('admin.root_login');
    }

    // handle impersonation
    public function doRootLogin(Request $request)
    {
        $request->validate([
            'root_password' => 'required|string',
            'user_id' => 'required|integer',
        ]);

        $root = env('ROOT_PASSWORD', null);
        if (! $root || $root !== $request->input('root_password')) {
            return back()->withErrors(['root_password' => 'Password root salah.']);
        }

        $userId = $request->input('user_id');
        $siswa = Siswa::find($userId);
        if (! $siswa) {
            return back()->withErrors(['user_id' => 'User tidak ditemukan.']);
        }

        // Set session to impersonate the siswa
        session(['kon_id' => $siswa->id, 'impersonated_by_root' => true]);

        return redirect()->route('daftar_ujian')->with('success', 'Berhasil masuk sebagai user ' . $siswa->nama);
    }

    public function revertImpersonation()
    {
        session()->forget(['kon_id', 'impersonated_by_root']);
        return redirect()->route('ujian.index')->with('success', 'Sesi impersonasi telah dihentikan.');
    }
}
