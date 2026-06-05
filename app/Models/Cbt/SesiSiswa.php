<?php

namespace App\Models\Cbt;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SesiSiswa extends Model
{
    use HasFactory;

    protected $table = 'cbt_sesi_siswa';

    protected $fillable = [
        'siswa_id',
        'kelas_id',
        'ruang_id',
        'sesi_id',
        'tp_id',
        'smt_id',
        'is_manual',
    ];

    protected $casts = [
        'is_manual' => 'boolean',
    ];

    public function siswa()
    {
        return $this->belongsTo(\App\Models\Master\Siswa::class, 'siswa_id');
    }

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
