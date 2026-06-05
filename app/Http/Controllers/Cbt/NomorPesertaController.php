<?php

namespace App\Http\Controllers\Cbt;

use App\Http\Controllers\Controller;
use App\Models\Cbt\NomorPeserta;
use App\Models\Master\Kelas;
use App\Models\TahunPelajaran;
use App\Models\Semester;
use App\Actions\Cbt\GenerateNomorPesertaAction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class NomorPesertaController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('viewAny', NomorPeserta::class);

        $tpAktif = TahunPelajaran::where('active', true)->firstOrFail();
        $smtAktif = Semester::where('active', true)->firstOrFail();

        $query = NomorPeserta::with(['siswa', 'siswa.kelasSiswa' => function($q) use ($tpAktif, $smtAktif) {
            $q->where('tahun_pelajaran_id', $tpAktif->id)->where('semester_id', $smtAktif->id)->with('kelas');
        }])
        ->where('tp_id', $tpAktif->id)
        ->where('smt_id', $smtAktif->id);

        if ($request->kelas_id) {
            $query->whereHas('siswa.kelasSiswa', function ($q) use ($request, $tpAktif, $smtAktif) {
                $q->where('kelas_id', $request->kelas_id)
                  ->where('tahun_pelajaran_id', $tpAktif->id)
                  ->where('semester_id', $smtAktif->id);
            });
        }

        $peserta = $query->paginate(50)->through(function ($item) {
            $kelasSiswa = $item->siswa->kelasSiswa->first();
            return [
                'id' => $item->id,
                'nomor_peserta' => $item->nomor_peserta,
                'siswa' => [
                    'id' => $item->siswa->id,
                    'nama' => $item->siswa->nama,
                    'nisn' => $item->siswa->nisn,
                ],
                'nama_kelas' => $kelasSiswa ? $kelasSiswa->kelas->nama_kelas : '-',
            ];
        })->withQueryString();

        // Get kelas for filter dropdown
        $kelas = Kelas::whereHas('kelasSiswa', function ($q) use ($tpAktif, $smtAktif) {
            $q->where('tahun_pelajaran_id', $tpAktif->id)->where('semester_id', $smtAktif->id);
        })->get(['id', 'nama_kelas']);

        return Inertia::render('Cbt/NomorPeserta/Index', [
            'peserta' => $peserta,
            'kelas' => $kelas,
            'filters' => $request->only('kelas_id'),
        ]);
    }

    public function generate(GenerateNomorPesertaAction $action): RedirectResponse
    {
        Gate::authorize('generate', NomorPeserta::class);

        $tpAktif = TahunPelajaran::where('active', true)->firstOrFail();
        $smtAktif = Semester::where('active', true)->firstOrFail();

        try {
            $action->execute($tpAktif, $smtAktif);
            return redirect()->back()->with('success', 'Nomor Peserta berhasil digenerate secara otomatis.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
