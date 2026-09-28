<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class BackupData extends Command
{
    protected $signature = 'backup:data';
    protected $description = 'Backup selected tables to storage/app/backups as JSON files';

    public function handle()
    {
        $tables = ['m_siswa', 'm_siswa_tryout', 'm_admin', 'm_mapel'];
        $timestamp = date('Ymd_His');
        $dir = "backups/{$timestamp}";

        foreach ($tables as $table) {
            try {
                $rows = DB::table($table)->get();
                $json = $rows->toJson(JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                $filename = "{$dir}/{$table}.json";
                Storage::disk('local')->put($filename, $json);
                $this->info("Backed up {$table} -> {$filename}");
            } catch (\Exception $e) {
                $this->error("Failed to backup {$table}: " . $e->getMessage());
            }
        }

        $this->info('Backup completed. Files are in storage/app/' . $dir);
        return 0;
    }
}
