<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\LevelKelas;
use App\Http\Requests\StoreLevelKelasRequest;
use App\Http\Requests\UpdateLevelKelasRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class LevelKelasController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', LevelKelas::class);

        $levels = LevelKelas::with([
            'kelas' => function ($query) {
                $query->with(['waliKelas', 'tahunPelajaran', 'semester'])
                      ->withCount('siswa');
            }
        ])->latest()->get();

        $years = \App\Models\TahunPelajaran::latest()->get();
        $semesters = \App\Models\Semester::latest()->get();
        $jurusans = \App\Models\Master\Jurusan::active()->get();
        $gurus = \App\Models\Master\Guru::latest()->get();

        return Inertia::render('Master/LevelKelas/Index', [
            'levels' => $levels,
            'years' => $years,
            'semesters' => $semesters,
            'jurusans' => $jurusans,
            'gurus' => $gurus,
        ]);
    }

    public function store(StoreLevelKelasRequest $request): RedirectResponse
    {
        Gate::authorize('create', LevelKelas::class);

        LevelKelas::create($request->validated());

        return redirect()->route('master.level-kelas.index')
            ->with('success', 'Level kelas berhasil ditambahkan.');
    }

    public function update(UpdateLevelKelasRequest $request, LevelKelas $levelKelas): RedirectResponse
    {
        Gate::authorize('update', $levelKelas);

        $levelKelas->update($request->validated());

        return redirect()->route('master.level-kelas.index')
            ->with('success', 'Level kelas berhasil diperbarui.');
    }

    public function destroy(LevelKelas $levelKelas): RedirectResponse
    {
        Gate::authorize('delete', $levelKelas);

        // Deletability check: check if used in Kelas
        if ($levelKelas->kelas()->exists()) {
            return redirect()->route('master.level-kelas.index')
                ->with('error', 'Level kelas tidak dapat dihapus karena sedang digunakan pada kelas.');
        }

        $levelKelas->delete();

        return redirect()->route('master.level-kelas.index')
            ->with('success', 'Level kelas berhasil dihapus.');
    }

    public function generate(Request $request): RedirectResponse
    {
        Gate::authorize('create', LevelKelas::class);

        $request->validate([
            'type' => 'required|string|in:sd,smp,sma',
        ]);

        $levels = [];
        if ($request->type === 'sd') {
            $levels = ['1', '2', '3', '4', '5', '6'];
        } elseif ($request->type === 'smp') {
            $levels = ['7', '8', '9'];
        } elseif ($request->type === 'sma') {
            $levels = ['10', '11', '12'];
        }

        foreach ($levels as $level) {
            LevelKelas::firstOrCreate(['level' => $level]);
        }

        return redirect()->route('master.level-kelas.index')
            ->with('success', 'Template level kelas berhasil di-generate.');
    }
}
