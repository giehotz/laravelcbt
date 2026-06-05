<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Semester;
use App\Models\TahunPelajaran;

class GuruMapelKelas extends Model
{
    protected $table = 'guru_mapel_kelas';
    protected $guarded = ['id'];

    public function guru(): BelongsTo
    {
        return $this->belongsTo(Guru::class);
    }

    public function mapel(): BelongsTo
    {
        return $this->belongsTo(Mapel::class);
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function scopeCurrentPeriod($query, $tpId = null, $smtId = null)
    {
        if (!$tpId || !$smtId) {
            $tpId = TahunPelajaran::where('active', true)->first()?->id;
            $smtId = Semester::where('active', true)->first()?->id;
        }

        return $query->where('tahun_pelajaran_id', $tpId)
                     ->where('semester_id', $smtId);
    }

    public function scopeForGuru($query, $guruId)
    {
        return $query->where('guru_id', $guruId);
    }

    public function scopeForMapel($query, $mapelId)
    {
        return $query->where('mapel_id', $mapelId);
    }
}
