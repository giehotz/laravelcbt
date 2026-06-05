<?php

namespace App\Http\Controllers\Lms;

use App\Http\Controllers\Controller;
use App\Models\Materi;
use App\Models\Master\Kelas;
use App\Models\Master\Mapel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class MateriController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = Materi::with(['guru', 'mapel']);

        if ($user->hasRole('siswa')) {
            $kelasId = $user->siswa->kelasAktif?->kelas_id;
            if ($kelasId) {
                // Scope query for student
                $query->whereJsonContains('kelas', $kelasId);
            } else {
                // Return empty if student has no active class
                $query->where('id', 0);
            }
        } elseif ($user->hasRole('guru')) {
            // Guru only sees their own materi
            $query->where('guru_id', $user->guru->id);
        }

        $materi = $query->latest()->paginate(10);

        return Inertia::render('Lms/Materi/Index', [
            'materi' => $materi
        ]);
    }

    public function create(Request $request)
    {
        $kelas = Kelas::select('id', 'nama')->get();
        $mapel = Mapel::select('id', 'nama_mapel')->get();

        return Inertia::render('Lms/Materi/Form', [
            'kelas' => $kelas,
            'mapel' => $mapel,
        ]);
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'judul' => 'required|string|max:150',
            'deskripsi' => 'required|string',
            'mapel_id' => 'required|exists:mapel,id',
            'kelas' => 'required|array',
            'youtube' => 'nullable|url|regex:/^https?:\/\/(www\.)?(youtube\.com|youtu\.be)\/.+/',
            'file.*' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,png,mp4,mp3|max:51200',
        ]);

        $filePaths = [];

        if ($request->hasFile('file')) {
            foreach ($request->file('file') as $file) {
                $path = $file->store('materi', 'public');
                $filePaths[] = $path;
            }
        }

        Materi::create([
            'guru_id' => $request->user()->guru->id,
            'mapel_id' => $validatedData['mapel_id'],
            'judul' => $validatedData['judul'],
            'deskripsi' => $validatedData['deskripsi'],
            'kelas' => $validatedData['kelas'],
            'youtube' => $validatedData['youtube'] ?? null,
            'file' => $filePaths,
        ]);

        return redirect()->route('lms.materi.index')->with('success', 'Materi berhasil ditambahkan.');
    }

    public function show(Materi $materi, Request $request)
    {
        $materi->load(['guru', 'mapel']);
        
        $hasRead = false;
        if ($request->user()->hasRole('siswa')) {
            $hasRead = DB::table('log_materi')
                ->where('siswa_id', $request->user()->siswa->id)
                ->where('materi_id', $materi->id)
                ->exists();
        }

        return Inertia::render('Lms/Materi/Show', [
            'materi' => $materi,
            'hasRead' => $hasRead
        ]);
    }

    public function edit(Materi $materi)
    {
        $kelas = Kelas::select('id', 'nama')->get();
        $mapel = Mapel::select('id', 'nama_mapel')->get();

        return Inertia::render('Lms/Materi/Form', [
            'materi' => $materi,
            'kelas' => $kelas,
            'mapel' => $mapel,
        ]);
    }

    public function update(Request $request, Materi $materi)
    {
        $validatedData = $request->validate([
            'judul' => 'required|string|max:150',
            'deskripsi' => 'required|string',
            'mapel_id' => 'required|exists:mapel,id',
            'kelas' => 'required|array',
            'youtube' => 'nullable|url|regex:/^https?:\/\/(www\.)?(youtube\.com|youtu\.be)\/.+/',
            'file.*' => 'nullable|file|mimes:pdf,doc,docx,ppt,pptx,xls,xlsx,jpg,png,mp4,mp3|max:51200',
            'existing_files' => 'nullable|array',
        ]);

        $filePaths = $validatedData['existing_files'] ?? [];
        
        if ($request->hasFile('file')) {
            foreach ($request->file('file') as $file) {
                $path = $file->store('materi', 'public');
                $filePaths[] = $path; // Append new files
            }
        }

        $oldFiles = $materi->getOriginal('file') ?? [];

        $materi->update([
            'mapel_id' => $validatedData['mapel_id'],
            'judul' => $validatedData['judul'],
            'deskripsi' => $validatedData['deskripsi'],
            'kelas' => $validatedData['kelas'],
            'youtube' => $validatedData['youtube'] ?? null,
            'file' => array_values($filePaths),
        ]);

        // Find deleted files and remove them from storage after DB update
        foreach ($oldFiles as $oldFile) {
            if (!in_array($oldFile, $filePaths)) {
                Storage::disk('public')->delete($oldFile);
            }
        }

        return redirect()->route('lms.materi.index')->with('success', 'Materi berhasil diperbarui.');
    }

    public function destroy(Materi $materi)
    {
        if (is_array($materi->file)) {
            foreach ($materi->file as $file) {
                Storage::disk('public')->delete($file);
            }
        }

        $materi->delete();

        return redirect()->route('lms.materi.index')->with('success', 'Materi berhasil dihapus.');
    }

    public function logBaca(Request $request, Materi $materi)
    {
        $user = $request->user();
        if ($user->hasRole('siswa')) {
            DB::table('log_materi')->updateOrInsert(
                [
                    'siswa_id' => $user->siswa->id,
                    'materi_id' => $materi->id,
                ],
                [
                    'created_at' => now(),
                    'updated_at' => now()
                ]
            );
        }

        return response()->json(['success' => true]);
    }
}
