<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Jurusan;
use App\Models\Master\Mapel;
use App\Http\Requests\StoreJurusanRequest;
use App\Http\Requests\UpdateJurusanRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class JurusanController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Jurusan::class);

        $jurusans = Jurusan::latest()->get();
        $mapels = Mapel::active()->get();

        return Inertia::render('Master/Jurusan/Index', [
            'jurusans' => $jurusans,
            'mapels' => $mapels,
        ]);
    }

    public function store(StoreJurusanRequest $request): RedirectResponse
    {
        Gate::authorize('create', Jurusan::class);

        Jurusan::create($request->validated());

        return redirect()->route('master.jurusan.index')
            ->with('success', 'Jurusan berhasil ditambahkan.');
    }

    public function update(UpdateJurusanRequest $request, Jurusan $jurusan): RedirectResponse
    {
        Gate::authorize('update', $jurusan);

        $jurusan->update($request->validated());

        return redirect()->route('master.jurusan.index')
            ->with('success', 'Jurusan berhasil diperbarui.');
    }

    public function destroy(Jurusan $jurusan): RedirectResponse
    {
        Gate::authorize('delete', $jurusan);

        // Deletability check
        if (!$jurusan->deletable) {
            return redirect()->route('master.jurusan.index')
                ->with('error', 'Jurusan ini tidak dapat dihapus.');
        }

        if ($jurusan->kelas()->exists()) {
            return redirect()->route('master.jurusan.index')
                ->with('error', 'Jurusan tidak dapat dihapus karena sedang digunakan pada kelas.');
        }

        $jurusan->delete();

        return redirect()->route('master.jurusan.index')
            ->with('success', 'Jurusan berhasil dihapus.');
    }
}
