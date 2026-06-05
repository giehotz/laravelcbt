<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtNilai extends Model
{
    protected $table = 'cbt_nilai';

    protected $guarded = ['id'];

    protected $casts = [
        'dikoreksi' => 'boolean',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(CbtJadwal::class);
    }
}
