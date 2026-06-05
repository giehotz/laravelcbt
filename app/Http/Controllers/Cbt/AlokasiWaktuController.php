<?php

namespace App\Http\Controllers\Cbt;

use App\Http\Controllers\Controller;
use App\Models\Cbt\DurasiSiswa;
use App\Models\Cbt\Jadwal;
use App\Models\Master\Kelas;
use App\Models\Master\Siswa;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AlokasiWaktuController extends Controller
{
    public function index()
    {
        $tpAktif = TahunPelajaran::where('active', true)->firstOrFail();
        $smtAktif = Semester::where('active', true)->firstOrFail();

        $jadwals = Jadwal::with(['bankSoal.mapel', 'bankSoal.jenis'])
            ->where('tahun_pelajaran_id', $tpAktif->id)
            ->where('semester_id', $smtAktif->id)
            ->orderBy('tgl_mulai')
            ->get();

        // Group by substr(tgl_mulai, 0, 10)
        $groupedJadwals = $jadwals->groupBy(function ($item) {
            return substr($item->tgl_mulai, 0, 10);
        });

        // Format for UI
        $formattedData = [];
        foreach ($groupedJadwals as $date => $items) {
            $formattedData[] = [
                'tanggal' => $date,
                'jadwals' => $items->map(function ($j) use ($tpAktif, $smtAktif) {
                    $kelasBank = $j->bankSoal->kelas ?? [];
                    // Get total students for this jadwal
                    $totalSiswa = \App\Models\Master\KelasSiswa::whereIn('kelas_id', $kelasBank)
                        ->where('tahun_pelajaran_id', $tpAktif->id)
                        ->where('semester_id', $smtAktif->id)
                        ->count();

                    // Get total durasi generated
                    $totalDurasi = DurasiSiswa::where('jadwal_id', $j->id)->count();

                    return [
                        'id' => $j->id,
                        'mapel' => $j->bankSoal->mapel->nama_mapel ?? '-',
                        'jenis' => $j->bankSoal->jenis->nama_jenis ?? '-',
                        'waktu' => substr($j->tgl_mulai, 11, 5) . ' - ' . substr($j->tgl_selesai, 11, 5),
                        'total_siswa' => $totalSiswa,
                        'total_alokasi' => $totalDurasi,
                        'status' => $j->status,
                    ];
                })
            ];
        }

        return Inertia::render('Cbt/AlokasiWaktu/Index', [
            'data' => $formattedData,
        ]);
    }

    public function generate(Jadwal $jadwal)
    {
        $jadwal->load('bankSoal');
        $kelasIds = $jadwal->bankSoal->kelas ?? [];

        // Get all students for the classes
        $siswas = \App\Models\Master\KelasSiswa::whereIn('kelas_id', $kelasIds)
            ->where('tahun_pelajaran_id', $jadwal->tahun_pelajaran_id)
            ->where('semester_id', $jadwal->semester_id)
            ->get(['siswa_id']);

        $generated = 0;
        foreach ($siswas as $kelasSiswa) {
            $exists = DurasiSiswa::where('jadwal_id', $jadwal->id)
                ->where('siswa_id', $kelasSiswa->siswa_id)
                ->exists();

            if (!$exists) {
                DurasiSiswa::create([
                    'jadwal_id' => $jadwal->id,
                    'siswa_id' => $kelasSiswa->siswa_id,
                    'status' => 0, // 0 = belum mulai
                    'reset' => 0,
                ]);
                $generated++;
            }
        }

        return redirect()->back()->with('success', "Berhasil me-generate {$generated} alokasi waktu siswa.");
    }
}
