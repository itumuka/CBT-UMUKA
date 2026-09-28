<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CbtSession extends Model
{
    protected $table = 'cbt_sessions';
    protected $fillable = ['key','name','active','token_status'];

    // Helper accessor for readable token status
    public function getTokenStatusLabelAttribute()
    {
        $v = $this->token_status ?? 0;
        return $v === 0 ? 'Off' : ($v === 1 ? 'Real' : 'Tester');
    }
}
