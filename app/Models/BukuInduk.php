<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BukuInduk extends Model
{
    protected $table = 'buku_induk';
    protected $primaryKey = 'siswa_id';
    public $incrementing = false;

    protected $fillable = [
        'siswa_id',
        'uid',
        'nama_panggilan',
        'bahasa',
        'jml_saudara_kandung',
        'jml_saudara_tiri',
        'jml_saudara_angkat',
        'yatim',
        'tinggal_bersama',
        'jarak',
        'gol_darah',
        'penyakit',
        'kelainan_fisik',
        'kegemaran',
        'beasiswa',
        'no_ijazah_sebelumnya',
        'tahun_lulus_sebelumnya',
        'pindahan_dari',
        'alasan_kepindahan',
        'status',
        'tahun_lulus',
        'no_ijazah',
        'kelas_akhir',
        'catatan_penting',
    ];
}
