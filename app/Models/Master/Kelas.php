<?php

namespace App\Models\Master;

use App\Models\Semester;
use App\Models\TahunPelajaran;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'tahun_pelajaran_id',
        'semester_id',
        'nama_kelas',
        'kode_kelas',
        'jurusan_id',
        'level_id',
        'guru_id',
        'set_siswa',
    ];

    protected $casts = [
        'tahun_pelajaran_id' => 'integer',
        'semester_id' => 'integer',
        'jurusan_id' => 'integer',
        'level_id' => 'integer',
        'guru_id' => 'integer',
    ];

    public function tahunPelajaran(): BelongsTo
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function levelKelas(): BelongsTo
    {
        return $this->belongsTo(LevelKelas::class, 'level_id');
    }

    public function jurusan(): BelongsTo
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }

    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function siswa(): BelongsToMany
    {
        return $this->belongsToMany(Siswa::class, 'kelas_siswa', 'kelas_id', 'siswa_id')
            ->withPivot('tahun_pelajaran_id', 'semester_id')
            ->withTimestamps();
    }

    public function kelasSiswa()
    {
        return $this->hasMany(KelasSiswa::class, 'kelas_id');
    }
}
