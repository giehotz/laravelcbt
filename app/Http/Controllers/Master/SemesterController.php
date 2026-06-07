<?php

namespace App\Http\Controllers\Master;

use App\Actions\ActivateSemesterAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSemesterRequest;
use App\Http\Requests\UpdateSemesterRequest;
use App\Models\Semester;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;

class SemesterController extends Controller
{
    /**
     * Display a listing of semesters.
     */
    /**
     * Display a listing of semesters (redirects to combined index).
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('master.tahun-pelajaran.index');
    }

    /**
     * Store a newly created semester.
     */
    public function store(StoreSemesterRequest $request): RedirectResponse
    {
        $semester = Semester::create($request->validated());

        if ($request->boolean('active')) {
            app(ActivateSemesterAction::class)->execute($semester);
        }

        return redirect()->route('master.tahun-pelajaran.index')
            ->with('success', 'Semester berhasil ditambahkan.');
    }

    /**
     * Update the specified semester.
     */
    public function update(UpdateSemesterRequest $request, Semester $semester): RedirectResponse
    {
        $semester->update($request->validated());

        if ($semester->active) {
            Cache::forget('semester_active');
            Cache::forget('semester_active_array');
        }

        return redirect()->route('master.tahun-pelajaran.index')
            ->with('success', 'Semester berhasil diperbarui.');
    }

    /**
     * Activate the specified semester.
     */
    public function activate(Semester $semester, ActivateSemesterAction $action): RedirectResponse
    {
        $action->execute($semester);

        return redirect()->route('master.tahun-pelajaran.index')
            ->with('success', "Semester {$semester->nama_smt} berhasil diaktifkan.");
    }

    /**
     * Delete the specified semester.
     */
    public function destroy(Semester $semester): RedirectResponse
    {
        if ($semester->active) {
            return redirect()->route('master.tahun-pelajaran.index')
                ->with('error', 'Tidak dapat menghapus semester yang sedang aktif.');
        }

        $semester->delete();

        return redirect()->route('master.tahun-pelajaran.index')
            ->with('success', 'Semester berhasil dihapus.');
    }
}
