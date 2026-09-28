<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Soal extends Model
{
    use HasFactory;

    protected $table = 'm_soal'; // Nama tabel di database
    protected $fillable = ['id_mapel','bobot','file','tipe_file','soal','opsi_a','opsi_b','opsi_c','opsi_d','opsi_e',
    'jawaban','tgl_input','jml_benar','jml_salah','jenis','sumber', 'updated_at', 'created_at']; 

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'id_mapel');
    }
}

