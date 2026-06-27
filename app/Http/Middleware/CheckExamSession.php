<?php

namespace App\Http\Middleware;

use App\Models\Cbt\Jadwal as CbtJadwal;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class CheckExamSession
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // 1. Mencegah multi-login: Jika fitur reset_login diaktifkan pada jadwal dan
        // ada lebih dari 1 session aktif untuk user ini, tolak akses ujian.
        $jadwal = $request->route('jadwal');
        if ($user && $jadwal) {
            if (! $jadwal instanceof CbtJadwal) {
                $jadwal = CbtJadwal::find($jadwal);
            }

            if ($jadwal && $jadwal->reset_login) {
                $sessionCount = DB::table('sessions')->where('user_id', $user->id)->count();
                if ($sessionCount > 1) {
                    if ($request->expectsJson()) {
                        return response()->json(['message' => 'Terdeteksi login ganda. Harap logout dari perangkat lain.'], 403);
                    }

                    return redirect()->route('dashboard')->with('error', 'Terdeteksi login ganda. Anda tidak bisa mengikuti ujian sebelum logout dari perangkat lain.');
                }
            }
        }

        // 2. Memvalidasi bahwa sesi ujian masih dalam rentang waktu jadwal yang aktif.
        if ($jadwal) {
            $now = now()->toISOString();
            if ($now < $jadwal->tgl_mulai || $now > $jadwal->tgl_selesai || $jadwal->status !== 1) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Ujian sudah ditutup atau belum dimulai.'], 403);
                }

                return redirect()->route('dashboard')->with('error', 'Waktu ujian telah habis atau ujian ditutup.');
            }

            // 3. Memvalidasi bahwa siswa terdaftar di salah satu kelas target ujian (IDOR prevention)
            if ($user && $user->siswa) {
                $bankSoal = $jadwal->bankSoal;
                if ($bankSoal) {
                    $allowedKelasIds = is_array($bankSoal->kelas) ? $bankSoal->kelas : (json_decode($bankSoal->kelas, true) ?: []);

                    $isRegistered = DB::table('kelas_siswa')
                        ->where('siswa_id', $user->siswa->id)
                        ->whereIn('kelas_id', $allowedKelasIds)
                        ->exists();

                    if (! $isRegistered) {
                        if ($request->expectsJson()) {
                            return response()->json(['message' => 'Anda tidak terdaftar di kelas yang diizinkan untuk mengikuti ujian ini.'], 403);
                        }

                        return redirect()->route('dashboard')->with('error', 'Anda tidak terdaftar di kelas yang diizinkan untuk mengikuti ujian ini.');
                    }
                }
            }
        }

        return $next($request);
    }
}
