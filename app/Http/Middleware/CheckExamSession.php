<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use App\Models\CbtJadwal;
use Carbon\Carbon;

class CheckExamSession
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // 1. Mencegah multi-login: Jika ada lebih dari 1 session aktif untuk user ini,
        // tolak akses ujian untuk mencegah kecurangan (misal dikerjakan orang lain di device berbeda).
        if ($user) {
            $sessionCount = DB::table('sessions')->where('user_id', $user->id)->count();
            if ($sessionCount > 1) {
                // Hapus session lama atau blokir request
                // Untuk keamanan CBT, kita bisa kembalikan error 403 atau logout
                if ($request->expectsJson()) {
                    return response()->json(['message' => 'Terdeteksi login ganda. Harap logout dari perangkat lain.'], 403);
                }
                return redirect()->route('dashboard')->with('error', 'Terdeteksi login ganda. Anda tidak bisa mengikuti ujian sebelum logout dari perangkat lain.');
            }
        }

        // 2. Memvalidasi bahwa sesi ujian masih dalam rentang waktu jadwal yang aktif.
        $jadwal = $request->route('jadwal');
        if ($jadwal) {
            if (!$jadwal instanceof CbtJadwal) {
                $jadwal = CbtJadwal::find($jadwal);
            }

            if ($jadwal) {
                $now = now()->toISOString();
                // tgl_mulai dan tgl_selesai disave sebagai string ISO sesuai IMPL_DASHBOARD
                if ($now < $jadwal->tgl_mulai || $now > $jadwal->tgl_selesai || $jadwal->status !== 1) {
                    if ($request->expectsJson()) {
                        return response()->json(['message' => 'Ujian sudah ditutup atau belum dimulai.'], 403);
                    }
                    return redirect()->route('dashboard')->with('error', 'Waktu ujian telah habis atau ujian ditutup.');
                }
            }
        }

        return $next($request);
    }
}
