<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\TahunPelajaran;
use App\Http\Requests\StoreTahunPelajaranRequest;
use App\Http\Requests\UpdateTahunPelajaranRequest;
use App\Actions\ActivateTahunPelajaranAction;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class TahunPelajaranController extends Controller
{
    public function index(): Response
    {
        $years = TahunPelajaran::latest()->get();
        $semesters = \App\Models\Semester::latest()->get();

        return Inertia::render('Master/TahunPelajaran/Index', [
            'years' => $years,
            'semesters' => $semesters,
        ]);
    }

    /**
     * Store a newly created academic year.
     */
    public function store(StoreTahunPelajaranRequest $request): RedirectResponse
    {
        $tahunPelajaran = TahunPelajaran::create($request->validated());

        if ($request->boolean('active')) {
            app(ActivateTahunPelajaranAction::class)->execute($tahunPelajaran);
        }

        return redirect()->route('master.tahun-pelajaran.index')
            ->with('success', 'Tahun pelajaran berhasil ditambahkan.');
    }

    /**
     * Update the specified academic year.
     */
    public function update(UpdateTahunPelajaranRequest $request, TahunPelajaran $tahunPelajaran): RedirectResponse
    {
        $tahunPelajaran->update($request->validated());

        if ($tahunPelajaran->active) {
            \Illuminate\Support\Facades\Cache::forget('tp_active');
            \Illuminate\Support\Facades\Cache::forget('tp_active_array');
        }

        return redirect()->route('master.tahun-pelajaran.index')
            ->with('success', 'Tahun pelajaran berhasil diperbarui.');
    }

    /**
     * Activate the specified academic year.
     */
    public function activate(TahunPelajaran $tahunPelajaran, ActivateTahunPelajaranAction $action): RedirectResponse
    {
        $action->execute($tahunPelajaran);

        return redirect()->route('master.tahun-pelajaran.index')
            ->with('success', "Tahun pelajaran {$tahunPelajaran->tahun} berhasil diaktifkan.");
    }

    /**
     * Delete the specified academic year.
     */
    public function destroy(TahunPelajaran $tahunPelajaran): RedirectResponse
    {
        if ($tahunPelajaran->active) {
            return redirect()->route('master.tahun-pelajaran.index')
                ->with('error', 'Tidak dapat menghapus tahun pelajaran yang sedang aktif.');
        }

        $tahunPelajaran->delete();

        return redirect()->route('master.tahun-pelajaran.index')
            ->with('success', 'Tahun pelajaran berhasil dihapus.');
    }
}
