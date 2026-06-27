<?php

namespace App\Http\Controllers\Cbt;

use App\Http\Controllers\Controller;
use App\Models\Cbt\Jadwal;
use App\Models\Cbt\Nilai;
use Inertia\Inertia;

class HasilUjianController extends Controller
{
    /**
     * Tampilkan daftar ujian (Jadwal) yang sudah atau sedang berjalan
     */
    public function index()
    {
        // Ambil semua jadwal yang ada, mungkin difilter berdasarkan tahun ajaran / semester aktif
        $jadwals = Jadwal::with(['bankSoal', 'jenis', 'guruPengawas'])
            ->orderBy('tgl_mulai', 'desc')
            ->paginate(15);

        return Inertia::render('Cbt/HasilUjian/Index', [
            'jadwals' => $jadwals,
        ]);
    }

    /**
     * Tampilkan detail hasil (nilai) siswa untuk ujian tertentu
     */
    public function show(Jadwal $jadwal)
    {
        $jadwal->load(['bankSoal', 'jenis']);

        $nilais = Nilai::with([
            'siswa.sesiSiswa.sesi',
            'siswa.sesiSiswa.ruang',
            'siswa.durasi' => function ($query) use ($jadwal) {
                $query->where('jadwal_id', $jadwal->id);
            }
        ])
            ->where('jadwal_id', $jadwal->id)
            ->get()
            ->map(function ($n) {
                // Kalkulasi total nilai
                $n->total_nilai = $n->pg_nilai + $n->kompleks_nilai + $n->jodohkan_nilai + $n->isian_nilai + $n->esai_nilai;
                return $n;
            });

        return Inertia::render('Cbt/HasilUjian/Show', [
            'jadwal' => $jadwal,
            'nilais' => $nilais,
        ]);
    }
}
