<?php

namespace App\Models\Lms;

use App\Models\Master\Guru;
use App\Models\Master\Mapel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Materi extends Model
{
    use HasFactory;

    protected $table = 'materi';

    protected $fillable = [
        'guru_id',
        'mapel_id',
        'judul',
        'deskripsi',
        'kelas',
        'file',
        'youtube',
    ];

    protected $casts = [
        'kelas' => 'array',
        'file' => 'array',
    ];

    public function guru()
    {
        return $this->belongsTo(Guru::class);
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class);
    }
}
