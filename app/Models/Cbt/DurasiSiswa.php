<?php

namespace App\Models\Cbt;

use App\Enums\Cbt\DurasiStatus;
use App\Models\Master\Siswa;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DurasiSiswa extends Model
{
    use HasFactory;

    protected $table = 'cbt_durasi_siswa';

    protected $fillable = [
        'siswa_id',
        'jadwal_id',
        'lama_ujian',
        'mulai',
        'selesai',
        'reset',
    ];

    protected $casts = [
        'status' => DurasiStatus::class,
    ];

    public function scopeSedangUjian(Builder $query): Builder
    {
        return $query->where('status', DurasiStatus::SEDANG);
    }

    public function scopeSelesai(Builder $query): Builder
    {
        return $query->where('status', DurasiStatus::SELESAI);
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id');
    }
}
