<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Cbt\DurasiSiswa;
use App\Models\Cbt\Jadwal;
use App\Models\Cbt\SoalSiswa;
use Inertia\Inertia;

class GuruStatusSiswaController extends Controller
{
    public function index()
    {
        $guru = auth()->user()->guru;
        abort_unless($guru, 403);

        $jadwals = Jadwal::with('bankSoal')
            ->whereHas('bankSoal', fn ($q) => $q->where('guru_id', $guru->id))
            ->where('status', 1)
            ->orderBy('tgl_mulai', 'desc')
            ->get();

        return Inertia::render('Guru/StatusSiswa/Index', [
            'jadwals' => $jadwals,
        ]);
    }

    public function data(Jadwal $jadwal)
    {
        $guru = auth()->user()->guru;
        abort_unless($guru, 403);

        $jadwal->load(['bankSoal' => fn ($q) => $q->where('guru_id', $guru->id)]);
        abort_unless($jadwal->bankSoal, 403);

        $durasis = DurasiSiswa::with(['siswa' => fn ($q) => $q->select('id', 'nama', 'nis', 'nisn')])
            ->where('jadwal_id', $jadwal->id)
            ->get();

        $progress = SoalSiswa::where('jadwal_id', $jadwal->id)
            ->selectRaw('siswa_id, COUNT(id) as total_soal, SUM(CASE WHEN jawaban_siswa IS NOT NULL AND jawaban_siswa != \'\' THEN 1 ELSE 0 END) as dijawab')
            ->groupBy('siswa_id')
            ->get()
            ->keyBy('siswa_id');

        $result = $durasis->map(function ($durasi) use ($progress) {
            $p = $progress->get($durasi->siswa_id);

            return [
                'id' => $durasi->id,
                'siswa_id' => $durasi->siswa_id,
                'nama' => $durasi->siswa?->nama ?? 'Unknown',
                'nis' => $durasi->siswa?->nis ?? '-',
                'status' => (int) $durasi->status,
                'mulai' => $durasi->mulai,
                'selesai' => $durasi->selesai,
                'total_soal' => $p ? (int) $p->total_soal : 0,
                'dijawab' => $p ? (int) $p->dijawab : 0,
            ];
        });

        return response()->json(['data' => $result]);
    }
}
