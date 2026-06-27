<?php

namespace App\Models\Cbt;

use App\Models\Master\Siswa;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Nilai extends Model
{
    protected $table = 'cbt_nilai';

    protected $fillable = [
        'siswa_id',
        'jadwal_id',
        'pg_benar',
        'pg_nilai',
        'esai_nilai',
        'kompleks_nilai',
        'jodohkan_nilai',
        'isian_nilai',
    ];

    protected $casts = [
        'dikoreksi' => 'boolean',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id');
    }
}
