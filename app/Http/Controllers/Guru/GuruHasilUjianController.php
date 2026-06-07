<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Cbt\Jadwal;
use App\Models\Cbt\Nilai;
use Inertia\Inertia;

class GuruHasilUjianController extends Controller
{
    public function index()
    {
        $guru = auth()->user()->guru;
        abort_unless($guru, 403);

        $jadwals = Jadwal::with('bankSoal')
            ->whereHas('bankSoal', fn ($q) => $q->where('guru_id', $guru->id))
            ->where('tgl_selesai', '<=', now())
            ->orderBy('tgl_mulai', 'desc')
            ->get();

        return Inertia::render('Guru/HasilUjian/Index', [
            'jadwals' => $jadwals,
        ]);
    }

    public function nilai(Jadwal $jadwal)
    {
        $guru = auth()->user()->guru;
        abort_unless($guru, 403);

        $jadwal->load('bankSoal');
        abort_if($jadwal->bankSoal->guru_id !== $guru->id, 403);

        $kkm = $jadwal->bankSoal->kkm ?? 0;

        $nilais = Nilai::with('siswa:id,nama,nis')
            ->where('jadwal_id', $jadwal->id)
            ->get()
            ->map(function ($n) use ($kkm) {
                $total = (float) $n->pg_nilai
                    + (float) $n->kompleks_nilai
                    + (float) $n->jodohkan_nilai
                    + (float) $n->isian_nilai
                    + (float) $n->esai_nilai;

                return [
                    'siswa_id' => $n->siswa_id,
                    'nama' => $n->siswa?->nama ?? '-',
                    'nis' => $n->siswa?->nis ?? '-',
                    'pg_nilai' => (float) $n->pg_nilai,
                    'esai_nilai' => (float) $n->esai_nilai,
                    'kompleks_nilai' => (float) $n->kompleks_nilai,
                    'jodohkan_nilai' => (float) $n->jodohkan_nilai,
                    'isian_nilai' => (float) $n->isian_nilai,
                    'total' => round($total, 2),
                    'dikoreksi' => (bool) $n->dikoreksi,
                    'lulus' => $total >= $kkm,
                    'kkm' => $kkm,
                ];
            });

        return response()->json(['data' => $nilais]);
    }
}
