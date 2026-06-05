<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CbtSoalSiswa extends Model
{
    use HasUlids;

    protected $table = 'cbt_soal_siswa';

    protected $guarded = ['id'];

    protected $casts = [
        'soal_end' => 'boolean',
        'nilai_otomatis' => 'boolean',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(CbtJadwal::class);
    }

    public function bankSoal(): BelongsTo
    {
        return $this->belongsTo(CbtBankSoal::class, 'bank_id');
    }

    public function soal(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Cbt\Soal::class, 'soal_id');
    }
}
