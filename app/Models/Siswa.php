<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'm_siswa'; // Sesuaikan dengan nama tabel di database
    protected $primaryKey = 'id'; // Pastikan ini sesuai dengan primary key tabel
    public $timestamps = false; // Nonaktifkan jika tabel tidak memiliki created_at & updated_at

    // Kolom yang boleh diisi mass-assignment
    protected $fillable = [
        'nama', 'nim', 'jurusan', 'sekolah', 'no_hp', 'email', 'mapel_wajib', 'mapel_pilihan', 'session_key', 'token', 'status_akun', 'has_participated'
    ];

    public function hasParticipated(): bool
    {
        return (int) ($this->has_participated ?? 0) === 1;
    }

    public function markParticipated(): void
    {
        $this->has_participated = 1;
        $this->save();
    }

    public function clearParticipated(): void
    {
        $this->has_participated = 0;
        $this->save();
    }

    /**
     * Segmen peserta per mode halaman:
     *  - cbt: pendaftar PMB (punya NIM/no_register) + akun non-tryout
     *         (demo/tester tanpa NIM, session non-tryout)
     *  - tryout: registrasi mandiri tryout (session_key tryout%)
     */
    public function scopeSegment($query, ?string $mode)
    {
        if ($mode === 'cbt') {
            return $query->where(function ($q) {
                $q->whereNotNull('nim')->where('nim', '!=', '')
                    ->orWhereNull('session_key')
                    ->orWhere('session_key', 'not like', 'tryout%');
            });
        }
        if ($mode === 'tryout') {
            return $query->where('session_key', 'like', 'tryout%');
        }

        return $query;
    }

    /**
     * Segmen milik satu baris siswa (untuk badge di halaman peserta).
     */
    public function getSegmentAttribute(): string
    {
        if (!empty($this->nim)) {
            return 'cbt';
        }
        if (!empty($this->session_key) && str_starts_with(strtolower($this->session_key), 'tryout')) {
            return 'tryout';
        }

        return 'cbt';
    }

}
