<?php

namespace App\Http\Controllers;

use App\Models\Soal;
use App\Models\Mapel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\File;

class SoalController extends Controller
{
    // Menampilkan daftar soal
    public function index()
    {
        // Merge all sources into a single master view. We'll show a card dashboard grouped by mapel
        // If ?mapel={id} is present, show the paginated soal list for that mapel.
        $mapelId = request()->get('mapel');

        // Count how many non-master sources exist (for merge button visibility)
        $nonMasterCount = Soal::whereIn('sumber', ['tryout', 'import'])->count();

        if ($mapelId) {
            // show soal list filtered by mapel
            $query = Soal::with('mapel')->where('id_mapel', $mapelId)->orderBy('id', 'desc');
            $soals = $query->paginate(10)->appends(request()->query());
            $source = 'master';
            // provide list of mapels so the view can offer a "move to mapel" dropdown
            $mapelsList = Mapel::orderBy('nama')->get();
            return view('soal.index', compact('soals', 'source', 'mapelId', 'nonMasterCount', 'mapelsList'));
        }

        // dashboard: group soal counts per mapel
        $mapels = DB::table('m_mapel as m')
            ->leftJoin('m_soal as s', 'm.id', '=', 's.id_mapel')
            ->select('m.id', 'm.nama', DB::raw('COUNT(s.id) as soal_count'))
            ->groupBy('m.id', 'm.nama')
            ->orderBy('m.nama')
            ->get();

        // Pass empty paginator for compatibility when rendering
        $soals = null;
        $source = 'master';
        return view('soal.index', compact('mapels', 'soals', 'source', 'nonMasterCount'));
    }

    /**
     * Laporan soal bermasalah terkait gambar:
     *  A. file gambar dirujuk DB tapi hilang dari disk.
     *  B. teks menyebut gambar tapi tidak ada file terlampir.
     */
    public function bermasalah()
    {
        $keywords = [
            'gambar di atas', 'gambar di bawah', 'gambar berikut',
            'perhatikan gambar', 'gambar di samping', 'pada gambar', 'lihat gambar',
        ];

        $soals = Soal::with('mapel')->orderBy('id')->get(['id', 'id_mapel', 'soal', 'file', 'tipe_file']);

        $hilang = [];
        $tanpaFile = [];
        foreach ($soals as $s) {
            if (!empty($s->file)) {
                $ok = File::exists(public_path('upload/gambar_soal/' . $s->file))
                    || File::exists(public_path('image/gambar_soal/' . $s->file));
                if (!$ok) {
                    $hilang[] = $s;
                }
            } else {
                $text = strtolower(strip_tags((string) $s->soal));
                foreach ($keywords as $kw) {
                    if (str_contains($text, $kw)) {
                        $tanpaFile[] = $s;
                        break;
                    }
                }
            }
        }

        return view('soal.bermasalah', compact('hilang', 'tanpaFile'));
    }

    /**
     * Jumlah soal bermasalah (untuk badge sidebar). Di-cache 10 menit.
     */
    public static function countBermasalah(): int
    {
        return (int) \Illuminate\Support\Facades\Cache::remember('soal_bermasalah_count', 600, function () {
            $keywords = [
                'gambar di atas', 'gambar di bawah', 'gambar berikut',
                'perhatikan gambar', 'gambar di samping', 'pada gambar', 'lihat gambar',
            ];

            $count = 0;
            $soals = Soal::orderBy('id')->get(['id', 'soal', 'file']);
            foreach ($soals as $s) {
                if (!empty($s->file)) {
                    $ok = File::exists(public_path('upload/gambar_soal/' . $s->file))
                        || File::exists(public_path('image/gambar_soal/' . $s->file));
                    if (!$ok) {
                        $count++;
                    }
                } else {
                    $text = strtolower(strip_tags((string) $s->soal));
                    foreach ($keywords as $kw) {
                        if (str_contains($text, $kw)) {
                            $count++;
                            break;
                        }
                    }
                }
            }

            return $count;
        });
    }

    /**
     * Convert existing soal with sumber tryout/import into master.
     * This is an administrative helper and should be protected by auth middleware.
     */
    public function mergeSources(Request $request)
    {
        // simple confirmation check
        if (!$request->has('confirm') || $request->input('confirm') !== 'yes') {
            return redirect()->route('soal.index')->with('error', 'Konfirmasi diperlukan untuk melakukan merge sumber soal.');
        }

        $updated = Soal::whereIn('sumber', ['tryout', 'import'])->update(['sumber' => 'master']);

        return redirect()->route('soal.index')->with('success', "Selesai: {$updated} soal berhasil diubah menjadi sumber 'master'.");
    }

    /**
     * Normalize imported text: replace literal _x000D_ markers and CR/LF
     * If the text already contains HTML tags, preserve them and convert newlines to <br>.
     * Otherwise wrap paragraphs (<p>) around double-newline separated blocks.
     */
    private function sanitizeImportedText($text)
    {
        if ($text === null) return '';
        // replace common artifact and normalize newlines
        $t = str_ireplace(['_x000d_', '_x000D_'], "\n", $text);
        $t = str_replace(["\r\n", "\r"], "\n", $t);
        $t = trim($t);

        if ($t === '') return '';

        // if it looks like HTML already, keep tags and convert newlines to <br>
        if (preg_match('/<[^>]+>/', $t)) {
            return nl2br($t);
        }

        // plain text: split paragraphs on empty lines, wrap with <p>, convert single newlines to <br>
        $parts = preg_split('/\n{2,}/', $t);
        $parts = array_map(function ($p) {
            $p = trim($p);
            // escape to avoid injecting HTML from imports
            $p = e($p);
            // keep single newlines as <br>
            $p = nl2br($p);
            return '<p>' . $p . '</p>';
        }, $parts);

        return implode("\n", $parts);
    }

      // Form tambah soal
      public function create()
      {
        $source = request()->get('source', 'master');
        if ($source === 'tryout' && Schema::hasTable('m_mapel_tryout')) {
            $p_mapel = DB::table('m_mapel_tryout')->get();
        } else {
            $p_mapel = Mapel::all(); // atau pluck('nama', 'id') sesuai kebutuhan
        }
          $huruf_opsi = ['a','b','c','d','e'];
      
          // untuk tampilan awal (kosong)
          $soal = new Soal();
          $data_pc = [];

        return view('soal.create', compact('p_mapel', 'huruf_opsi', 'soal', 'data_pc', 'source'));
      }
  
      // Simpan soal baru
      public function store(Request $request)
        {
            $tgl_input = Carbon::now('Asia/Jakarta')->format('Y-m-d H:i:s');
            
            $request->validate([
                'id_mapel' => 'required',
                'file' => 'nullable|file|max:5120',
                'jenis' => 'nullable|string|in:teks,listening',
                'soal' => 'required|string',
                'opsi_a' => 'required|string',
                'opsi_b' => 'required|string',
                'opsi_c' => 'required|string',
                'opsi_d' => 'required|string',
                'opsi_e' => 'required|string',
                'jawaban' => 'required|string',
            ]);

            // Simpan data awal tanpa file
            $data = $request->except('file');
            // pastikan sumber terset (master/tryout)
            $data['sumber'] = $request->input('sumber', $request->get('source', 'master'));
            $data['tgl_input'] = $tgl_input;
            
            $soal = Soal::create($data);

            // Jika ada file, upload dan update soal
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $ext = $file->getClientOriginalExtension();
                $mime = $file->getClientMimeType();

                // Jika jenis listening, simpan sebagai audio
                if (($data['jenis'] ?? '') === 'listening') {
                    $filename = 'audio_soal_' . $soal->id . '.' . $ext;
                    $dest = public_path('upload/audio_soal');
                    File::ensureDirectoryExists($dest);
                    $file->move($dest, $filename);

                    $soal->update([
                        'file' => $filename,
                        'tipe_file' => $mime,
                    ]);
                } else {
                    // default: gambar soal
                    $filename = 'gambar_soal_' . $soal->id . '.' . $ext;
                    $dest = public_path('upload/gambar_soal');
                    File::ensureDirectoryExists($dest);
                    $file->move($dest, $filename);

                    $soal->update([
                        'file' => $filename,
                        'tipe_file' => $mime,
                    ]);
                }
            }

            return redirect()->route('soal.index', ['source' => $data['sumber'] ?? 'master'])->with('success', 'Soal berhasil ditambahkan.');
        }



    // Menampilkan halaman edit soal
    public function edit($id)
    {
        $soal = Soal::findOrFail($id);
        $source = request()->get('source', 'master');
        if ($source === 'tryout' && Schema::hasTable('m_mapel_tryout')) {
            $mapels = DB::table('m_mapel_tryout')->get();
        } else {
            $mapels = Mapel::all();
        }
        $huruf_opsi = ['a','b','c','d','e'];
        // If the request is AJAX, return only the form partial so it can be loaded into a modal
        if (request()->ajax()) {
            return view('soal._form', compact('soal', 'mapels','huruf_opsi', 'source'));
        }

        return view('soal.edit', compact('soal', 'mapels','huruf_opsi', 'source'));
    }

    // Memperbarui soal yang sudah ada
    public function update(Request $request, $id)
    {
        $request->validate([
            'id_mapel' => 'required',
            // 'id_guru' => 'required',
            'soal' => 'required',
            'jawaban' => 'required',
            'gambar_soal' => 'nullable|file|max:5120',
            'jenis' => 'nullable|string|in:teks,listening',
        ]);

        $soal = Soal::findOrFail($id);
    $data = $request->except('gambar_soal');
    $data['sumber'] = $request->input('sumber', $request->get('source', 'master'));

        // Hapus lampiran jika diminta (sebelum upload baru)
        if ($request->boolean('hapus_file') && $soal->file) {
            foreach (['upload/gambar_soal/', 'upload/audio_soal/', 'image/gambar_soal/'] as $dir) {
                $p = public_path($dir . $soal->file);
                if (file_exists($p)) {
                    unlink($p);
                }
            }
            $data['file'] = null;
            $data['tipe_file'] = null;
        }
        unset($data['hapus_file']);

        // Upload file (gambar atau audio) jika ada perubahan
        if ($request->hasFile('gambar_soal')) {
            $file = $request->file('gambar_soal');
            $ext = $file->getClientOriginalExtension();
            $mime = $file->getClientMimeType();

            // Hapus file lama jika ada
            if ($soal->file) {
                // cek lokasi gambar
                $oldImage = public_path('upload/gambar_soal/' . $soal->file);
                $oldAudio = public_path('upload/audio_soal/' . $soal->file);
                if (file_exists($oldImage)) unlink($oldImage);
                if (file_exists($oldAudio)) unlink($oldAudio);
            }

            if (($data['jenis'] ?? '') === 'listening') {
                $filename = 'audio_soal_' . $soal->id . '.' . $ext;
                $dest = public_path('upload/audio_soal');
                File::ensureDirectoryExists($dest);
                $file->move($dest, $filename);
                $data['file'] = $filename;
                $data['tipe_file'] = $mime;
            } else {
                $filename = time() . '_' . $file->getClientOriginalName();
                $dest = public_path('upload/gambar_soal');
                File::ensureDirectoryExists($dest);
                $file->move($dest, $filename);
                $data['file'] = $filename;
                $data['tipe_file'] = $mime;
            }
        }

        $soal->update($data);

    \Illuminate\Support\Facades\Cache::forget('soal_bermasalah_count');
    return redirect()->route('soal.index', ['source' => $data['sumber'] ?? 'master'])->with('success', 'Soal berhasil diperbarui.');
    }

    // Menghapus soal
    public function destroy($id)
    {
        $soal = Soal::findOrFail($id);

        // Hapus gambar jika ada
        if ($soal->file && file_exists(public_path('upload/gambar_soal/' . $soal->file))) {
            unlink(public_path('upload/gambar_soal/' . $soal->file));
        }

        $soal->delete();

        return redirect()->route('soal.index')->with('success', 'Soal berhasil dihapus.');
    }

    /**
     * Bulk delete selected soal (expects array of ids in selected[])
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('selected', []);
        if (!is_array($ids) || count($ids) === 0) {
            return back()->with('error', 'Tidak ada soal terpilih.');
        }

        $soals = Soal::whereIn('id', $ids)->get();
        foreach ($soals as $soal) {
            if ($soal->file) {
                $img = public_path('upload/gambar_soal/' . $soal->file);
                $aud = public_path('upload/audio_soal/' . $soal->file);
                if (file_exists($img)) @unlink($img);
                if (file_exists($aud)) @unlink($aud);
            }
            $soal->delete();
        }

        return back()->with('success', count($soals) . ' soal berhasil dihapus.');
    }

    /**
     * Bulk move selected soal to another mapel (expects selected[] and target_mapel)
     */
    public function bulkMove(Request $request)
    {
        $ids = $request->input('selected', []);
        $target = $request->input('target_mapel');

        if (!is_array($ids) || count($ids) === 0) {
            return back()->with('error', 'Tidak ada soal terpilih.');
        }
        if (!$target || !is_numeric($target)) {
            return back()->with('error', 'Pilih mapel tujuan terlebih dahulu.');
        }

        $updated = Soal::whereIn('id', $ids)->update(['id_mapel' => (int)$target]);
        return back()->with('success', "$updated soal berhasil dipindahkan ke mapel.");
    }

    /**
     * Bulk duplicate selected soal to another mapel
     */
    public function bulkDuplicate(Request $request)
    {
        $ids = $request->input('selected', []);
        $target = $request->input('target_mapel');

        if (!is_array($ids) || count($ids) === 0) {
            return back()->with('error', 'Tidak ada soal terpilih.');
        }
        if (!$target || !is_numeric($target)) {
            return back()->with('error', 'Pilih mapel tujuan terlebih dahulu.');
        }

        $count = 0;
        $soals = Soal::whereIn('id', $ids)->get();

        foreach ($soals as $soal) {
            $newSoal = $soal->replicate();
            $newSoal->id_mapel = (int)$target;
            $newSoal->tgl_input = now()->format('Y-m-d H:i:s');
            $newSoal->save();

            // Handle file duplication (image/audio) agar tidak hilang jika soal asli dihapus
            if ($soal->file) {
                $extension = pathinfo($soal->file, PATHINFO_EXTENSION);
                $isListening = ($soal->jenis === 'listening');
                $folder = $isListening ? 'audio_soal' : 'gambar_soal';
                $newFilename = $isListening ? 'audio_soal_' . $newSoal->id . '.' . $extension : time() . '_' . $newSoal->id . '_' . $soal->file;

                if (File::exists(public_path("upload/$folder/" . $soal->file))) {
                    File::copy(public_path("upload/$folder/" . $soal->file), public_path("upload/$folder/" . $newFilename));
                    $newSoal->update(['file' => $newFilename]);
                }
            }
            $count++;
        }

        return back()->with('success', "$count soal berhasil diduplikasi ke mapel tujuan.");
    }

    /**
     * Show import form for bulk soal import (CSV/TSV or pasted table)
     */
    public function importForm()
    {
        // provide mapel list to help admins find id_mapel if needed
        $mapels = Mapel::all();
        return view('soal.import', compact('mapels'));
    }

    /**
     * Process import file or pasted content.
     * Expected columns: no, soal, opsi a, opsi b, opsi c, opsi d, jawaban, id_mapel
     */
    public function import(Request $request)
    {
        $request->validate([
            // allow csv, txt (tsv) and xlsx/xls
            'file' => 'nullable|file|mimes:csv,txt,xlsx,xls|max:15360',
            'paste' => 'nullable|string'
        ]);

        $content = '';
        $lines = [];
        if ($request->hasFile('file')) {
            $uploaded = $request->file('file');
            $ext = strtolower($uploaded->getClientOriginalExtension());
            if (in_array($ext, ['xlsx','xls'])) {
                // parse spreadsheet using PhpSpreadsheet
                try {
                    $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($uploaded->getRealPath());
                    $sheet = $spreadsheet->getActiveSheet();
                    $rows = $sheet->toArray(null, true, true, true);
                    foreach ($rows as $r) {
                        $vals = array_values($r);
                        $cols = array_map(function($v){ return trim((string)$v); }, $vals);
                        if (count(array_filter($cols, function($v){ return $v !== ''; })) === 0) continue;
                        // join with a special marker (we'll parse via str_getcsv if needed)
                        $lines[] = implode("\t", $cols);
                    }
                } catch (\Throwable $e) {
                    return back()->withErrors(['file' => 'Gagal membaca file spreadsheet: ' . $e->getMessage()]);
                }
            } else {
                $content = file_get_contents($uploaded->getRealPath());
                $lines = preg_split('/\r\n|\r|\n/', trim($content));
            }
        } elseif ($request->filled('paste')) {
            $content = $request->input('paste');
            $lines = preg_split('/\r\n|\r|\n/', trim($content));
        } else {
            return back()->withErrors(['file' => 'Silakan unggah file CSV/TSV atau tempel data pada isian.']);
        }

        if (empty($lines)) return back()->withErrors(['file' => 'File kosong atau tidak dapat dibaca.']);

        // detect delimiter for text lines (tab or comma) using first non-empty line
        $firstLine = '';
        foreach ($lines as $ln) { if (trim($ln) !== '') { $firstLine = $ln; break; } }
        $delimiter = (strpos($firstLine, "\t") !== false) ? "\t" : ",";

        $created = 0;
        $errors = [];

        // header detection
        $firstCols = array_map('trim', str_getcsv($firstLine, $delimiter));
        $headersLower = array_map('strtolower', $firstCols);
        $hasHeader = (in_array('soal', $headersLower) || in_array('type', $headersLower) || in_array('jawaban', $headersLower));

        $groupMap = []; // map group_key => ['passage_id'=>..., 'id_mapel'=>...]

        foreach ($lines as $i => $line) {
            if (trim($line) === '') continue;
            $cols = array_map('trim', str_getcsv($line, $delimiter));

            // skip header row
            if ($hasHeader && $i === 0) continue;

            if ($hasHeader) {
                // map by header names
                $rowAssoc = [];
                foreach ($firstCols as $idx => $h) {
                    // Normalisasi: Ubah spasi menjadi underscore agar 'opsi a' menjadi 'opsi_a'
                    $key = str_replace(' ', '_', strtolower(trim($h)));
                    $rowAssoc[$key] = isset($cols[$idx]) ? $cols[$idx] : '';
                }

                $type = strtolower($rowAssoc['type'] ?? 'soal');
                $groupKey = $rowAssoc['group_key'] ?? $rowAssoc['group'] ?? '';
                $soalText = $rowAssoc['soal'] ?? '';
                $jawaban = $rowAssoc['jawaban'] ?? '';
                $idMapel = isset($rowAssoc['id_mapel']) && is_numeric($rowAssoc['id_mapel']) ? (int)$rowAssoc['id_mapel'] : null;
                $opsiA = $rowAssoc['opsi_a'] ?? ($rowAssoc['a'] ?? '');
                $opsiB = $rowAssoc['opsi_b'] ?? ($rowAssoc['b'] ?? '');
                $opsiC = $rowAssoc['opsi_c'] ?? ($rowAssoc['c'] ?? '');
                $opsiD = $rowAssoc['opsi_d'] ?? ($rowAssoc['d'] ?? '');
                $opsiE = $rowAssoc['opsi_e'] ?? ($rowAssoc['e'] ?? '');

                if ($type === 'passage') {
                    // must have soalText and idMapel
                    if (!$soalText || !$idMapel) {
                        $errors[] = "Baris " . ($i+1) . ": passage harus memiliki kolom soal dan id_mapel";
                        continue;
                    }
                    try {
                        $now = now()->format('Y-m-d H:i:s');
                        $data = [
                            'id_mapel' => $idMapel,
                            'soal' => $soalText,
                            'opsi_a' => '',
                            'opsi_b' => '',
                            'opsi_c' => '',
                            'opsi_d' => '',
                            'opsi_e' => '',
                            'jawaban' => '',
                            'sumber' => 'import',
                            'jenis' => 'passage',
                            'tgl_input' => $now,
                        ];
                        $passage = Soal::create($data);
                        if ($groupKey) {
                            $groupMap[$groupKey] = ['passage_id' => $passage->id, 'id_mapel' => $idMapel];
                        }
                        $created++;
                    } catch (\Exception $ex) {
                        $errors[] = "Baris " . ($i+1) . ": gagal menyimpan passage (" . $ex->getMessage() . ")";
                    }
                } else {
                    // soal row
                    // determine id_mapel: prefer explicit, else from groupMap
                    if (!$idMapel && $groupKey && isset($groupMap[$groupKey])) {
                        $idMapel = $groupMap[$groupKey]['id_mapel'];
                    }
                    if (!$soalText || !$jawaban || !$idMapel) {
                        $errors[] = "Baris " . ($i+1) . ": soal/jawaban/id_mapel tidak valid atau tidak ditemukan (pastikan group mapping benar)";
                        continue;
                    }

                    try {
                        $now = now()->format('Y-m-d H:i:s');
                        $data = [
                            'id_mapel' => $idMapel,
                            'soal' => $soalText,
                            'opsi_a' => $opsiA,
                            'opsi_b' => $opsiB,
                            'opsi_c' => $opsiC,
                            'opsi_d' => $opsiD,
                            'opsi_e' => $opsiE ?? '',
                            'jawaban' => strtoupper(trim($jawaban)),
                            'sumber' => 'import',
                            'tgl_input' => $now,
                        ];
                        Soal::create($data);
                        $created++;
                    } catch (\Exception $ex) {
                        $errors[] = "Baris " . ($i+1) . ": gagal menyimpan (" . $ex->getMessage() . ")";
                    }
                }
            } else {
                // fallback: old positional parser (no headers)
                $colsCount = count($cols);
                // expected at least 8 columns (0..7)
                if ($colsCount < 4) {
                    if ($i === 0 && preg_match('/no|soal|opsi/i', strtolower($line))) continue;
                    $errors[] = "Baris " . ($i+1) . ": kolom kurang";
                    continue;
                }
                $soalText = $cols[1] ?? '';
                $jawaban = $cols[$colsCount - 2] ?? '';
                $idMapel = isset($cols[$colsCount - 1]) && is_numeric($cols[$colsCount - 1]) ? (int)$cols[$colsCount - 1] : null;
                $opsiCols = array_slice($cols, 2, max(0, $colsCount - 3));
                $opsiA = $opsiCols[0] ?? '';
                $opsiB = $opsiCols[1] ?? '';
                $opsiC = $opsiCols[2] ?? '';
                $opsiD = $opsiCols[3] ?? '';
                $opsiE = $opsiCols[4] ?? '';

                if (!$soalText || !$jawaban || !$idMapel) {
                    $errors[] = "Baris " . ($i+1) . ": soal/jawaban/id_mapel tidak valid (pastikan kolom jawaban berada di kolom kedua dari kanan, dan id_mapel di kolom terakhir)";
                    continue;
                }

                try {
                    $now = now()->format('Y-m-d H:i:s');
                    $data = [
                        'id_mapel' => $idMapel,
                        'soal' => $soalText,
                        'opsi_a' => $opsiA,
                        'opsi_b' => $opsiB,
                        'opsi_c' => $opsiC,
                        'opsi_d' => $opsiD,
                        'opsi_e' => $opsiE ?? '',
                        'jawaban' => strtoupper(trim($jawaban)),
                        'sumber' => 'import',
                        'tgl_input' => $now,
                    ];
                    Soal::create($data);
                    $created++;
                } catch (\Exception $ex) {
                    $errors[] = "Baris " . ($i+1) . ": gagal menyimpan (" . $ex->getMessage() . ")";
                }
            }
        }

        $msg = "Berhasil mengimpor $created soal.";
        return back()->with('success', $msg)->with('import_errors', $errors);
    }
}
