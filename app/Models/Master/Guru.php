<?php

namespace App\Models\Master;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Guru extends Model
{
    use HasFactory;

    protected $table = 'guru';

    protected $fillable = [
        'user_id',
        'nip',
        'nama_guru',
        'email',
        'kode_guru',
        'no_ktp',
        'tempat_lahir',
        'tgl_lahir',
        'jenis_kelamin',
        'agama',
        'no_hp',
        'alamat',
        'nuptk',
        'jenis_ptk',
        'status_pegawai',
        'status_aktif',
        'foto',
    ];

    /**
     * Get the user that owns the Guru profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get jabatan struktural records for this guru.
     */
    public function jabatanGuru(): HasMany
    {
        return $this->hasMany(JabatanGuru::class, 'guru_id');
    }

    /**
     * Get mapel-kelas assignment records for this guru.
     */
    public function guruMapelKelas(): HasMany
    {
        return $this->hasMany(GuruMapelKelas::class, 'guru_id');
    }

    /**
     * Get ekstra pembina records for this guru.
     */
    public function ekstraPembina(): HasMany
    {
        return $this->hasMany(EkstraPembina::class, 'guru_id');
    }
}
