<?php

namespace App\Http\Controllers;

use App\Models\Cbt\BankSoal;
use App\Models\Cbt\Jadwal;
use App\Models\Master\Guru;
use App\Models\Master\Siswa;
use App\Models\Master\Kelas;
use App\Models\Master\Mapel;
use App\Models\Master\Ekstra;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $tp   = TahunPelajaran::where('active', true)->first();
        $smt  = Semester::where('active', true)->first();

        // Safety fallback if no active academic year
        if (!$tp || !$smt) {
            return Inertia::render('Dashboard', [
                'serverTime' => now()->toIsoString(),
                'tp'         => null,
                'smt'        => null,
                'stats'      => [],
                'jadwalHariIni' => [],
            ]);
        }

        $data = match(true) {
            $user->hasRole('superadmin') => $this->dataAdmin($tp, $smt),
            $user->hasRole('operator')   => $this->dataOperator($tp, $smt),
            $user->hasRole('guru')       => $this->dataGuru($user, $tp, $smt),
            $user->hasRole('proktor')    => $this->dataProktor($tp, $smt),
            $user->hasRole('kepsek')     => $this->dataKepsek($tp, $smt),
            default => ['stats' => []],
        };

        // All roles need "Jadwal Hari Ini" except maybe students if they had access
        $jadwalHariIni = $this->jadwalHariIni($tp, $smt, $user);

        return Inertia::render('Dashboard', array_merge($data, [
            'serverTime' => now()->toIsoString(),
            'tp'         => $tp->only('id', 'tahun'),
            'smt'        => $smt->only('id', 'nama_smt'),
            'jadwalHariIni' => $jadwalHariIni,
        ]));
    }

    private function dataAdmin(TahunPelajaran $tp, Semester $smt): array
    {
        $stats = Cache::remember("dashboard_admin_{$tp->id}_{$smt->id}", 300, fn() => [
            'total_siswa' => Siswa::count(),
            'total_guru'  => Guru::count(),
            'total_rombel'=> Kelas::where('tahun_pelajaran_id', $tp->id)->where('semester_id', $smt->id)->count(),
            'total_wali'  => Kelas::where('tahun_pelajaran_id', $tp->id)->where('semester_id', $smt->id)->whereNotNull('guru_id')->distinct()->count('guru_id'),
            'total_mapel' => Mapel::count(),
            'total_ekstra'=> Ekstra::count(),
            'total_bank'  => BankSoal::where('tahun_pelajaran_id', $tp->id)->count(),
        ]);

        // Realtime, not cached
        $stats['jadwal_aktif_sekarang'] = Jadwal::aktifSekarang()->count();

        $grafik = $this->grafikData($tp, $smt);

        return ['stats' => $stats, 'grafik' => $grafik];
    }

    private function dataOperator(TahunPelajaran $tp, Semester $smt): array
    {
        $stats = Cache::remember("dashboard_operator_{$tp->id}_{$smt->id}", 300, fn() => [
            'total_siswa' => Siswa::count(),
            'total_guru'  => Guru::count(),
            'total_bank'  => BankSoal::where('tahun_pelajaran_id', $tp->id)->count(),
        ]);

        $stats['jadwal_aktif_sekarang'] = Jadwal::aktifSekarang()->count();
        return ['stats' => $stats];
    }

    private function dataGuru($user, TahunPelajaran $tp, Semester $smt): array
    {
        $guruId = $user->guru?->id;
        
        $stats = Cache::remember("dashboard_guru_{$guruId}_{$tp->id}_{$smt->id}", 300, fn() => [
            'total_bank_saya' => BankSoal::where('guru_id', $guruId)->where('tahun_pelajaran_id', $tp->id)->count(),
            'total_soal_saya' => \App\Models\Cbt\Soal::whereHas('bank', function($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })->count(),
        ]);

        $stats['jadwal_aktif_saya'] = Jadwal::aktifSekarang()
            ->whereHas('bankSoal', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            })->count();

        return ['stats' => $stats];
    }

    private function dataProktor(TahunPelajaran $tp, Semester $smt): array
    {
        $stats = Cache::remember("dashboard_proktor_{$tp->id}_{$smt->id}", 300, fn() => [
            'total_siswa' => Siswa::count(),
            'jadwal_hari_ini' => Jadwal::whereDate('tgl_mulai', '<=', today())
                                       ->whereDate('tgl_selesai', '>=', today())
                                       ->count(),
        ]);

        $stats['jadwal_aktif_sekarang'] = Jadwal::aktifSekarang()->count();
        return ['stats' => $stats];
    }

    private function dataKepsek(TahunPelajaran $tp, Semester $smt): array
    {
        $stats = Cache::remember("dashboard_kepsek_{$tp->id}_{$smt->id}", 300, fn() => [
            'total_siswa' => Siswa::count(),
            'total_guru'  => Guru::count(),
            'total_rombel'=> Kelas::where('tahun_pelajaran_id', $tp->id)->where('semester_id', $smt->id)->count(),
            'total_wali'  => Kelas::where('tahun_pelajaran_id', $tp->id)->where('semester_id', $smt->id)->whereNotNull('guru_id')->distinct()->count('guru_id'),
            'total_mapel' => Mapel::count(),
            'total_ekstra'=> Ekstra::count(),
        ]);

        $stats['jadwal_aktif_sekarang'] = Jadwal::aktifSekarang()->count();
        
        $grafik = $this->grafikData($tp, $smt);

        return ['stats' => $stats, 'grafik' => $grafik];
    }

    private function jadwalHariIni(TahunPelajaran $tp, Semester $smt, $user)
    {
        $start = now()->startOfDay()->toDateTimeString();
        $end   = now()->endOfDay()->toDateTimeString();

        $query = Jadwal::with(['bankSoal.mapel', 'jenis'])
            ->where('tahun_pelajaran_id', $tp->id)
            ->where('semester_id', $smt->id)
            ->where('tgl_mulai', '<=', $end)
            ->where('tgl_selesai', '>=', $start)
            ->orderBy('tgl_mulai');

        if ($user->hasRole('guru') && !$user->hasRole(['superadmin', 'operator'])) {
            $guruId = $user->guru?->id;
            $query->whereHas('bankSoal', function ($q) use ($guruId) {
                $q->where('guru_id', $guruId);
            });
        }

        return $query->get();
    }

    private function grafikData(TahunPelajaran $tp, Semester $smt): array
    {
        return Cache::remember("dashboard_grafik_{$tp->id}_{$smt->id}", 1800, function () use ($tp, $smt) {
            // Grafik 1 — Rata-rata Nilai per Mata Pelajaran (Bar Chart)
            $distribusiNilai = DB::table('cbt_nilai')
                ->join('cbt_jadwal', 'cbt_nilai.jadwal_id', '=', 'cbt_jadwal.id')
                ->join('cbt_bank_soal', 'cbt_jadwal.bank_id', '=', 'cbt_bank_soal.id')
                ->join('mapel', 'cbt_bank_soal.mapel_id', '=', 'mapel.id')
                ->where('cbt_jadwal.tahun_pelajaran_id', $tp->id)
                ->where('cbt_jadwal.semester_id', $smt->id)
                ->groupBy('mapel.id', 'mapel.nama_mapel')
                ->select(
                    'mapel.nama_mapel',
                    DB::raw('ROUND(AVG(pg_nilai + esai_nilai + kompleks_nilai + jodohkan_nilai + isian_nilai), 1) as rata_rata'),
                    DB::raw('COUNT(*) as total_peserta')
                )
                ->get();

            // Grafik 2 — Status Peserta Ujian (Donut Chart)
            $statusPeserta = DB::table('cbt_durasi_siswa')
                ->join('cbt_jadwal', 'cbt_durasi_siswa.jadwal_id', '=', 'cbt_jadwal.id')
                ->where('cbt_jadwal.tahun_pelajaran_id', $tp->id)
                ->where('cbt_jadwal.semester_id', $smt->id)
                ->selectRaw('cbt_durasi_siswa.status, COUNT(*) as total')
                ->groupBy('cbt_durasi_siswa.status')
                ->get()
                ->mapWithKeys(function ($row) {
                    $label = match ((int)$row->status) {
                        0 => 'Belum Ujian',
                        1 => 'Sedang Ujian',
                        2 => 'Selesai Ujian',
                        default => 'Lainnya'
                    };
                    return [$label => $row->total];
                });

            // Grafik 3 — Jumlah Ujian per Bulan (Line Chart)
            $trenUjian = DB::table('cbt_jadwal')
                ->where('tahun_pelajaran_id', $tp->id)
                ->selectRaw("SUBSTRING(tgl_mulai, 1, 7) as bulan, COUNT(*) as total")
                ->groupByRaw("SUBSTRING(tgl_mulai, 1, 7)")
                ->orderBy('bulan')
                ->get();

            return [
                'distribusi_nilai' => $distribusiNilai,
                'status_peserta'   => $statusPeserta,
                'tren_ujian'       => $trenUjian,
            ];
        });
    }
}
