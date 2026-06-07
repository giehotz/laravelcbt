<?php

namespace App\Http\Controllers\Siswa;

use App\Http\Controllers\Controller;
use App\Models\Cbt\DurasiSiswa;
use App\Models\Cbt\Jadwal;
use App\Models\Master\KelasSiswa;
use App\Models\Pengumuman;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $siswa = auth()->user()->siswa;
        abort_unless($siswa, 403);

        $tp = TahunPelajaran::where('active', true)->first();
        $smt = Semester::where('active', true)->first();

        $kelasSiswa = KelasSiswa::with('kelas')
            ->where('siswa_id', $siswa->id)
            ->when($tp, fn ($q) => $q->where('tahun_pelajaran_id', $tp->id))
            ->when($smt, fn ($q) => $q->where('semester_id', $smt->id))
            ->first();

        return Inertia::render('Siswa/Dashboard', [
            'siswa' => [
                'nama' => $siswa->nama,
                'nis' => $siswa->nis,
                'nisn' => $siswa->nisn,
                'kelas' => $kelasSiswa?->kelas?->nama_kelas ?? '—',
                'foto' => $siswa->foto,
            ],
        ]);
    }

    public function jadwalHariIni(): JsonResponse
    {
        $siswa = auth()->user()->siswa;
        abort_unless($siswa, 403);

        $tp = TahunPelajaran::where('active', true)->first();
        $smt = Semester::where('active', true)->first();
        $today = now()->toDateString();

        $kelasSiswa = KelasSiswa::with('kelas')
            ->where('siswa_id', $siswa->id)
            ->when($tp, fn ($q) => $q->where('tahun_pelajaran_id', $tp->id))
            ->when($smt, fn ($q) => $q->where('semester_id', $smt->id))
            ->first();

        if (! $kelasSiswa) {
            return response()->json(['data' => []]);
        }

        $jadwals = Jadwal::with('bankSoal.mapel')
            ->where('tgl_mulai', '<=', $today)
            ->where('tgl_selesai', '>=', $today)
            ->get()
            ->filter(function ($jadwal) use ($kelasSiswa) {
                $kelasIds = $jadwal->bankSoal?->kelas ?? [];

                return in_array($kelasSiswa->kelas_id, $kelasIds);
            })
            ->values();

        return response()->json([
            'data' => $jadwals->map(fn ($j) => [
                'id' => $j->id,
                'nama' => $j->bankSoal?->nama ?? '—',
                'mapel' => $j->bankSoal?->mapel?->nama_mapel ?? '—',
                'tgl_mulai' => $j->tgl_mulai,
                'tgl_selesai' => $j->tgl_selesai,
                'durasi' => $j->durasi_ujian,
                'status' => $j->status,
            ]),
        ]);
    }

    public function pengumuman(): JsonResponse
    {
        $pengumuman = Pengumuman::latest()->take(5)->get();

        return response()->json([
            'data' => $pengumuman->map(fn ($p) => [
                'id' => $p->id,
                'judul' => $p->judul,
                'isi' => $p->isi,
                'created_at' => $p->created_at?->diffForHumans(),
            ]),
        ]);
    }

    public function daftarUjian(): Response
    {
        $siswa = auth()->user()->siswa;
        abort_unless($siswa, 403);

        $tp = TahunPelajaran::where('active', true)->first();
        $smt = Semester::where('active', true)->first();

        $kelasSiswa = KelasSiswa::with('kelas')
            ->where('siswa_id', $siswa->id)
            ->when($tp, fn ($q) => $q->where('tahun_pelajaran_id', $tp->id))
            ->when($smt, fn ($q) => $q->where('semester_id', $smt->id))
            ->first();

        if (! $kelasSiswa) {
            return Inertia::render('Siswa/Ujian', [
                'ujians' => [],
                'siswa' => ['kelas' => '—'],
            ]);
        }

        $ujians = Jadwal::with('bankSoal.mapel')
            ->whereHas('bankSoal', function ($q) use ($kelasSiswa) {
                $q->whereJsonContains('kelas', (int) $kelasSiswa->kelas_id);
            })
            ->orderBy('tgl_mulai', 'desc')
            ->get()
            ->map(function ($jadwal) use ($siswa) {
                $durasi = DurasiSiswa::where('siswa_id', $siswa->id)
                    ->where('jadwal_id', $jadwal->id)
                    ->first();

                $statusSiswa = 'baru';
                if ($durasi && $durasi->status == 1) {
                    $statusSiswa = 'sedang';
                } elseif ($durasi && $durasi->status == 2) {
                    $statusSiswa = 'selesai';
                }

                return [
                    'id' => $jadwal->id,
                    'nama' => $jadwal->bankSoal?->nama ?? '—',
                    'mapel' => $jadwal->bankSoal?->mapel?->nama_mapel ?? '—',
                    'tgl_mulai' => $jadwal->tgl_mulai,
                    'tgl_selesai' => $jadwal->tgl_selesai,
                    'durasi' => $jadwal->durasi_ujian,
                    'status' => $jadwal->status,
                    'status_siswa' => $statusSiswa,
                ];
            });

        return Inertia::render('Siswa/Ujian', [
            'ujians' => $ujians,
            'siswa' => ['kelas' => $kelasSiswa->kelas?->nama_kelas ?? '—'],
        ]);
    }

    public function jadwalPelajaran(): Response
    {
        $siswa = auth()->user()->siswa;
        abort_unless($siswa, 403);

        return Inertia::render('Siswa/Jadwal', [
            'siswa' => ['kelas' => $this->getKelasSiswa($siswa)?->kelas?->nama_kelas ?? '—'],
        ]);
    }

    public function tugas(): Response
    {
        $siswa = auth()->user()->siswa;
        abort_unless($siswa, 403);

        return Inertia::render('Siswa/Tugas', [
            'siswa' => ['kelas' => $this->getKelasSiswa($siswa)?->kelas?->nama_kelas ?? '—'],
        ]);
    }

    public function nilaiHasil(): Response
    {
        $siswa = auth()->user()->siswa;
        abort_unless($siswa, 403);

        return Inertia::render('Siswa/Nilai', [
            'siswa' => ['kelas' => $this->getKelasSiswa($siswa)?->kelas?->nama_kelas ?? '—'],
        ]);
    }

    public function absensi(): Response
    {
        $siswa = auth()->user()->siswa;
        abort_unless($siswa, 403);

        return Inertia::render('Siswa/Absensi', [
            'siswa' => ['kelas' => $this->getKelasSiswa($siswa)?->kelas?->nama_kelas ?? '—'],
        ]);
    }

    public function catatanGuru(): Response
    {
        $siswa = auth()->user()->siswa;
        abort_unless($siswa, 403);

        return Inertia::render('Siswa/CatatanGuru', [
            'siswa' => ['kelas' => $this->getKelasSiswa($siswa)?->kelas?->nama_kelas ?? '—'],
        ]);
    }

    private function getKelasSiswa($siswa)
    {
        $tp = TahunPelajaran::where('active', true)->first();
        $smt = Semester::where('active', true)->first();

        return KelasSiswa::with('kelas')
            ->where('siswa_id', $siswa->id)
            ->when($tp, fn ($q) => $q->where('tahun_pelajaran_id', $tp->id))
            ->when($smt, fn ($q) => $q->where('semester_id', $smt->id))
            ->first();
    }
}
