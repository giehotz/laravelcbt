<?php

namespace App\Models\Cbt;

use App\Models\Master\Siswa;
use App\Models\Semester;
use App\Models\TahunPelajaran;
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
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tp_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'smt_id');
    }
}
