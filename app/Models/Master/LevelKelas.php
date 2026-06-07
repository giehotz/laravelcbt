<?php

namespace App\Models\Master;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LevelKelas extends Model
{
    use HasFactory;

    protected $table = 'level_kelas';

    protected $fillable = [
        'level',
    ];

    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class, 'level_id');
    }
}
