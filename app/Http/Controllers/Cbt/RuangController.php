<?php

namespace App\Http\Controllers\Cbt;

use App\Http\Controllers\Controller;
use App\Models\Cbt\Ruang;
use App\Http\Requests\Cbt\StoreRuangRequest;
use App\Http\Requests\Cbt\UpdateRuangRequest;
use App\Http\Resources\Cbt\RuangResource;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class RuangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        Gate::authorize('viewAny', Ruang::class);

        $ruang = Ruang::latest()->paginate(10);

        return Inertia::render('Cbt/Ruang/Index', [
            'ruang' => RuangResource::collection($ruang),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRuangRequest $request): RedirectResponse
    {
        Ruang::create($request->validated());

        return redirect()->back()->with('success', 'Ruang Ujian berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRuangRequest $request, Ruang $ruang): RedirectResponse
    {
        $ruang->update($request->validated());

        return redirect()->back()->with('success', 'Ruang Ujian berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ruang $ruang): RedirectResponse
    {
        Gate::authorize('delete', $ruang);

        try {
            $ruang->delete();
            return redirect()->back()->with('success', 'Ruang Ujian berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->with('error', 'Ruang tidak dapat dihapus karena sudah memiliki relasi data.');
        }
    }
}
