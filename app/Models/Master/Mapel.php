<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Mapel extends Model
{
    protected $table = 'mapel';

    protected $fillable = [
        'nama_mapel',
        'kode',
        'kelompok',
        'bobot_p',
        'bobot_k',
        'jenjang',
        'urutan',
        'urutan_tampil',
        'status',
        'deletable',
    ];

    protected $casts = [
        'status' => 'boolean',
        'deletable' => 'boolean',
        'bobot_p' => 'integer',
        'bobot_k' => 'integer',
        'jenjang' => 'integer',
        'urutan' => 'integer',
        'urutan_tampil' => 'integer',
    ];

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}
