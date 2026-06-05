<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Mapel;
use App\Http\Requests\StoreMapelRequest;
use App\Http\Requests\UpdateMapelRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class MapelController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Mapel::class);

        $mapels = Mapel::latest()->get();

        return Inertia::render('Master/Mapel/Index', [
            'mapels' => $mapels,
        ]);
    }

    public function store(StoreMapelRequest $request): RedirectResponse
    {
        Gate::authorize('create', Mapel::class);

        Mapel::create($request->validated());

        return redirect()->route('master.mapel.index')
            ->with('success', 'Mata pelajaran berhasil ditambahkan.');
    }

    public function update(UpdateMapelRequest $request, Mapel $mapel): RedirectResponse
    {
        Gate::authorize('update', $mapel);

        $mapel->update($request->validated());

        return redirect()->route('master.mapel.index')
            ->with('success', 'Mata pelajaran berhasil diperbarui.');
    }

    public function destroy(Mapel $mapel): RedirectResponse
    {
        Gate::authorize('delete', $mapel);

        // Deletability check
        if (!$mapel->deletable) {
            return redirect()->route('master.mapel.index')
                ->with('error', 'Mata pelajaran ini tidak dapat dihapus.');
        }

        $mapel->delete();

        return redirect()->route('master.mapel.index')
            ->with('success', 'Mata pelajaran berhasil dihapus.');
    }
}
