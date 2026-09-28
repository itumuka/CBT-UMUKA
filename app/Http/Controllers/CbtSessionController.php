<?php

namespace App\Http\Controllers;

use App\Models\CbtSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class CbtSessionController extends Controller
{
    public function index()
    {
        $sessions = CbtSession::orderBy('id')->get();
        return view('cbt_sessions.index', compact('sessions'));
    }

    public function create()
    {
        return view('cbt_sessions.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'key' => 'required|string|max:50|unique:cbt_sessions,key',
            'name' => 'required|string|max:100',
            'active' => 'nullable|boolean'
        ]);

        $data = $request->only(['key','name']);
        $data['active'] = $request->has('active') ? (bool)$request->active : false;

        // Jika active = true, matikan semua session lain
        if ($data['active']) {
            CbtSession::query()->update(['active' => false]);
        }

        CbtSession::create($data);

        return Redirect::route('cbt_sessions.index')->with('success', 'Sesi berhasil ditambahkan.');
    }

    public function destroy($id)
    {
        CbtSession::findOrFail($id)->delete();
        return Redirect::route('cbt_sessions.index')->with('success', 'Sesi berhasil dihapus.');
    }

    public function toggleActive($id)
    {
        $s = CbtSession::findOrFail($id);
        // toggle: jika akan diaktifkan, nonaktifkan yang lain
        if (! $s->active) {
            CbtSession::query()->update(['active' => false]);
            $s->active = true;
            $s->save();
            return Redirect::route('cbt_sessions.index')->with('success', 'Sesi diaktifkan.');
        }

        // jika dimatikan
        $s->active = false;
        $s->save();
        return Redirect::route('cbt_sessions.index')->with('success', 'Sesi dinonaktifkan.');
    }

    /**
     * Toggle token activation status for a session
     */
    public function toggleToken(Request $request, $id)
    {
        $s = CbtSession::findOrFail($id);

        // Hanya bisa set token untuk sesi yang aktif
        if (!$s->active) {
            return Redirect::route('cbt_sessions.index')
                ->with('error', 'Hanya sesi aktif yang dapat mengubah status token.');
        }

        // Accept status from form (0=off,1=real,2=tester). If not provided, toggle between 0 and 1.
        $status = $request->input('status', null);
        if ($status !== null) {
            $status = (int)$status;
            if (!in_array($status, [0,1,2])) {
                return Redirect::route('cbt_sessions.index')->with('error', 'Status token tidak valid.');
            }
            $s->token_status = $status;
            $s->save();

            $label = $status === 0 ? 'dinonaktifkan' : ($status === 1 ? 'aktif (REAL)' : 'aktif (TESTER)');
            return Redirect::route('cbt_sessions.index')->with('success', "Status token telah diset: {$label}.");
        }

        // fallback: toggle between off (0) and real (1)
        $current = $s->token_status ?? 0;
        $new = $current === 0 ? 1 : 0;
        $s->token_status = $new;
        $s->save();

        $label = $new === 0 ? 'dinonaktifkan' : 'diaktifkan (REAL)';
        return Redirect::route('cbt_sessions.index')->with('success', "Token untuk sesi ini telah {$label}.");
    }
}
