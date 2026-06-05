<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Master\TahunPelajaran;
use App\Models\Master\Semester;
use App\Models\Master\Kelas;
use App\Models\Master\Mapel;
use App\Models\Master\Siswa;

class RaporNilaiAkhir extends Model
{
    use HasFactory;

    protected $table = 'rapor_nilai_akhir';

    protected $fillable = [
        'tp_id',
        'smt_id',
        'mapel_id',
        'kelas_id',
        'siswa_id',
        'nilai_ph',
        'nilai_pts',
        'nilai_pas',
        'nilai_akhir',
        'predikat',
        'sumber_ph',
        'sumber_pts',
        'sumber_pas',
        'final',
    ];

    protected $casts = [
        'final' => 'boolean',
        'nilai_ph' => 'decimal:2',
        'nilai_pts' => 'decimal:2',
        'nilai_pas' => 'decimal:2',
        'nilai_akhir' => 'decimal:2',
    ];

    public function tp()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tp_id');
    }

    public function smt()
    {
        return $this->belongsTo(Semester::class, 'smt_id');
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }
}
