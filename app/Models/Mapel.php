<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mapel extends Model
{
    use HasFactory;

    protected $table = 'm_mapel'; // Nama tabel di database
    protected $fillable = ['nama','id','kategori']; 
}
