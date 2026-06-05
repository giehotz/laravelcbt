<?php

namespace App\Models\Cbt;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KelasRuang extends Model
{
    use HasFactory;

    protected $table = 'cbt_kelas_ruang';

    protected $fillable = [
        'kelas_id',
        'ruang_id',
        'sesi_id',
        'tp_id',
        'smt_id',
    ];

    public function kelas()
    {
        return $this->belongsTo(\App\Models\Master\Kelas::class, 'kelas_id');
    }

    public function ruang()
    {
        return $this->belongsTo(Ruang::class, 'ruang_id');
    }

    public function sesi()
    {
        return $this->belongsTo(Sesi::class, 'sesi_id');
    }
}
