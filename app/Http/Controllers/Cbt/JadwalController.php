<?php

namespace App\Http\Controllers\Cbt;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cbt\StoreJadwalRequest;
use App\Http\Requests\Cbt\UpdateJadwalRequest;
use App\Models\Cbt\BankSoal;
use App\Models\Cbt\Jadwal;
use App\Models\Cbt\Jenis;
use App\Models\TahunPelajaran;
use App\Models\Semester;
use Illuminate\Http\Request;
use Inertia\Inertia;

class JadwalController extends Controller
{
    public function index(Request $request)
    {
        $tpAktif = TahunPelajaran::where('active', true)->first();
        $smtAktif = Semester::where('active', true)->first();

        if (!$tpAktif || !$smtAktif) {
            return Inertia::render('Cbt/Jadwal/Index', [
                'jadwals' => [],
                'bankSoals' => [],
                'jenisUjians' => [],
            ]);
        }

        $query = Jadwal::with(['bankSoal', 'jenis', 'tahunPelajaran', 'semester'])
            ->where('tahun_pelajaran_id', $tpAktif->id)
            ->where('semester_id', $smtAktif->id);

        if ($request->user()->hasRole('guru') && !$request->user()->hasRole(['superadmin', 'operator'])) {
            $guruId = $request->user()->guru?->id;
            $assignmentService = app(\App\Services\GuruAssignmentService::class);
            $allowedMapels = $assignmentService->allowedMapels($request->user()->guru)->pluck('id');

            $query->whereHas('bankSoal', function ($q) use ($guruId, $allowedMapels) {
                $q->where('guru_id', $guruId)->whereIn('mapel_id', $allowedMapels);
            });
        }

        $jadwals = $query->latest()->get();

        return Inertia::render('Cbt/Jadwal/Index', [
            'jadwals' => $jadwals,
        ]);
    }

    public function create(Request $request)
    {
        $tpAktif = TahunPelajaran::where('active', true)->firstOrFail();
        $smtAktif = Semester::where('active', true)->firstOrFail();

        $bankSoalQuery = BankSoal::where('status', 1)
            ->where('tahun_pelajaran_id', $tpAktif->id)
            ->where('semester_id', $smtAktif->id);
            
        if ($request->user()->hasRole('guru') && !$request->user()->hasRole(['superadmin', 'operator'])) {
            $assignmentService = app(\App\Services\GuruAssignmentService::class);
            $allowedMapels = $assignmentService->allowedMapels($request->user()->guru)->pluck('id');
            $bankSoalQuery->where('guru_id', $request->user()->guru?->id)->whereIn('mapel_id', $allowedMapels);
        }

        $bankSoals = $bankSoalQuery->get(['id', 'kode', 'nama', 'jenis_id']);
        $jenisUjians = Jenis::get(['id', 'nama_jenis', 'kode_jenis']);

        return Inertia::render('Cbt/Jadwal/Form', [
            'bankSoals' => $bankSoals,
            'jenisUjians' => $jenisUjians,
        ]);
    }

    public function store(StoreJadwalRequest $request)
    {
        $tpAktif = TahunPelajaran::where('active', true)->firstOrFail();
        $smtAktif = Semester::where('active', true)->firstOrFail();

        $data = $request->validated();
        $data['tahun_pelajaran_id'] = $tpAktif->id;
        $data['semester_id'] = $smtAktif->id;
        
        // Append default times since user only inputs date
        if (isset($data['tgl_mulai'])) {
            $data['tgl_mulai'] = $data['tgl_mulai'] . ' 00:00:00';
        }
        if (isset($data['tgl_selesai'])) {
            $data['tgl_selesai'] = $data['tgl_selesai'] . ' 23:59:59';
        }

        Jadwal::create($data);

        return redirect()->route('cbt.jadwal.index')->with('success', 'Jadwal ujian berhasil ditambahkan.');
    }

    public function edit(Jadwal $jadwal, Request $request)
    {
        $tpAktif = TahunPelajaran::where('active', true)->firstOrFail();
        $smtAktif = Semester::where('active', true)->firstOrFail();

        $bankSoalQuery = BankSoal::where('tahun_pelajaran_id', $tpAktif->id)
            ->where('semester_id', $smtAktif->id);
            
        if ($request->user()->hasRole('guru') && !$request->user()->hasRole(['superadmin', 'operator'])) {
            $assignmentService = app(\App\Services\GuruAssignmentService::class);
            $allowedMapels = $assignmentService->allowedMapels($request->user()->guru)->pluck('id');
            $bankSoalQuery->where('guru_id', $request->user()->guru?->id)->whereIn('mapel_id', $allowedMapels);
        }

        $bankSoals = $bankSoalQuery->get(['id', 'kode', 'nama', 'jenis_id']);
        $jenisUjians = Jenis::get(['id', 'nama_jenis', 'kode_jenis']);

        return Inertia::render('Cbt/Jadwal/Form', [
            'jadwal' => $jadwal,
            'bankSoals' => $bankSoals,
            'jenisUjians' => $jenisUjians,
        ]);
    }

    public function update(UpdateJadwalRequest $request, Jadwal $jadwal)
    {
        $data = $request->validated();
        
        // Append default times since user only inputs date
        if (isset($data['tgl_mulai']) && !str_contains($data['tgl_mulai'], ' ')) {
            $data['tgl_mulai'] = $data['tgl_mulai'] . ' 00:00:00';
        }
        if (isset($data['tgl_selesai']) && !str_contains($data['tgl_selesai'], ' ')) {
            $data['tgl_selesai'] = $data['tgl_selesai'] . ' 23:59:59';
        }

        $jadwal->update($data);

        return redirect()->route('cbt.jadwal.index')->with('success', 'Jadwal ujian berhasil diperbarui.');
    }

    public function destroy(Jadwal $jadwal)
    {
        $jadwal->delete();
        return back()->with('success', 'Jadwal ujian berhasil dihapus.');
    }

    public function toggleStatus(Jadwal $jadwal)
    {
        $jadwal->update([
            'status' => $jadwal->status == 1 ? 0 : 1
        ]);

        return back()->with('success', 'Status jadwal ujian berhasil diubah.');
    }
}
