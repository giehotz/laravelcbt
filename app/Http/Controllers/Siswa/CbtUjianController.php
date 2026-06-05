<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Cbt\Jadwal;
use App\Models\CbtSoalSiswa;
use App\Models\Cbt\DurasiSiswa;
use App\Services\CbtService;
use App\Http\Resources\SoalSiswaResource;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CbtUjianController extends Controller
{
    protected $cbtService;

    public function __construct(CbtService $cbtService)
    {
        $this->cbtService = $cbtService;
    }

    /**
     * Halaman Utama Ujian (Inertia)
     */
    public function show(Request $request, Jadwal $jadwal)
    {
        // Load proper relation for Jadwal
        $jadwal->load('bankSoal.mapel');

        return Inertia::render('Cbt/Siswa/Ujian', [
            'jadwal' => $jadwal,
        ]);
    }

    /**
     * Memulai ujian: Distribusi soal dan set durasi mulai.
     */
    public function mulaiUjian(Request $request, Jadwal $jadwal)
    {
        $siswa = auth()->user()->siswa;
        
        if (!$siswa) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        // Distribusikan soal (CbtService menghandle pessimistic lock dan duplikasi)
        $this->cbtService->distribusiSoal($siswa, $jadwal);

        // Catat waktu mulai di CbtDurasiSiswa
        $durasi = DurasiSiswa::where('siswa_id', $siswa->id)
            ->where('jadwal_id', $jadwal->id)
            ->first();

        if ($durasi && $durasi->status == 0) {
            $durasi->update([
                'status' => 1,
                'mulai' => now()->toTimeString(),
            ]);
        }

        return response()->json(['message' => 'Ujian dimulai']);
    }

    /**
     * Mengambil daftar soal untuk ujian.
     */
    public function getSoal(Request $request, Jadwal $jadwal)
    {
        $siswa = auth()->user()->siswa;

        $soalSiswas = CbtSoalSiswa::with(['soal.pairs', 'jadwal'])
            ->where('siswa_id', $siswa->id)
            ->where('jadwal_id', $jadwal->id)
            ->orderBy('no_soal_alias', 'asc')
            ->get();

        return SoalSiswaResource::collection($soalSiswas);
    }

    /**
     * Menyimpan jawaban per nomor.
     */
    public function simpanJawaban(Request $request, CbtSoalSiswa $soalSiswa)
    {
        $request->validate([
            'jawaban' => 'nullable|string',
            'ragu_ragu' => 'boolean',
        ]);

        $siswa = auth()->user()->siswa;
        
        // Pastikan milik siswa yang sedang login
        if ($soalSiswa->siswa_id !== $siswa->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Validasi waktu ujian dari durasi
        $durasi = DurasiSiswa::where('siswa_id', $siswa->id)
            ->where('jadwal_id', $soalSiswa->jadwal_id)
            ->first();

        if (!$durasi || $durasi->status !== 1) {
            return response()->json(['message' => 'Waktu ujian telah habis atau belum dimulai.'], 403);
        }

        // Validasi tambahan: jika waktu jadwal terlewati dengan toleransi latency misal 1 menit
        $jadwal = Jadwal::find($soalSiswa->jadwal_id);
        $now = now()->toISOString();
        if ($now > $jadwal->tgl_selesai) {
            return response()->json(['message' => 'Jadwal ujian sudah ditutup.'], 403);
        }

        $soalSiswa->update([
            'jawaban_siswa' => $request->jawaban,
            'ragu_ragu' => $request->ragu_ragu,
            'updated_at' => now(), // Auto updated by Eloquent
        ]);

        return response()->json(['message' => 'Tersimpan']);
    }

    /**
     * Menyelesaikan ujian dan menghitung nilai awal.
     */
    public function selesaiUjian(Request $request, Jadwal $jadwal)
    {
        $siswa = auth()->user()->siswa;

        $durasi = DurasiSiswa::where('siswa_id', $siswa->id)
            ->where('jadwal_id', $jadwal->id)
            ->first();

        if ($durasi && $durasi->status == 1) {
            $durasi->update([
                'status' => 2,
                'selesai' => now()->toTimeString(),
            ]);

            // Hitung nilai via CbtService
            $this->cbtService->hitungNilai($siswa, $jadwal);

            return response()->json(['message' => 'Ujian selesai dan nilai diproses.']);
        }

        return response()->json(['message' => 'Status ujian tidak valid.'], 400);
    }
}
