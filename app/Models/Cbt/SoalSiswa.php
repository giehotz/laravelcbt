<?php

namespace App\Models\Cbt;

use App\Models\Master\Siswa;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoalSiswa extends Model
{
    use HasUlids;

    protected $table = 'cbt_soal_siswa';

    protected $fillable = [
        'jawaban_siswa',
        'ragu_ragu',
    ];

    protected $hidden = [
        'jawaban_benar',
        'jawaban_alias',
        'opsi_alias_a',
        'opsi_alias_b',
        'opsi_alias_c',
        'opsi_alias_d',
        'opsi_alias_e',
    ];

    protected $casts = [
        'soal_end' => 'boolean',
        'nilai_otomatis' => 'boolean',
        'ragu_ragu' => 'boolean',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class, 'siswa_id');
    }

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id');
    }

    public function bankSoal(): BelongsTo
    {
        return $this->belongsTo(BankSoal::class, 'bank_id');
    }

    public function soal(): BelongsTo
    {
        return $this->belongsTo(Soal::class, 'soal_id');
    }
}
