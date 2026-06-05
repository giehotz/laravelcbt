<?php

namespace App\Models\Cbt;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Jenis extends Model
{
    use HasFactory;

    protected $table = 'cbt_jenis';

    protected $fillable = [
        'nama_jenis',
        'kode_jenis',
    ];
}
