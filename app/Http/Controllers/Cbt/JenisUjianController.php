<?php

namespace App\Http\Controllers\Cbt;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cbt\StoreJenisRequest;
use App\Http\Requests\Cbt\UpdateJenisRequest;
use App\Http\Resources\Cbt\JenisResource;
use App\Models\Cbt\Jenis;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class JenisUjianController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        Gate::authorize('viewAny', Jenis::class);

        $jenis = Jenis::latest()->paginate(10);

        return Inertia::render('Cbt/Jenis/Index', [
            'jenis' => JenisResource::collection($jenis),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreJenisRequest $request): RedirectResponse
    {
        Jenis::create($request->validated());

        return redirect()->back()->with('success', 'Jenis Ujian berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateJenisRequest $request, Jenis $jenis): RedirectResponse
    {
        $jenis->update($request->validated());

        return redirect()->back()->with('success', 'Jenis Ujian berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Jenis $jenis): RedirectResponse
    {
        Gate::authorize('delete', $jenis);

        try {
            $jenis->delete();

            return redirect()->back()->with('success', 'Jenis Ujian berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Jenis Ujian tidak dapat dihapus karena sedang digunakan.');
        }
    }
}
