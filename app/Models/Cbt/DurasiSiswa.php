<?php

namespace App\Models\Cbt;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DurasiSiswa extends Model
{
    use HasFactory;

    protected $table = 'cbt_durasi_siswa';

    protected $fillable = [
        'siswa_id',
        'jadwal_id',
        'status',
        'lama_ujian',
        'mulai',
        'selesai',
        'reset',
    ];

    public function siswa()
    {
        return $this->belongsTo(\App\Models\Master\Siswa::class, 'siswa_id');
    }

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id');
    }
}
