<?php

namespace App\Models\Cbt;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NomorPeserta extends Model
{
    use HasFactory;

    protected $table = 'cbt_nomor_peserta';

    protected $fillable = [
        'siswa_id',
        'tp_id',
        'smt_id',
        'nomor_peserta',
    ];

    public function siswa()
    {
        return $this->belongsTo(\App\Models\Master\Siswa::class, 'siswa_id');
    }

    public function tahunPelajaran()
    {
        return $this->belongsTo(\App\Models\TahunPelajaran::class, 'tp_id');
    }

    public function semester()
    {
        return $this->belongsTo(\App\Models\Semester::class, 'smt_id');
    }
}
