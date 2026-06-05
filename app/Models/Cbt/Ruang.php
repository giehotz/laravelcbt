<?php

namespace App\Models\Cbt;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ruang extends Model
{
    use HasFactory;

    protected $table = 'cbt_ruang';

    protected $fillable = [
        'nama_ruang',
        'kode_ruang',
    ];
}
