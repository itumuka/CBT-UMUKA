<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GuruTes extends Model
{
    use HasFactory;
    protected $table = 'tr_guru_tes';
    protected $fillable = [
        'id_guru',
        'nama_ujian',
        'id_mapel',
        'id_mapels',
        'jumlah_soal',
        'waktu',
        
        'allow_submit_after',
        'sumber',
        'jenis',
        'mode',
        'detil_jenis',
        'tgl_mulai',
        'terlambat',
        'nilai_minimal',
        'token'
    ];

    /**
     * Default attribute values to avoid NULL inserts.
     */
    protected $attributes = [
        'sumber' => 'master',
        'jenis' => 'acak',
        'mode' => 'cbt',
        'detil_jenis' => '',
        'nilai_minimal' => 0,
        'token' => '',
    ];

    /**
     * Cast common numeric fields.
     */
    protected $casts = [
        'nilai_minimal' => 'integer',
        'jumlah_soal' => 'integer',
        'waktu' => 'integer',
        'allow_submit_after' => 'integer',
    ];

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'id_mapel');
    }

    /**
     * Return id_mapels as array (fallback to id_mapel for legacy rows)
     */
    public function getMapelIdsAttribute()
    {
        if (!empty($this->id_mapels)) {
            $ids = array_filter(array_map('intval', array_map('trim', explode(',', $this->id_mapels))));
            return array_values($ids);
        }
        if (!empty($this->id_mapel)) {
            return [(int)$this->id_mapel];
        }
        return [];
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'id_guru');
    }

    public function ikut_ujian()
    {
        return $this->hasMany(IkutUjian::class, 'id_tes', 'id');
    }

    /**
     * Scope mode CBT vs Tryout (kolom tr_guru_tes.mode).
     */
    public function scopeMode($query, ?string $mode)
    {
        if (in_array($mode, ['cbt', 'tryout'], true)) {
            return $query->where($this->getTable() . '.mode', $mode);
        }

        return $query;
    }

}
