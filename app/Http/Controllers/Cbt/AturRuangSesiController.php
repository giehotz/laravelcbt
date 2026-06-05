<?php

namespace App\Http\Controllers\Cbt;

use App\Http\Controllers\Controller;
use App\Models\Cbt\KelasRuang;
use App\Models\Master\Kelas;
use App\Models\Cbt\Ruang;
use App\Models\Cbt\Sesi;
use App\Models\TahunPelajaran;
use App\Models\Semester;
use App\Jobs\Cbt\SyncSesiSiswaJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class AturRuangSesiController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', KelasRuang::class);

        $tpAktif = TahunPelajaran::where('active', true)->firstOrFail();
        $smtAktif = Semester::where('active', true)->firstOrFail();

        // Ambil semua kelas yang ada siswanya di TP/SMT ini
        $kelas = Kelas::whereHas('kelasSiswa', function ($q) use ($tpAktif, $smtAktif) {
            $q->where('tahun_pelajaran_id', $tpAktif->id)->where('semester_id', $smtAktif->id);
        })
        ->orderBy('nama_kelas')
        ->get(['id', 'nama_kelas']);

        // Dapatkan mapping KelasRuang saat ini
        $kelasRuang = KelasRuang::where('tp_id', $tpAktif->id)
            ->where('smt_id', $smtAktif->id)
            ->get()
            ->keyBy('kelas_id');

        // Gabungkan kelas dengan status alokasinya
        $data = $kelas->map(function ($k) use ($kelasRuang) {
            $mapping = $kelasRuang->get($k->id);
            return [
                'kelas_id' => $k->id,
                'nama_kelas' => $k->nama_kelas,
                'ruang_id' => $mapping ? $mapping->ruang_id : null,
                'sesi_id' => $mapping ? $mapping->sesi_id : null,
                'id' => $mapping ? $mapping->id : null, // ID cbt_kelas_ruang jika ada
            ];
        });

        return Inertia::render('Cbt/AturRuang/Index', [
            'kelasData' => $data,
            'ruang' => Ruang::orderBy('nama_ruang')->get(['id', 'nama_ruang']),
            'sesi' => Sesi::orderBy('nama_sesi')->get(['id', 'nama_sesi', 'waktu_mulai', 'waktu_akhir']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        Gate::authorize('store', KelasRuang::class);

        $request->validate([
            'kelas_id' => ['required', 'exists:kelas,id'],
            'ruang_id' => ['required', 'exists:cbt_ruang,id'],
            'sesi_id' => ['required', 'exists:cbt_sesi,id'],
        ]);

        $tpAktif = TahunPelajaran::where('active', true)->firstOrFail();
        $smtAktif = Semester::where('active', true)->firstOrFail();

        // Update or Create mapping
        $kelasRuang = KelasRuang::updateOrCreate(
            [
                'kelas_id' => $request->kelas_id,
                'tp_id' => $tpAktif->id,
                'smt_id' => $smtAktif->id,
            ],
            [
                'ruang_id' => $request->ruang_id,
                'sesi_id' => $request->sesi_id,
            ]
        );

        // Jalankan Job Sinkronisasi individual
        SyncSesiSiswaJob::dispatch($kelasRuang);

        return redirect()->back()->with('success', 'Mapping kelas berhasil disimpan dan sedang disinkronkan.');
    }

    public function sync(KelasRuang $kelasRuang): RedirectResponse
    {
        Gate::authorize('update', KelasRuang::class);

        // Panggil Job secara sinkron (atau dispatch)
        SyncSesiSiswaJob::dispatchSync($kelasRuang);

        return redirect()->back()->with('success', 'Sinkronisasi manual berhasil dijalankan.');
    }
}
