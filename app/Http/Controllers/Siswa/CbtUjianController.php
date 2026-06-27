<?php

namespace App\Http\Controllers\Siswa;

use App\Enums\Cbt\DurasiStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\SoalUjianResource;
use App\Models\Cbt\DurasiSiswa;
use App\Models\Cbt\Jadwal;
use App\Models\Cbt\SoalSiswa;
use App\Models\Cbt\Token;
use App\Services\CbtService;
use Carbon\Carbon;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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

        $siswa = auth()->user()->siswa;

        $durasi = DurasiSiswa::where('siswa_id', $siswa->id)
            ->where('jadwal_id', $jadwal->id)
            ->first();

        $sisaWaktu = (int) $jadwal->durasi_ujian * 60;

        if ($durasi && $durasi->status === DurasiStatus::SEDANG) {
            $startTime = Carbon::parse($durasi->mulai);
            $endTime = $startTime->copy()->addMinutes($jadwal->durasi_ujian);
            $timeLeft = now()->diffInSeconds($endTime, false);

            $scheduleEndTime = Carbon::parse($jadwal->tgl_selesai);
            $scheduleTimeLeft = now()->diffInSeconds($scheduleEndTime, false);

            $sisaWaktu = max(0, min($timeLeft, $scheduleTimeLeft));
        }

        return Inertia::render('Cbt/Siswa/Ujian', [
            'jadwal' => $jadwal,
            'sisa_waktu' => $sisaWaktu,
        ]);
    }

    /**
     * Memulai ujian: Distribusi soal dan set durasi mulai.
     */
    public function mulaiUjian(Request $request, Jadwal $jadwal)
    {
        $siswa = auth()->user()->siswa;

        if (! $siswa) {
            return response()->json(['message' => 'Akses ditolak.'], 403);
        }

        // Validasi token jika diperlukan oleh jadwal (C-06, C-08)
        if ($jadwal->token) {
            $request->validate([
                'token' => 'required|string',
            ]);

            $lockKey = "token_lock:{$siswa->id}:{$jadwal->id}";
            if (RateLimiter::tooManyAttempts($lockKey, 5)) {
                $seconds = RateLimiter::availableIn($lockKey);

                return response()->json(['message' => "Terlalu banyak percobaan token salah. Coba lagi dalam {$seconds} detik."], 429);
            }

            // Ambil token aktif
            $activeToken = Token::first();
            if (! $activeToken || strtoupper($request->token) !== strtoupper($activeToken->token)) {
                RateLimiter::hit($lockKey, 900); // 15 menit lockout

                return response()->json(['message' => 'Token ujian tidak valid atau salah.'], 422);
            }

            // Sukses, bersihkan lockout counter
            RateLimiter::clear($lockKey);
        }

        // Distribusikan soal (CbtService menghandle pessimistic lock dan duplikasi)
        try {
            $this->cbtService->distribusiSoal($siswa, $jadwal);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }

        // Catat waktu mulai di CbtDurasiSiswa
        $durasi = DurasiSiswa::where('siswa_id', $siswa->id)
            ->where('jadwal_id', $jadwal->id)
            ->first();

        if ($durasi && $durasi->status === DurasiStatus::BELUM_UJIAN) {
            $durasi->status = DurasiStatus::SEDANG;
            $durasi->mulai = now()->toTimeString();
            $durasi->save();
        }

        return response()->json(['message' => 'Ujian dimulai']);
    }

    /**
     * Mengambil daftar soal untuk ujian.
     */
    public function getSoal(Request $request, Jadwal $jadwal)
    {
        $siswa = auth()->user()->siswa;

        $soalSiswas = SoalSiswa::with(['soal.pairs', 'jadwal', 'bankSoal'])
            ->where('siswa_id', $siswa->id)
            ->where('jadwal_id', $jadwal->id)
            ->orderBy('no_soal_alias', 'asc')
            ->get();

        return SoalUjianResource::collection($soalSiswas);
    }

    /**
     * Menyimpan jawaban per nomor.
     */
    public function simpanJawaban(Request $request, SoalSiswa $soalSiswa)
    {
        $request->validate([
            'jawaban' => 'nullable|string',
            'ragu_ragu' => 'boolean',
        ]);

        $siswa = auth()->user()->siswa;

        try {
            $this->cbtService->simpanJawaban($soalSiswa, $request->jawaban, $request->ragu_ragu, $siswa);

            return response()->json(['message' => 'Tersimpan']);
        } catch (AuthorizationException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        } catch (\DomainException $e) {
            return response()->json(['message' => $e->getMessage()], 403);
        }
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

        // Cek jika ujian sudah diselesaikan sebelumnya (idempotensi)
        if ($durasi && $durasi->status === DurasiStatus::SELESAI) {
            return response()->json(['message' => 'Ujian sudah diselesaikan sebelumnya.']);
        }

        if ($durasi && $durasi->status === DurasiStatus::SEDANG) {
            $durasi->status = DurasiStatus::SELESAI;
            $durasi->selesai = now()->toTimeString();
            $durasi->save();

            // Hitung nilai via CbtService
            $this->cbtService->hitungNilai($siswa, $jadwal);

            return response()->json(['message' => 'Ujian selesai dan nilai diproses.']);
        }

        return response()->json(['message' => 'Status ujian tidak valid.'], 400);
    }
}
