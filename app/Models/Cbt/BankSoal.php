<?php

namespace App\Models\Cbt;

use App\Models\Master\Guru;
use App\Models\Master\Jurusan;
use App\Models\Master\Mapel;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BankSoal extends Model
{
    use HasFactory;

    protected $table = 'cbt_bank_soal';

    protected $fillable = [
        'jenis_id', 'kode', 'level', 'kelas', 'mapel_id', 'jurusan_id', 'guru_id',
        'tahun_pelajaran_id', 'semester_id', 'nama', 'kkm',
        'jml_pg', 'tampil_pg', 'bobot_pg',
        'jml_esai', 'tampil_esai', 'bobot_esai',
        'jml_kompleks', 'tampil_kompleks', 'bobot_kompleks', 'skoring_kompleks',
        'jml_jodohkan', 'tampil_jodohkan', 'bobot_jodohkan',
        'jml_isian', 'tampil_isian', 'bobot_isian',
        'opsi', 'deskripsi', 'status', 'status_soal', 'soal_agama',
    ];

    protected $casts = [
        'kelas' => 'array',
    ];

    public bool $skipSyncJumlah = false;

    public function jenis()
    {
        return $this->belongsTo(Jenis::class, 'jenis_id');
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'mapel_id');
    }

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id');
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class, 'tahun_pelajaran_id');
    }

    public function semester()
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function soals()
    {
        return $this->hasMany(Soal::class, 'bank_id');
    }

    /**
     * Scope untuk mengisolasi akses guru berdasarkan assignment.
     */
    public function scopeVisibleBy(Builder $query, User $user): Builder
    {
        if ($user->hasRole('guru') && $user->guru) {
            return $query->where('guru_id', $user->guru->id);
        }

        return $query;
    }

    /**
     * Menghitung ulang jumlah soal yang ada dan meng-update kolom jml_*
     */
    public function syncJumlahSoal(): void
    {
        $counts = DB::table('cbt_soal')
            ->where('bank_id', $this->id)
            ->select('jenis', DB::raw('count(*) as total'))
            ->groupBy('jenis')
            ->pluck('total', 'jenis')
            ->toArray();

        // jenis: 1=PG, 2=Ganda Kompleks, 3=Menjodohkan, 4=Isian Singkat, 5=Uraian/Esai
        $this->update([
            'jml_pg' => $counts[1] ?? 0,
            'jml_kompleks' => $counts[2] ?? 0,
            'jml_jodohkan' => $counts[3] ?? 0,
            'jml_isian' => $counts[4] ?? 0,
            'jml_esai' => $counts[5] ?? 0,
        ]);
    }
}
