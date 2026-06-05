<?php

namespace App\Models\Akademik;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Master\TahunPelajaran;
use App\Models\Master\Semester;

class RaporConfig extends Model
{
    use HasFactory;

    protected $table = 'rapor_config';

    protected $fillable = [
        'tp_id',
        'smt_id',
        'jenis_ph_id',
        'jenis_pts_id',
        'jenis_pas_id',
    ];

    public function tp()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tp_id');
    }

    public function smt()
    {
        return $this->belongsTo(Semester::class, 'smt_id');
    }

    public function jenisPh()
    {
        return $this->belongsTo(\App\Models\Cbt\CbtJenis::class, 'jenis_ph_id');
    }

    public function jenisPts()
    {
        return $this->belongsTo(\App\Models\Cbt\CbtJenis::class, 'jenis_pts_id');
    }

    public function jenisPas()
    {
        return $this->belongsTo(\App\Models\Cbt\CbtJenis::class, 'jenis_pas_id');
    }
}
