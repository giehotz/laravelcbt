<?php

namespace App\Http\Controllers\Cbt;

use App\Http\Controllers\Controller;
use App\Models\Cbt\Sesi;
use App\Http\Requests\Cbt\StoreSesiRequest;
use App\Http\Requests\Cbt\UpdateSesiRequest;
use App\Http\Resources\Cbt\SesiResource;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class SesiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        Gate::authorize('viewAny', Sesi::class);

        $sesi = Sesi::latest()->paginate(10);

        return Inertia::render('Cbt/Sesi/Index', [
            'sesi' => SesiResource::collection($sesi),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSesiRequest $request): RedirectResponse
    {
        Sesi::create($request->validated());

        return redirect()->back()->with('success', 'Sesi Ujian berhasil ditambahkan.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSesiRequest $request, Sesi $sesi): RedirectResponse
    {
        // Prevent disabling if it's currently used in cbt_kelas_ruang or cbt_sesi_siswa (if those exist)
        // Since we are building 3.1, those tables might not have models yet, but we can query them directly if they exist, or just rely on foreign key constraints.
        // Actually, aktif = false doesn't violate FK, so we should check manually.
        if ($sesi->aktif && !$request->boolean('aktif')) {
            if (\Illuminate\Support\Facades\Schema::hasTable('cbt_sesi_siswa')) {
                $isUsed = DB::table('cbt_sesi_siswa')->where('sesi_id', $sesi->id)->exists();
                if ($isUsed) {
                    return redirect()->back()->with('error', 'Sesi tidak dapat dinonaktifkan karena sedang digunakan oleh peserta ujian.');
                }
            }
        }

        $data = $request->validated();
        $data['aktif'] = $request->boolean('aktif'); // Ensure it's casted for updating
        $sesi->update($data);

        return redirect()->back()->with('success', 'Sesi Ujian berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sesi $sesi): RedirectResponse
    {
        Gate::authorize('delete', $sesi);

        try {
            $sesi->delete();
            return redirect()->back()->with('success', 'Sesi Ujian berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->back()->with('error', 'Sesi tidak dapat dihapus karena sudah memiliki relasi data (jadwal/peserta).');
        }
    }
}
