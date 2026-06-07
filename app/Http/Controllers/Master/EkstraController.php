<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreEkstraRequest;
use App\Http\Requests\UpdateEkstraRequest;
use App\Models\Master\Ekstra;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class EkstraController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Ekstra::class);

        $ekstras = Ekstra::latest()->get();

        return Inertia::render('Master/Ekstra/Index', [
            'ekstras' => $ekstras,
        ]);
    }

    public function store(StoreEkstraRequest $request): RedirectResponse
    {
        Gate::authorize('create', Ekstra::class);

        Ekstra::create($request->validated());

        return redirect()->route('master.ekstra.index')
            ->with('success', 'Ekstrakurikuler berhasil ditambahkan.');
    }

    public function update(UpdateEkstraRequest $request, Ekstra $ekstra): RedirectResponse
    {
        Gate::authorize('update', $ekstra);

        $ekstra->update($request->validated());

        return redirect()->route('master.ekstra.index')
            ->with('success', 'Ekstrakurikuler berhasil diperbarui.');
    }

    public function destroy(Ekstra $ekstra): RedirectResponse
    {
        Gate::authorize('delete', $ekstra);

        $ekstra->delete();

        return redirect()->route('master.ekstra.index')
            ->with('success', 'Ekstrakurikuler berhasil dihapus.');
    }
}
