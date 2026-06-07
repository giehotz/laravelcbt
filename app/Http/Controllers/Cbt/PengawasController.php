<?php

namespace App\Http\Controllers\Cbt;

use App\Http\Controllers\Controller;
use App\Models\Cbt\Jadwal;
use App\Models\Cbt\KelasRuang;
use App\Models\Cbt\Pengawas;
use App\Models\Master\Guru;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PengawasController extends Controller
{
    public function index()
    {
        $tpAktif = TahunPelajaran::where('active', true)->firstOrFail();
        $smtAktif = Semester::where('active', true)->firstOrFail();

        $jadwals = Jadwal::with(['bankSoal.mapel', 'bankSoal.jenis'])
            ->where('tahun_pelajaran_id', $tpAktif->id)
            ->where('semester_id', $smtAktif->id)
            ->withCount('guruPengawas as pengawas_count')
            ->orderBy('tgl_mulai')
            ->get();

        return Inertia::render('Cbt/Pengawas/Index', [
            'jadwals' => $jadwals,
        ]);
    }

    public function show(Jadwal $jadwal)
    {
        $jadwal->load('bankSoal.mapel');
        $tpAktif = TahunPelajaran::where('active', true)->firstOrFail();
        $smtAktif = Semester::where('active', true)->firstOrFail();

        // Get relevant ruang & sesi based on KelasRuang
        $kelasRuangs = KelasRuang::where('tp_id', $tpAktif->id)
            ->where('smt_id', $smtAktif->id)
            ->with(['ruang', 'sesi'])
            ->get();

        $ruangs = $kelasRuangs->pluck('ruang')->unique('id')->values();
        $sesis = $kelasRuangs->pluck('sesi')->unique('id')->sortBy('urutan')->values();

        $validPairs = $kelasRuangs->map(function ($kr) {
            return $kr->ruang_id.'-'.$kr->sesi_id;
        })->unique()->values();

        $gurus = Guru::get(['id', 'nama_guru']);

        $pengawas = Pengawas::where('jadwal_id', $jadwal->id)->get();

        return Inertia::render('Cbt/Pengawas/Assign', [
            'jadwal' => $jadwal,
            'ruangs' => $ruangs,
            'sesis' => $sesis,
            'validPairs' => $validPairs,
            'gurus' => $gurus,
            'pengawas' => $pengawas,
        ]);
    }

    public function sync(Jadwal $jadwal, Request $request)
    {
        $payload = $request->validate([
            'pengawas' => 'array',
            'pengawas.*.ruang_id' => 'required|exists:cbt_ruang,id',
            'pengawas.*.sesi_id' => 'required|exists:cbt_sesi,id',
            'pengawas.*.guru_id' => 'required|exists:guru,id',
        ])['pengawas'] ?? [];

        // Validation for conflict
        foreach ($payload as $item) {
            $guruId = $item['guru_id'];
            $sesiId = $item['sesi_id'];

            // Check if this guru is already assigned to another jadwal that overlaps in time for the SAME sesi
            $konflik = Pengawas::where('guru_id', $guruId)
                ->where('sesi_id', $sesiId)
                ->whereHas('jadwal', function ($q) use ($jadwal) {
                    $q->where('id', '!=', $jadwal->id)
                        ->where('tgl_mulai', '<', $jadwal->tgl_selesai)
                        ->where('tgl_selesai', '>', $jadwal->tgl_mulai);
                })->exists();

            if ($konflik) {
                $guru = Guru::find($guruId);
                throw ValidationException::withMessages([
                    'pengawas' => "Guru {$guru->nama_guru} sudah ditugaskan sebagai pengawas pada ujian lain yang waktunya tumpang tindih.",
                ]);
            }
        }

        DB::transaction(function () use ($jadwal, $payload) {
            Pengawas::where('jadwal_id', $jadwal->id)->delete();

            foreach ($payload as $item) {
                Pengawas::create([
                    'jadwal_id' => $jadwal->id,
                    'ruang_id' => $item['ruang_id'],
                    'sesi_id' => $item['sesi_id'],
                    'guru_id' => $item['guru_id'],
                ]);
            }
        });

        return redirect()->route('cbt.pengawas.index')->with('success', 'Pengawas berhasil disimpan.');
    }
}
