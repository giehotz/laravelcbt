<?php

namespace App\Http\Controllers;

use App\Models\Master\Kelas;
use App\Models\Post;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PengumumanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $query = Post::with('user');

        // Access Control:
        // Admin/Operator/Kepsek can see everything.
        // Guru/Siswa can only see posts targeted to them.
        if ($user->hasRole('siswa')) {
            $kelasId = $user->siswa->kelasAktif?->kelas_id;

            $query->where(function ($q) use ($kelasId) {
                $q->whereJsonContains('kepada->type', 'all')
                    ->orWhereJsonContains('kepada->type', 'siswa');

                if ($kelasId) {
                    // if target is specific kelas
                    $q->orWhere(function ($sub) use ($kelasId) {
                        $sub->whereJsonContains('kepada->type', 'kelas')
                            ->whereJsonContains('kepada->ids', $kelasId);
                    });
                }
            });
        } elseif ($user->hasRole('guru')) {
            $query->where(function ($q) {
                $q->whereJsonContains('kepada->type', 'all')
                    ->orWhereJsonContains('kepada->type', 'guru');
            });
        }

        $posts = $query->latest()->paginate(10);

        return Inertia::render('Pengumuman/Index', [
            'posts' => $posts,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Post::class); // Ensure we have policy later or remove if simple role check

        $kelas = Kelas::select('id', 'nama')->get();

        return Inertia::render('Pengumuman/Form', [
            'kelas' => $kelas,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'kepada.type' => 'required|string|in:all,siswa,guru,kelas',
            'kepada.ids' => 'required_if:kepada.type,kelas|array',
            'text' => 'required|string',
        ]);

        Post::create([
            'dari_user_id' => $request->user()->id,
            'dari_group' => $request->user()->roles->first()?->id ?? null,
            'kepada' => $validatedData['kepada'],
            'text' => $validatedData['text'],
        ]);

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil dibuat.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $pengumuman)
    {
        $pengumuman->delete();

        return redirect()->route('pengumuman.index')->with('success', 'Pengumuman berhasil dihapus.');
    }
}
