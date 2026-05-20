<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Standard extends Model
{
    protected $fillable = [
        'nutrisi', 'minimum', 'maximum',
        'rekomendasi_harian', 'fungsi_zat',
        'dampak_kelebihan', 'dampak_kekurangan',
    ];
}
