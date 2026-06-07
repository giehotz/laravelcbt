<?php

namespace App\Models\Cbt;

use App\Models\Master\Guru;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Jadwal extends Model
{
    use HasFactory;

    protected $table = 'cbt_jadwal';

    protected $fillable = [
        'tahun_pelajaran_id',
        'semester_id',
        'bank_id',
        'jenis_id',
        'tgl_mulai',
        'tgl_selesai',
        'durasi_ujian',
        'pengawas',
        'acak_soal',
        'acak_opsi',
        'hasil_tampil',
        'token',
        'status',
        'ulang',
        'reset_login',
        'rekap',
        'jam_ke',
        'jarak',
    ];

    protected $casts = [
        'pengawas' => 'array',
        'acak_soal' => 'boolean',
        'acak_opsi' => 'boolean',
        'hasil_tampil' => 'boolean',
        'token' => 'boolean',
        'ulang' => 'boolean',
        'reset_login' => 'boolean',
        'rekap' => 'boolean',
        'status' => 'integer',
        'durasi_ujian' => 'integer',
        'jam_ke' => 'integer',
        'jarak' => 'integer',
    ];

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class);
    }

    public function bankSoal(): BelongsTo
    {
        return $this->belongsTo(BankSoal::class, 'bank_id');
    }

    public function durasiSiswa()
    {
        return $this->hasMany(DurasiSiswa::class, 'jadwal_id');
    }

    public function guruPengawas()
    {
        return $this->hasMany(Pengawas::class, 'jadwal_id');
    }

    public function jenis(): BelongsTo
    {
        return $this->belongsTo(Jenis::class, 'jenis_id');
    }

    // Accessor to get actual pengawas models if needed
    public function getPengawasModelsAttribute()
    {
        if (empty($this->pengawas)) {
            return [];
        }

        return Guru::whereIn('id', $this->pengawas)->get();
    }

    public function scopeAktifSekarang($query)
    {
        $now = now()->toDateTimeString();

        return $query->where('status', 1)
            ->where('tgl_mulai', '<=', $now)
            ->where('tgl_selesai', '>=', $now);
    }
}
