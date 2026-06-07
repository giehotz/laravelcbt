<?php

namespace App\Http\Controllers\Cbt;

use App\Http\Controllers\Controller;
use App\Models\Cbt\DurasiSiswa;
use App\Models\Cbt\Jadwal;
use App\Models\Cbt\Nilai;
use App\Models\Cbt\Sesi;
use App\Models\Cbt\SoalSiswa;
use App\Models\Master\Kelas;
use App\Models\Master\Siswa;
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

        $kelasList = Kelas::orderBy('nama_kelas')->get();
        $sesiList = Sesi::orderBy('waktu_mulai')->get();

        return Inertia::render('Cbt/Proctor/Monitoring', [
            'jadwalAktif' => $jadwalAktif,
            'kelasList' => $kelasList,
            'sesiList' => $sesiList,
        ]);
    }

    /**
     * API: Ambil status siswa untuk sebuah jadwal
     */
    public function statusSiswa(Request $request, Jadwal $jadwal)
    {
        // Ambil semua durasi siswa untuk jadwal ini beserta info kelas & sesi
        $durasis = DurasiSiswa::with([
            'siswa' => function ($q) {
                $q->select('id', 'nama', 'nis', 'nisn');
            },
            'siswa.sesiSiswa.kelas',
            'siswa.sesiSiswa.sesi',
            'siswa.sesiSiswa.ruang',
        ])
            ->where('jadwal_id', $jadwal->id)
            ->get();

        // Ambil info progres (jumlah dijawab) dari SoalSiswa
        $progress = SoalSiswa::where('jadwal_id', $jadwal->id)
            ->selectRaw('siswa_id, COUNT(id) as total_soal, SUM(CASE WHEN jawaban_siswa IS NOT NULL THEN 1 ELSE 0 END) as dijawab')
            ->groupBy('siswa_id')
            ->get()
            ->keyBy('siswa_id');

        $result = $durasis->map(function ($durasi) use ($progress) {
            $siswaProgress = $progress->get($durasi->siswa_id);
            $sesiSiswa = $durasi->siswa ? $durasi->siswa->sesiSiswa->first() : null;

            return [
                'id' => $durasi->id,
                'siswa_id' => $durasi->siswa_id,
                'nama' => $durasi->siswa->nama ?? 'Unknown',
                'nis' => $durasi->siswa->nis ?? '-',
                'nisn' => $durasi->siswa->nisn ?? '-',
                'kelas_id' => $sesiSiswa->kelas_id ?? null,
                'nama_kelas' => $sesiSiswa->kelas->nama_kelas ?? '-',
                'sesi_id' => $sesiSiswa->sesi_id ?? null,
                'nama_sesi' => $sesiSiswa->sesi->nama_sesi ?? '-',
                'nama_ruang' => $sesiSiswa->ruang->nama_ruang ?? '-',
                'status' => $durasi->status, // 0: belum, 1: sedang, 2: selesai
                'mulai' => $durasi->mulai,
                'selesai' => $durasi->selesai,
                'total_soal' => $siswaProgress ? $siswaProgress->total_soal : 0,
                'dijawab' => $siswaProgress ? $siswaProgress->dijawab : 0,
            ];
        });

        return response()->json([
            'data' => $result,
        ]);
    }

    /**
     * Reset Login Siswa (Hapus sesi aktif)
     */
    public function resetLogin(DurasiSiswa $durasi, Request $request)
    {
        if (auth()->user()->hasRole('kepsek') && ! auth()->user()->hasRole('superadmin')) {
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
    public function forceSelesai(Request $request, DurasiSiswa $durasi, CbtService $cbtService)
    {
        if (auth()->user()->hasRole('kepsek') && ! auth()->user()->hasRole('superadmin')) {
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

    /**
     * Ulangi Ujian Siswa dari Awal (Hapus data jawaban & durasi)
     */
    public function ulang(DurasiSiswa $durasi, Request $request)
    {
        if (auth()->user()->hasRole('kepsek') && ! auth()->user()->hasRole('superadmin')) {
            abort(403, 'Anda tidak memiliki akses untuk melakukan aksi ini.');
        }

        $siswaId = $durasi->siswa_id;
        $jadwalId = $durasi->jadwal_id;

        // Hapus data jawaban siswa
        SoalSiswa::where('siswa_id', $siswaId)
            ->where('jadwal_id', $jadwalId)
            ->delete();

        // Hapus data nilai jika ada
        Nilai::where('siswa_id', $siswaId)
            ->where('jadwal_id', $jadwalId)
            ->delete();

        // Hapus data durasi
        $durasi->delete();

        return response()->json(['message' => 'Ujian siswa berhasil diulang dari awal.']);
    }

    /**
     * Aksi Massal untuk durasi terpilih
     */
    public function bulkAction(Request $request, CbtService $cbtService)
    {
        if (auth()->user()->hasRole('kepsek') && ! auth()->user()->hasRole('superadmin')) {
            abort(403, 'Anda tidak memiliki akses untuk melakukan aksi ini.');
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:cbt_durasi_siswa,id',
            'action' => 'required|string|in:reset,force-selesai,ulang',
        ]);

        $durasiIds = $request->ids;
        $action = $request->action;

        foreach ($durasiIds as $durasiId) {
            $durasi = DurasiSiswa::find($durasiId);
            if (! $durasi) {
                continue;
            }

            if ($action === 'reset') {
                $durasi->update([
                    'status' => 0,
                    'mulai' => null,
                    'selesai' => null,
                ]);
            } elseif ($action === 'force-selesai') {
                if ($durasi->status == 1) {
                    $durasi->update([
                        'status' => 2,
                        'selesai' => now()->toTimeString(),
                    ]);
                    $siswa = Siswa::find($durasi->siswa_id);
                    $jadwal = Jadwal::find($durasi->jadwal_id);
                    if ($siswa && $jadwal) {
                        $cbtService->hitungNilai($siswa, $jadwal);
                    }
                }
            } elseif ($action === 'ulang') {
                SoalSiswa::where('siswa_id', $durasi->siswa_id)
                    ->where('jadwal_id', $durasi->jadwal_id)
                    ->delete();
                Nilai::where('siswa_id', $durasi->siswa_id)
                    ->where('jadwal_id', $durasi->jadwal_id)
                    ->delete();
                $durasi->delete();
            }
        }

        return response()->json(['message' => 'Aksi massal berhasil diterapkan.']);
    }
}
