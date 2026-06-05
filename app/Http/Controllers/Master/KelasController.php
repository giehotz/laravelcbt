<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\Master\Kelas;
use App\Models\Master\LevelKelas;
use App\Models\Master\Jurusan;
use App\Models\Master\Guru;
use App\Models\Master\Siswa;
use App\Models\TahunPelajaran;
use App\Models\Semester;
use App\Http\Requests\StoreKelasRequest;
use App\Http\Requests\UpdateKelasRequest;
use App\Http\Requests\UpdateKelasStudentsRequest;
use App\Services\KelasStudentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class KelasController extends Controller
{
    public function index(): Response
    {
        Gate::authorize('viewAny', Kelas::class);

        $kelas = Kelas::with(['levelKelas', 'jurusan', 'waliKelas', 'tahunPelajaran', 'semester'])
            ->latest()
            ->paginate(10);

        $years = TahunPelajaran::latest()->get();
        $semesters = Semester::latest()->get();
        $jurusans = Jurusan::active()->get();
        $levels = LevelKelas::latest()->get();
        $gurus = Guru::latest()->get();

        return Inertia::render('Master/Kelas/Index', [
            'kelas' => $kelas,
            'years' => $years,
            'semesters' => $semesters,
            'jurusans' => $jurusans,
            'levels' => $levels,
            'gurus' => $gurus,
        ]);
    }

    public function store(StoreKelasRequest $request): RedirectResponse
    {
        Gate::authorize('create', Kelas::class);

        Kelas::create($request->validated());

        return redirect()->route('master.kelas.index')
            ->with('success', 'Rombel/Kelas berhasil ditambahkan.');
    }

    public function update(UpdateKelasRequest $request, Kelas $kelas): RedirectResponse
    {
        Gate::authorize('update', $kelas);

        $kelas->update($request->validated());

        return redirect()->route('master.kelas.index')
            ->with('success', 'Rombel/Kelas berhasil diperbarui.');
    }

    public function destroy(Kelas $kelas): RedirectResponse
    {
        Gate::authorize('delete', $kelas);

        if ($kelas->siswa()->exists()) {
            return redirect()->route('master.kelas.index')
                ->with('error', 'Kelas tidak dapat dihapus karena memiliki siswa terdaftar.');
        }

        $kelas->delete();

        return redirect()->route('master.kelas.index')
            ->with('success', 'Rombel/Kelas berhasil dihapus.');
    }

    /**
     * Show the form for managing students in the class.
     */
    public function editStudents(Kelas $kelas): Response
    {
        Gate::authorize('manageStudents', $kelas);

        $tpId = $kelas->tahun_pelajaran_id;
        $smtId = $kelas->semester_id;
        $search = request('q');

        // 1. Get students currently assigned to this class for the period
        $assigned = $kelas->siswa()
            ->wherePivot('tahun_pelajaran_id', $tpId)
            ->wherePivot('semester_id', $smtId)
            ->get();

        // 2. Query students not assigned to ANY class for the period
        $unassignedQuery = Siswa::whereDoesntHave('kelasSiswa', function ($q) use ($tpId, $smtId) {
            $q->where('tahun_pelajaran_id', $tpId)->where('semester_id', $smtId);
        });

        if ($search) {
            $unassignedQuery->where(function ($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%")
                  ->orWhere('nisn', 'like', "%{$search}%");
            });
        }

        $unassigned = $unassignedQuery->paginate(20)->withQueryString();

        return Inertia::render('Master/Kelas/ManageStudents', [
            'kelas' => $kelas->load(['levelKelas', 'jurusan', 'waliKelas', 'tahunPelajaran', 'semester']),
            'assigned' => $assigned,
            'unassigned' => $unassigned,
            'filters' => [
                'q' => $search,
            ],
        ]);
    }

    /**
     * Update student roster for the class.
     */
    public function updateStudents(
        UpdateKelasStudentsRequest $request,
        Kelas $kelas,
        KelasStudentService $service
    ): RedirectResponse {
        // FormRequest handles the authorize gate check

        try {
            $service->syncStudents(
                $kelas,
                $request->input('siswa_ids', []),
                $kelas->tahun_pelajaran_id,
                $kelas->semester_id
            );
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('master.kelas.index')
            ->with('success', 'Anggota rombel/kelas berhasil disimpan.');
    }
}
