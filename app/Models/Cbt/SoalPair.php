<?php

namespace App\Models\Cbt;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoalPair extends Model
{
    protected $table = 'cbt_soal_pairs';

    protected $fillable = [
        'soal_id', 'kiri', 'kanan',
    ];

    /**
     * Relasi ke Soal
     */
    public function soal(): BelongsTo
    {
        return $this->belongsTo(Soal::class, 'soal_id');
    }
}
