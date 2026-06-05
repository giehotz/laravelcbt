<?php

namespace App\Http\Controllers\Cbt;

use App\Http\Controllers\Controller;
use App\Models\Cbt\Jadwal;
use App\Models\CbtDurasiSiswa;
use App\Models\Master\Siswa;
use App\Models\CbtSoalSiswa;
use App\Services\CbtService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProctorController extends Controller
{
    /**
     * Halaman Monitoring Ujian
     */
    public function monitoring(Request $request)
    {
        // Load jadwal yang sedang aktif atau dijadwalkan hari ini
        $jadwalAktif = Jadwal::with(['bankSoal.mapel'])
            ->orderBy('tgl_mulai', 'desc')
            ->get();

        return Inertia::render('Cbt/Proctor/Monitoring', [
            'jadwalAktif' => $jadwalAktif,
        ]);
    }

    /**
     * API: Ambil status siswa untuk sebuah jadwal
     */
    public function statusSiswa(Request $request, Jadwal $jadwal)
    {
        // Ambil semua durasi siswa untuk jadwal ini
        $durasis = CbtDurasiSiswa::with(['siswa' => function($q) {
            $q->select('id', 'nama', 'nis', 'nisn');
        }])
        ->where('jadwal_id', $jadwal->id)
        ->get();

        // Ambil info progres (jumlah dijawab) dari CbtSoalSiswa
        $progress = CbtSoalSiswa::where('jadwal_id', $jadwal->id)
            ->selectRaw('siswa_id, COUNT(id) as total_soal, SUM(CASE WHEN jawaban_siswa IS NOT NULL THEN 1 ELSE 0 END) as dijawab')
            ->groupBy('siswa_id')
            ->get()
            ->keyBy('siswa_id');

        $result = $durasis->map(function ($durasi) use ($progress) {
            $siswaProgress = $progress->get($durasi->siswa_id);
            return [
                'id' => $durasi->id,
                'siswa_id' => $durasi->siswa_id,
                'nama' => $durasi->siswa->nama ?? 'Unknown',
                'nis' => $durasi->siswa->nis ?? '-',
                'status' => $durasi->status, // 0: belum, 1: sedang, 2: selesai
                'mulai' => $durasi->mulai,
                'selesai' => $durasi->selesai,
                'total_soal' => $siswaProgress ? $siswaProgress->total_soal : 0,
                'dijawab' => $siswaProgress ? $siswaProgress->dijawab : 0,
            ];
        });

        return response()->json([
            'data' => $result
        ]);
    }

    /**
     * Reset Login Siswa (Hapus sesi aktif)
     */
    public function resetLogin(CbtDurasiSiswa $durasi, Request $request)
    {
        if (auth()->user()->hasRole('kepsek') && !auth()->user()->hasRole('superadmin')) {
            abort(403, 'Anda tidak memiliki akses untuk melakukan aksi ini.');
        }

        $durasi->update([
            'status' => 0, // Reset ke belum mulai (atau hapus durasi sepenuhnya?)
            'mulai' => null,
            'selesai' => null,
        ]);

        return response()->json(['message' => 'Login siswa berhasil direset.']);
    }

    /**
     * Paksa Selesai Ujian Siswa
     */
    public function forceSelesai(Request $request, CbtDurasiSiswa $durasi, CbtService $cbtService)
    {
        if (auth()->user()->hasRole('kepsek') && !auth()->user()->hasRole('superadmin')) {
            abort(403, 'Anda tidak memiliki akses untuk melakukan aksi ini.');
        }

        if ($durasi->status == 1) {
            $durasi->update([
                'status' => 2,
                'selesai' => now()->toTimeString(),
            ]);

            // Hitung nilai
            $siswa = Siswa::find($durasi->siswa_id);
            $jadwal = Jadwal::find($durasi->jadwal_id);
            
            if ($siswa && $jadwal) {
                $cbtService->hitungNilai($siswa, $jadwal);
            }

            return response()->json(['message' => 'Sesi ujian berhasil diselesaikan secara paksa.']);
        }

        return response()->json(['message' => 'Siswa tidak sedang mengerjakan ujian.'], 400);
    }
}
