<?php

namespace App\Http\Controllers\Cbt;

use App\Http\Controllers\Controller;
use App\Models\Cbt\Jadwal;
use App\Models\Cbt\Nilai;
use App\Models\CbtKopAbsensi;
use App\Models\CbtKopBerita;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class ReportController extends Controller
{
    /**
     * Halaman Utama Menu Cetak Laporan
     */
    public function index()
    {
        $jadwals = Jadwal::with(['bankSoal'])->orderBy('tgl_mulai', 'desc')->get();

        return Inertia::render('Cbt/Guru/Report', [
            'jadwals' => $jadwals,
        ]);
    }

    /**
     * Cetak Daftar Hadir
     */
    public function cetakDaftarHadir(Request $request)
    {
        $jadwalId = $request->query('jadwal_id');
        $jadwal = Jadwal::with('bankSoal')->find($jadwalId);

        if (! $jadwal) {
            abort(404, 'Jadwal Ujian tidak ditemukan atau belum dipilih.');
        }

        $kelasIds = $jadwal->bankSoal->kelas ?? [];

        $siswas = DB::table('cbt_sesi_siswa')
            ->join('siswa', 'siswa.id', '=', 'cbt_sesi_siswa.siswa_id')
            ->leftJoin('cbt_ruang', 'cbt_ruang.id', '=', 'cbt_sesi_siswa.ruang_id')
            ->leftJoin('cbt_sesi', 'cbt_sesi.id', '=', 'cbt_sesi_siswa.sesi_id')
            ->whereIn('cbt_sesi_siswa.kelas_id', $kelasIds)
            ->where('cbt_sesi_siswa.tp_id', $jadwal->tahun_pelajaran_id)
            ->where('cbt_sesi_siswa.smt_id', $jadwal->semester_id)
            ->select('siswa.*', 'cbt_ruang.nama_ruang', 'cbt_sesi.nama_sesi')
            ->orderBy('cbt_ruang.nama_ruang')
            ->orderBy('siswa.nama')
            ->get();

        $kop = CbtKopAbsensi::first();

        return Inertia::render('Cbt/Guru/PrintAbsensi', [
            'siswas' => $siswas,
            'kop' => $kop,
            'jadwal' => $jadwal,
        ]);
    }

    /**
     * Cetak Berita Acara
     */
    public function cetakBeritaAcara(Request $request)
    {
        $jadwalId = $request->query('jadwal_id');
        $jadwal = Jadwal::with('bankSoal')->find($jadwalId);

        if (! $jadwal) {
            abort(404, 'Jadwal Ujian tidak ditemukan atau belum dipilih.');
        }

        $kelasIds = $jadwal->bankSoal->kelas ?? [];

        $ruangSesiList = DB::table('cbt_sesi_siswa')
            ->join('cbt_ruang', 'cbt_ruang.id', '=', 'cbt_sesi_siswa.ruang_id')
            ->join('cbt_sesi', 'cbt_sesi.id', '=', 'cbt_sesi_siswa.sesi_id')
            ->whereIn('cbt_sesi_siswa.kelas_id', $kelasIds)
            ->where('cbt_sesi_siswa.tp_id', $jadwal->tahun_pelajaran_id)
            ->where('cbt_sesi_siswa.smt_id', $jadwal->semester_id)
            ->select('cbt_sesi_siswa.ruang_id', 'cbt_sesi_siswa.sesi_id', 'cbt_ruang.nama_ruang', 'cbt_sesi.nama_sesi')
            ->distinct()
            ->get();

        // Dalam implementasi nyata, kita hitung jumlah peserta, hadir, tidak hadir
        foreach ($ruangSesiList as $rs) {
            $rs->peserta = DB::table('cbt_sesi_siswa')
                ->where('ruang_id', $rs->ruang_id)
                ->where('sesi_id', $rs->sesi_id)
                ->whereIn('kelas_id', $kelasIds)
                ->where('tp_id', $jadwal->tahun_pelajaran_id)
                ->where('smt_id', $jadwal->semester_id)
                ->count();
            // Estimasi hadir bisa dari durasi_siswa yang statusnya > 0
            $rs->hadir = DB::table('cbt_durasi_siswa')
                ->where('jadwal_id', $jadwalId)
                ->whereIn('siswa_id', function ($query) use ($rs, $kelasIds, $jadwal) {
                    $query->select('siswa_id')
                        ->from('cbt_sesi_siswa')
                        ->where('ruang_id', $rs->ruang_id)
                        ->where('sesi_id', $rs->sesi_id)
                        ->whereIn('kelas_id', $kelasIds)
                        ->where('tp_id', $jadwal->tahun_pelajaran_id)
                        ->where('smt_id', $jadwal->semester_id);
                })->count();
            $rs->absen = $rs->peserta - $rs->hadir;
        }

        $kop = CbtKopBerita::first();

        return Inertia::render('Cbt/Guru/PrintBerita', [
            'ruangSesiList' => $ruangSesiList,
            'kop' => $kop,
            'jadwal' => $jadwal,
        ]);
    }

    /**
     * Rekap / Ekspor Nilai
     */
    public function rekapNilai(Request $request)
    {
        $jadwalId = $request->query('jadwal_id');
        $jadwal = Jadwal::with('bankSoal')->find($jadwalId);

        if (! $jadwal) {
            abort(404, 'Jadwal Ujian tidak ditemukan atau belum dipilih.');
        }

        $nilais = DB::table('cbt_nilai')
            ->join('siswa', 'siswa.id', '=', 'cbt_nilai.siswa_id')
            ->where('cbt_nilai.jadwal_id', $jadwalId)
            ->select('cbt_nilai.*', 'siswa.nama', 'siswa.nis')
            ->orderBy('siswa.nama')
            ->get();

        // Menambahkan properti total_nilai
        $nilais = $nilais->map(function ($n) {
            $n->total_nilai = $n->pg_nilai + $n->kompleks_nilai + $n->jodohkan_nilai + $n->isian_nilai + $n->esai_nilai;

            return $n;
        });

        return Inertia::render('Cbt/Guru/RekapNilai', [
            'nilais' => $nilais,
            'jadwal' => $jadwal,
        ]);
    }
}
