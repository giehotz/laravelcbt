<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Cbt\Jenis;
use App\Models\Cbt\Nilai;
use App\Models\Master\GuruMapelKelas;
use App\Models\Master\Kelas;
use App\Models\Master\Mapel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GuruRekapNilaiController extends Controller
{
    public function index()
    {
        $guru = auth()->user()->guru;
        abort_unless($guru, 403);

        $assignmentIds = GuruMapelKelas::where('guru_id', $guru->id)->get();

        $kelasIds = $assignmentIds->pluck('kelas_id')->unique();
        $mapelIds = $assignmentIds->pluck('mapel_id')->unique();

        $kelas = Kelas::whereIn('id', $kelasIds)->get(['id', 'nama_kelas']);
        $mapels = Mapel::whereIn('id', $mapelIds)->get(['id', 'nama_mapel']);
        $jenis = Jenis::all(['id', 'nama_jenis']);

        return Inertia::render('Guru/RekapNilai/Index', [
            'kelas' => $kelas,
            'mapels' => $mapels,
            'jenis' => $jenis,
        ]);
    }

    public function data(Request $request)
    {
        $guru = auth()->user()->guru;
        abort_unless($guru, 403);

        $validated = $request->validate([
            'kelas_id' => 'nullable|exists:kelas,id',
            'mapel_id' => 'nullable|exists:mapel,id',
            'jenis_id' => 'nullable|exists:cbt_jenis,id',
            'status_kkm' => 'nullable|in:tuntas,belum',
        ]);

        $query = Nilai::query()
            ->join('cbt_jadwal', 'cbt_jadwal.id', '=', 'cbt_nilai.jadwal_id')
            ->join('cbt_bank_soal', 'cbt_bank_soal.id', '=', 'cbt_jadwal.bank_id')
            ->join('siswa', 'siswa.id', '=', 'cbt_nilai.siswa_id')
            ->join('kelas_siswa', function ($join) {
                $join->on('kelas_siswa.siswa_id', '=', 'siswa.id')
                    ->where('kelas_siswa.tahun_pelajaran_id', DB::raw('cbt_bank_soal.tahun_pelajaran_id'))
                    ->where('kelas_siswa.semester_id', DB::raw('cbt_bank_soal.semester_id'));
            })
            ->join('kelas', 'kelas.id', '=', 'kelas_siswa.kelas_id')
            ->where('cbt_bank_soal.guru_id', $guru->id)
            ->select(
                'siswa.id as siswa_id',
                'siswa.nama as nama_siswa',
                'siswa.nis',
                'kelas.nama_kelas',
                'cbt_bank_soal.mapel_id',
                'cbt_bank_soal.jenis_id',
                'cbt_bank_soal.kkm',
                'cbt_nilai.pg_nilai',
                'cbt_nilai.esai_nilai',
                'cbt_nilai.kompleks_nilai',
                'cbt_nilai.jodohkan_nilai',
                'cbt_nilai.isian_nilai',
            );

        if ($validated['kelas_id'] ?? null) {
            $query->where('kelas.id', $validated['kelas_id']);
        }
        if ($validated['mapel_id'] ?? null) {
            $query->where('cbt_bank_soal.mapel_id', $validated['mapel_id']);
        }
        if ($validated['jenis_id'] ?? null) {
            $query->where('cbt_bank_soal.jenis_id', $validated['jenis_id']);
        }

        $results = $query->get();

        $grouped = $results->group(function ($item) {
            return $item->siswa_id.'-'.$item->mapel_id;
        })->map(function ($group) {
            $first = $group->first();
            $total = $group->sum(fn ($g) => (float) $g->pg_nilai + (float) $g->esai_nilai + (float) $g->kompleks_nilai + (float) $g->jodohkan_nilai + (float) $g->isian_nilai);
            $kkm = $first->kkm ?? 0;

            return [
                'siswa_id' => $first->siswa_id,
                'nama' => $first->nama_siswa,
                'nis' => $first->nis,
                'kelas' => $first->nama_kelas,
                'total' => round($total, 2),
                'kkm' => $kkm,
                'lulus' => $total >= $kkm,
            ];
        })->values();

        if (($validated['status_kkm'] ?? null) === 'tuntas') {
            $grouped = $grouped->filter(fn ($g) => $g['lulus'])->values();
        } elseif (($validated['status_kkm'] ?? null) === 'belum') {
            $grouped = $grouped->filter(fn ($g) => ! $g['lulus'])->values();
        }

        return response()->json([
            'data' => $grouped,
        ]);
    }
}
