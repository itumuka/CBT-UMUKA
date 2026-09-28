<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;

/**
 * Mode context CBT vs Tryout.
 *
 * Mode diambil dari (urutan prioritas):
 *  1. default route grup prefix (cbt.* / tryout.*),
 *  2. query ?mode=,
 *  3. session,
 *  4. default 'cbt'.
 *
 * View menerima $mode, $rp (prefix nama route, mis. 'cbt.'),
 * $modeLabel dan $modeBadge untuk badge.
 */
trait ResolvesMode
{
    protected static function modes(): array
    {
        return ['cbt', 'tryout'];
    }

    protected function currentMode(?Request $request = null): string
    {
        $req = $request ?? request();

        $mode = null;
        if ($req) {
            // Segmen pertama URL pada grup prefix (cbt/... / tryout/...)
            $seg = strtolower(trim((string) $req->segment(1)));
            if (in_array($seg, self::modes(), true)) {
                $mode = $seg;
            }
            // Query ?mode= boleh override (mis. tab di halaman)
            if (!$mode && $req->query('mode')) {
                $mode = $req->query('mode');
            }
        }
        if (!$mode) {
            $mode = session('mode', 'cbt');
        }

        $mode = strtolower(trim((string) $mode));
        if (!in_array($mode, self::modes(), true)) {
            $mode = 'cbt';
        }
        session(['mode' => $mode]);

        return $mode;
    }

    public function inModeGroup(): bool
    {
        return in_array(strtolower(trim((string) request()->segment(1))), self::modes(), true);
    }

    /**
     * Mode yang dipakai untuk scoping query: hanya di grup prefix cbt/tryout.
     * Halaman lama (tanpa prefix) mengembalikan null = tanpa scope (semua data).
     */
    protected function scopedMode(?Request $request = null): ?string
    {
        if (!$this->inModeGroup()) {
            // Query ?mode= eksplisit tetap dihormati di halaman lama
            $q = $request ? strtolower(trim((string) $request->query('mode', ''))) : '';
            if (in_array($q, self::modes(), true)) {
                session(['mode' => $q]);

                return $q;
            }

            return null;
        }

        return $this->currentMode($request);
    }

    protected function modeMeta(?string $mode): array
    {
        // Halaman lama tanpa prefix: $rp kosong -> route('siswa.index') tetap jalan,
        // dan tanpa badge mode karena datanya tidak di-scope.
        $inModeGroup = $this->inModeGroup();
        if ($mode && !in_array($mode, self::modes(), true)) {
            $mode = null;
        }

        return [
            'mode' => $mode,
            'rp' => ($inModeGroup && $mode) ? $mode . '.' : '',
            'modeLabel' => $mode === 'cbt' ? 'CBT' : ($mode === 'tryout' ? 'Tryout' : null),
            'modeBadge' => $mode === 'cbt' ? 'primary' : 'success',
        ];
    }

    protected function modeRedirect(string $mode, string $name, $params = [])
    {
        $prefix = $this->inModeGroup() ? $mode . '.' : '';

        return redirect()->route($prefix . $name, $params);
    }
}
