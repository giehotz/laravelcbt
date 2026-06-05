<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Master\TahunPelajaran;
use App\Models\Master\Semester;
use App\Models\Master\Kelas;
use App\Models\Master\Mapel;

class RaporKkm extends Model
{
    use HasFactory;

    protected $table = 'rapor_kkm';

    protected $fillable = [
        'tp_id',
        'smt_id',
        'kelas_id',
        'mapel_id',
        'kkm',
        'bobot_ph',
        'bobot_pts',
        'bobot_pas',
    ];

    public function tp()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tp_id');
    }

    public function smt()
    {
        return $this->belongsTo(Semester::class, 'smt_id');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }
}
