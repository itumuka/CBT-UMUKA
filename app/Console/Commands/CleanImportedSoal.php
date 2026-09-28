<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanImportedSoal extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'soal:clean-imported';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup m_soal then sanitize imported text (_x000D_ and CRLF) for soal and opsi_* fields';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting cleanup of imported soal...');

        // create a backup table first
        $ts = date('Ymd_His');
        $backupTable = "m_soal_backup_{$ts}";

        $this->info("Creating backup table: {$backupTable}");
        DB::statement("CREATE TABLE IF NOT EXISTS `{$backupTable}` LIKE `m_soal`");
        DB::statement("INSERT INTO `{$backupTable}` SELECT * FROM `m_soal`");

        $this->info('Backup completed. Starting sanitization pass...');

        $total = 0;
        $updated = 0;

        DB::table('m_soal')->orderBy('id')->chunkById(200, function ($rows) use (&$total, &$updated) {
            foreach ($rows as $row) {
                $total++;

                $fields = ['soal', 'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e'];
                $updates = [];

                foreach ($fields as $f) {
                    if (! property_exists($row, $f)) {
                        continue;
                    }
                    $orig = $row->{$f};
                    $san = $this->sanitizeImportedText($orig);
                    // if sanitized differs and not null, update (allow empty string)
                    if ($san !== $orig) {
                        $updates[$f] = $san;
                    }
                }

                if (! empty($updates)) {
                    DB::table('m_soal')->where('id', $row->id)->update($updates);
                    $updated++;
                }
            }
        });

        $this->info("Sanitization finished. Processed: {$total} rows. Updated: {$updated} rows.");
        $this->info("A backup of the table was stored as: {$backupTable}");

        return 0;
    }

    /**
     * Sanitization logic mirroring import-time helper.
     * - remove literal _x000D_
     * - normalize CRLF to \n
     * - if contains HTML tags: convert lone newlines to <br>
     * - if plain text: split paragraphs on double-newline, wrap in <p> and convert single-newline to <br>
     */
    protected function sanitizeImportedText($text)
    {
        if ($text === null) {
            return null;
        }

        // ensure string
        $s = (string) $text;

        // replace literal _x000D_ (case-insensitive)
        $s = preg_replace('/_x000D_/i', '', $s);

        // normalize CRLF and CR
        $s = str_replace(["\r\n", "\r"], "\n", $s);

        // trim
        $s = trim($s);

        if ($s === '') {
            return $s; // keep empty string
        }

        // if it already contains HTML tags, keep tags and convert newlines to <br>
        if (strlen(strip_tags($s)) !== strlen($s)) {
            // contains some HTML tags
            $s = preg_replace('/\n/', '<br>', $s);
            return $s;
        }

        // plain text: split paragraphs on two or more newlines
        $paras = preg_split('/\n{2,}/', $s);
        $outParas = [];
        foreach ($paras as $p) {
            $p = trim($p);
            if ($p === '') {
                continue;
            }
            // convert single newlines in paragraph to <br>
            $p = e($p);
            $p = preg_replace('/\n+/', '<br>', $p);
            $outParas[] = '<p>' . $p . '</p>';
        }

        return implode("\n", $outParas);
    }
}
