<?php

namespace App\Models\Cbt;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class Sesi extends Model
{
    use HasFactory;

    protected $table = 'cbt_sesi';

    protected $fillable = [
        'nama_sesi',
        'kode_sesi',
        'waktu_mulai',
        'waktu_akhir',
        'aktif',
    ];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    /**
     * Scope a query to only include active sessions.
     */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('aktif', true);
    }
}
