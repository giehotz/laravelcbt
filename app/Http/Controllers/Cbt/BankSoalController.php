<?php

namespace App\Http\Controllers\Cbt;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cbt\StoreBankSoalRequest;
use App\Models\Cbt\BankSoal;
use App\Models\Cbt\Jenis;
use App\Models\Master\Guru;
use App\Models\Master\Jurusan;
use App\Models\Master\Kelas;
use App\Models\Master\LevelKelas;
use App\Models\Master\Mapel;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use App\Services\GuruAssignmentService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BankSoalController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', BankSoal::class);

        $tpAktif = TahunPelajaran::where('active', true)->first();
        $smtAktif = Semester::where('active', true)->first();

        $bankSoals = BankSoal::visibleBy($request->user())
            ->where('tahun_pelajaran_id', $tpAktif?->id)
            ->where('semester_id', $smtAktif?->id)
            ->with(['jenis', 'mapel', 'guru'])
            ->orderBy('id', 'desc')
            ->paginate(15);

        return Inertia::render('Cbt/BankSoal/Index', [
            'bankSoals' => $bankSoals,
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', BankSoal::class);
        $user = $request->user();
        $assignmentService = app(GuruAssignmentService::class);

        $mapels = $user->hasRole('superadmin|operator|kepsek|proktor')
            ? Mapel::all(['id', 'nama_mapel'])
            : ($user->guru ? $assignmentService->allowedMapels($user->guru) : collect());

        return Inertia::render('Cbt/BankSoal/Form', [
            'bankSoal' => null,
            'jenis' => Jenis::all(['id', 'nama_jenis']),
            'mapel' => $mapels,
            'jurusan' => Jurusan::all(['id', 'nama_jurusan']),
            'kelas' => Kelas::orderBy('nama_kelas')->get(['id', 'nama_kelas', 'level_id']),
            'levelKelas' => LevelKelas::all(['id', 'level']),
            'isGuru' => $user->hasRole('guru'),
            'guruAssignments' => ($user->hasRole('guru') && $user->guru) ? $user->guru->guruMapelKelas()->with('kelas:id')->get() : [],
            'gurus' => $user->hasRole('guru') ? [] : Guru::all(['id', 'nama_guru']),
        ]);
    }

    public function store(StoreBankSoalRequest $request): RedirectResponse
    {
        $this->authorize('create', BankSoal::class);
        $data = $request->validated();
        if ($request->user()->hasRole('guru')) {
            $data['guru_id'] = $request->user()->guru?->id;
        }
        $data['tahun_pelajaran_id'] = TahunPelajaran::where('active', true)->first()->id;
        $data['semester_id'] = Semester::where('active', true)->first()->id;

        BankSoal::create($data);

        return redirect()->route('cbt.bank-soal.index')->with('success', 'Bank Soal berhasil dibuat.');
    }

    public function edit(Request $request, BankSoal $bankSoal): Response
    {
        $this->authorize('update', $bankSoal);
        $user = $request->user();
        $assignmentService = app(GuruAssignmentService::class);

        $mapels = $user->hasRole('superadmin|operator|kepsek|proktor')
            ? Mapel::all(['id', 'nama_mapel'])
            : ($user->guru ? $assignmentService->allowedMapels($user->guru) : collect());

        return Inertia::render('Cbt/BankSoal/Form', [
            'bankSoal' => $bankSoal,
            'jenis' => Jenis::all(['id', 'nama_jenis']),
            'mapel' => $mapels,
            'jurusan' => Jurusan::all(['id', 'nama_jurusan']),
            'kelas' => Kelas::orderBy('nama_kelas')->get(['id', 'nama_kelas', 'level_id']),
            'levelKelas' => LevelKelas::all(['id', 'level']),
            'isGuru' => $user->hasRole('guru'),
            'guruAssignments' => ($user->hasRole('guru') && $user->guru) ? $user->guru->guruMapelKelas()->with('kelas:id')->get() : [],
            'gurus' => $user->hasRole('guru') ? [] : Guru::all(['id', 'nama_guru']),
        ]);
    }

    public function update(StoreBankSoalRequest $request, BankSoal $bankSoal): RedirectResponse
    {
        $this->authorize('update', $bankSoal);
        $data = $request->validated();
        if ($request->user()->hasRole('guru')) {
            $data['guru_id'] = $request->user()->guru?->id;
        }
        $bankSoal->update($data);

        return redirect()->route('cbt.bank-soal.index')->with('success', 'Bank Soal berhasil diperbarui.');
    }

    public function destroy(BankSoal $bankSoal): RedirectResponse
    {
        $this->authorize('delete', $bankSoal);
        $bankSoal->delete();

        return redirect()->back()->with('success', 'Bank Soal berhasil dihapus.');
    }
}
