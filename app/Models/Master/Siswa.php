<?php

namespace App\Models\Master;

use App\Models\Cbt\DurasiSiswa;
use App\Models\Cbt\SesiSiswa;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';

    protected $fillable = [
        'user_id',
        'nisn',
        'nis',
        'nama',
        'jenis_kelamin',
        'tahun_masuk',
        'sekolah_asal',
        'tempat_lahir',
        'tanggal_lahir',
        'agama',
        'hp',
        'email',
        'foto',
        'anak_ke',
        'status_keluarga',
        'alamat',
        'rt',
        'rw',
        'kelurahan',
        'kecamatan',
        'kabupaten',
        'provinsi',
        'kode_pos',
        'nama_ayah',
        'tgl_lahir_ayah',
        'pendidikan_ayah',
        'pekerjaan_ayah',
        'nohp_ayah',
        'alamat_ayah',
        'nama_ibu',
        'tgl_lahir_ibu',
        'pendidikan_ibu',
        'pekerjaan_ibu',
        'nohp_ibu',
        'alamat_ibu',
        'nama_wali',
        'tgl_lahir_wali',
        'pendidikan_wali',
        'pekerjaan_wali',
        'nohp_wali',
        'alamat_wali',
        'nik',
        'warga_negara',
    ];

    /**
     * Get the user that owns the Siswa profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kelasSiswa()
    {
        return $this->hasMany(KelasSiswa::class, 'siswa_id');
    }

    public function durasi()
    {
        return $this->hasMany(DurasiSiswa::class, 'siswa_id');
    }

    public function bukuInduk()
    {
        return $this->hasOne(BukuInduk::class, 'siswa_id');
    }

    public function kelasAktif()
    {
        return $this->hasOne(KelasSiswa::class, 'siswa_id')
            ->where('tahun_pelajaran_id', function ($query) {
                $query->select('id')->from('tahun_pelajaran')->where('active', true)->limit(1);
            })
            ->where('semester_id', function ($query) {
                $query->select('id')->from('semesters')->where('active', true)->limit(1);
            });
    }

    public function sesiSiswa()
    {
        return $this->hasMany(SesiSiswa::class, 'siswa_id');
    }
}
