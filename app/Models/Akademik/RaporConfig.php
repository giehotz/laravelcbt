<?php

namespace App\Models\Akademik;

use App\Models\Cbt\Jenis;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return $this->belongsTo(Jenis::class, 'jenis_ph_id');
    }

    public function jenisPts()
    {
        return $this->belongsTo(Jenis::class, 'jenis_pts_id');
    }

    public function jenisPas()
    {
        return $this->belongsTo(Jenis::class, 'jenis_pas_id');
    }
}
