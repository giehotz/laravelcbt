<?php

namespace App\Models\Cbt;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\Master\Mapel;

class Soal extends Model
{
    use HasFactory;

    protected $table = 'cbt_soal';

    protected $fillable = [
        'bank_id', 'mapel_id', 'jenis', 'nomor_soal',
        'file', 'tipe_file', 'soal',
        'opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'opsi_e',
        'file_a', 'file_b', 'file_c', 'file_d', 'file_e',
        'jawaban', 'deskripsi', 'kesulitan', 'timer', 'timer_menit', 'tampilkan'
    ];

    public function bank()
    {
        return $this->belongsTo(BankSoal::class, 'bank_id');
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    public function pairs()
    {
        return $this->hasMany(SoalPair::class, 'soal_id');
    }

    public function getJawabanAttribute($value)
    {
        if ($value === null || $value === '') {
            return $value;
        }

        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && (is_array($decoded) || is_object($decoded))) {
            return $decoded;
        }

        return $value;
    }

    public function setJawabanAttribute($value)
    {
        if (is_array($value) || is_object($value)) {
            $this->attributes['jawaban'] = json_encode($value);
        } else {
            $this->attributes['jawaban'] = $value;
        }
    }

    protected static function booted()
    {
        static::saved(function (Soal $soal) {
            if (!$soal->bank->skipSyncJumlah) {
                $soal->bank->syncJumlahSoal();
            }
        });

        static::deleted(function (Soal $soal) {
            if (!$soal->bank->skipSyncJumlah) {
                $soal->bank->syncJumlahSoal();
            }
        });
    }
}
