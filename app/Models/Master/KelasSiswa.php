<?php

namespace App\Models\Master;

use App\Models\Semester;
use App\Models\TahunPelajaran;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class KelasSiswa extends Pivot
{
    protected $table = 'kelas_siswa';

    protected $fillable = [
        'tahun_pelajaran_id',
        'semester_id',
        'siswa_id',
        'kelas_id',
    ];

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_id');
    }
}
