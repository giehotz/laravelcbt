<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Cbt\Jadwal;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GuruAnalisisSoalController extends Controller
{
    public function index()
    {
        $guru = auth()->user()->guru;
        abort_unless($guru, 403);

        $jadwals = Jadwal::with('bankSoal')
            ->whereHas('bankSoal', fn ($q) => $q->where('guru_id', $guru->id))
            ->whereExists(fn ($q) => $q->select(DB::raw(1))
                ->from('cbt_nilai')
                ->whereColumn('cbt_nilai.jadwal_id', 'cbt_jadwal.id'))
            ->orderBy('tgl_mulai', 'desc')
            ->get();

        return Inertia::render('Guru/AnalisisSoal/Index', [
            'jadwals' => $jadwals,
        ]);
    }

    public function data(Jadwal $jadwal)
    {
        $guru = auth()->user()->guru;
        abort_unless($guru, 403);

        $jadwal->load('bankSoal');
        abort_if($jadwal->bankSoal->guru_id !== $guru->id, 403);

        $results = DB::table('cbt_soal_siswa')
            ->join('cbt_soal', 'cbt_soal.id', '=', 'cbt_soal_siswa.soal_id')
            ->where('cbt_soal_siswa.jadwal_id', $jadwal->id)
            ->select(
                'cbt_soal.id as soal_id',
                DB::raw('LEFT(COALESCE(cbt_soal.soal, ""), 50) as soal_teks'),
                'cbt_soal.nomor_soal',
                'cbt_soal.jenis',
                'cbt_soal.kesulitan',
                DB::raw('COUNT(cbt_soal_siswa.id) as total_siswa'),
                DB::raw('SUM(CASE WHEN cbt_soal_siswa.jawaban_siswa IS NOT NULL AND cbt_soal_siswa.jawaban_siswa != "" AND cbt_soal_siswa.jawaban_siswa = cbt_soal_siswa.jawaban_benar THEN 1 ELSE 0 END) as benar'),
                DB::raw('ROUND(AVG(CASE WHEN cbt_soal_siswa.jawaban_siswa IS NOT NULL AND cbt_soal_siswa.jawaban_siswa != "" AND cbt_soal_siswa.jawaban_siswa = cbt_soal_siswa.jawaban_benar THEN 100 ELSE 0 END), 2) as persentase_benar')
            )
            ->groupBy('cbt_soal.id', 'cbt_soal.soal', 'cbt_soal.nomor_soal', 'cbt_soal.jenis', 'cbt_soal.kesulitan')
            ->orderBy('cbt_soal.nomor_soal')
            ->get();

        $soalList = $results->map(function ($r) {
            $persen = (float) $r->persentase_benar;
            if ($persen >= 76) {
                $kategori = 'Mudah';
            } elseif ($persen >= 26) {
                $kategori = 'Sedang';
            } else {
                $kategori = 'Sulit';
            }

            $kesulitanGuru = (int) $r->kesulitan;
            $ekspektasi = $kesulitanGuru <= 3 ? 'Mudah' : ($kesulitanGuru <= 7 ? 'Sedang' : 'Sulit');

            $kesesuaian = $kategori === $ekspektasi ? 'Sesuai' : ($persen > 50 ? 'Lebih Mudah' : 'Lebih Sulit');

            return [
                'soal_id' => $r->soal_id,
                'nomor' => $r->nomor_soal,
                'teks' => $r->soal_teks,
                'jenis' => (int) $r->jenis,
                'total_siswa' => (int) $r->total_siswa,
                'benar' => (int) $r->benar,
                'persentase_benar' => $persen,
                'kategori' => $kategori,
                'kesulitan_guru' => $kesulitanGuru,
                'kesesuaian' => $kesesuaian,
            ];
        });

        $summary = [
            'total_soal' => $soalList->count(),
            'mudah' => $soalList->where('kategori', 'Mudah')->count(),
            'sedang' => $soalList->where('kategori', 'Sedang')->count(),
            'sulit' => $soalList->where('kategori', 'Sulit')->count(),
            'rata_rata' => $soalList->avg('persentase_benar'),
        ];

        return response()->json([
            'data' => $soalList,
            'summary' => $summary,
        ]);
    }
}
