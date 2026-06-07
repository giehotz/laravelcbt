<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Cbt\Jadwal;
use App\Models\Cbt\Nilai;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GuruUlanganController extends Controller
{
    public function index()
    {
        $guru = auth()->user()->guru;
        abort_unless($guru, 403);

        $today = now()->toDateString();

        $bankIds = DB::table('cbt_bank_soal')
            ->where('guru_id', $guru->id)
            ->pluck('id');

        $jadwals = Jadwal::with('bankSoal')
            ->whereIn('bank_id', $bankIds)
            ->orderBy('tgl_mulai', 'desc')
            ->limit(50)
            ->get();

        $willCome = $jadwals->filter(fn ($j) => $j->tgl_mulai >= $today)->take(5)->values();
        $completed = $jadwals->filter(fn ($j) => $j->tgl_mulai < $today)->take(10)->values();

        $stats = [
            'total_jadwal' => $jadwals->count(),
            'total_selesai' => $completed->count(),
            'total_akan_datang' => $willCome->count(),
            'total_siswa' => Nilai::whereIn('jadwal_id', $jadwals->pluck('id'))->distinct('siswa_id')->count('siswa_id'),
        ];

        return Inertia::render('Guru/UlanganUjian/Index', [
            'willCome' => $willCome,
            'completed' => $completed,
            'stats' => $stats,
        ]);
    }
}
