<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Admin extends Authenticatable
{
    use Notifiable;
    use HasFactory;

    protected $table = 'm_admin'; // Sesuaikan dengan nama tabel di database
    protected $primaryKey = 'id'; // Sesuaikan dengan primary key tabel (jika berbeda)
    public $timestamps = false; // Nonaktifkan jika tabel tidak memiliki created_at & updated_at

    protected $fillable = [
        'username',
        'password',
        'level',
        'kon_id',
        'email'
    ];

    protected $hidden = [
        'password',
    ];
    public function adminlte_image()
    {
        // Contoh: return URL gambar avatar user (bisa dari database atau folder public)
        return asset('image/logout.png');
    }
}
