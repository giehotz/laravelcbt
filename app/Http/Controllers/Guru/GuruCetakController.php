<?php

namespace App\Http\Controllers\Guru;

use App\Http\Controllers\Controller;
use App\Models\Cbt\Jadwal;
use App\Models\CbtKopAbsensi;
use App\Models\CbtKopBerita;
use App\Models\CbtKopKartu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GuruCetakController extends Controller
{
    public function index()
    {
        $guru = auth()->user()->guru;
        abort_unless($guru, 403);

        $jadwals = Jadwal::with('bankSoal')
            ->whereHas('bankSoal', fn ($q) => $q->where('guru_id', $guru->id))
            ->orderBy('tgl_mulai', 'desc')
            ->get();

        return Inertia::render('Guru/Cetak/Index', [
            'jadwals' => $jadwals,
        ]);
    }

    public function kartu(Request $request)
    {
        $jadwal = $this->resolveJadwal($request);
        $siswas = DB::table('cbt_sesi_siswa')
            ->join('siswa', 'siswa.id', '=', 'cbt_sesi_siswa.siswa_id')
            ->whereIn('cbt_sesi_siswa.kelas_ruang_id', function ($query) use ($jadwal) {
                $query->select('id')->from('cbt_kelas_ruang')->where('jadwal_id', $jadwal->id);
            })
            ->select('siswa.*')
            ->get();

        return Inertia::render('Cbt/Guru/PrintKartu', [
            'siswas' => $siswas,
            'kop' => CbtKopKartu::first(),
            'jadwal' => $jadwal,
        ]);
    }

    public function daftarHadir(Request $request)
    {
        $jadwal = $this->resolveJadwal($request);
        $siswas = DB::table('cbt_sesi_siswa')
            ->join('siswa', 'siswa.id', '=', 'cbt_sesi_siswa.siswa_id')
            ->leftJoin('cbt_kelas_ruang', 'cbt_kelas_ruang.id', '=', 'cbt_sesi_siswa.kelas_ruang_id')
            ->leftJoin('cbt_ruang', 'cbt_ruang.id', '=', 'cbt_kelas_ruang.ruang_id')
            ->leftJoin('cbt_sesi', 'cbt_sesi.id', '=', 'cbt_kelas_ruang.sesi_id')
            ->where('cbt_kelas_ruang.jadwal_id', $jadwal->id)
            ->select('siswa.*', 'cbt_ruang.nama_ruang', 'cbt_sesi.nama_sesi')
            ->orderBy('cbt_ruang.nama_ruang')
            ->orderBy('siswa.nama')
            ->get();

        return Inertia::render('Cbt/Guru/PrintAbsensi', [
            'siswas' => $siswas,
            'kop' => CbtKopAbsensi::first(),
            'jadwal' => $jadwal,
        ]);
    }

    public function beritaAcara(Request $request)
    {
        $jadwal = $this->resolveJadwal($request);

        $ruangSesiList = DB::table('cbt_kelas_ruang')
            ->join('cbt_ruang', 'cbt_ruang.id', '=', 'cbt_kelas_ruang.ruang_id')
            ->join('cbt_sesi', 'cbt_sesi.id', '=', 'cbt_kelas_ruang.sesi_id')
            ->where('cbt_kelas_ruang.jadwal_id', $jadwal->id)
            ->select('cbt_kelas_ruang.id', 'cbt_ruang.nama_ruang', 'cbt_sesi.nama_sesi')
            ->get();

        foreach ($ruangSesiList as $rs) {
            $rs->peserta = DB::table('cbt_sesi_siswa')->where('kelas_ruang_id', $rs->id)->count();
            $rs->hadir = DB::table('cbt_durasi_siswa')
                ->where('jadwal_id', $jadwal->id)
                ->whereIn('siswa_id', function ($query) use ($rs) {
                    $query->select('siswa_id')->from('cbt_sesi_siswa')->where('kelas_ruang_id', $rs->id);
                })->count();
            $rs->absen = $rs->peserta - $rs->hadir;
        }

        return Inertia::render('Cbt/Guru/PrintBerita', [
            'ruangSesiList' => $ruangSesiList,
            'kop' => CbtKopBerita::first(),
            'jadwal' => $jadwal,
        ]);
    }

    public function rekapNilai(Request $request)
    {
        $jadwal = $this->resolveJadwal($request);

        $nilais = DB::table('cbt_nilai')
            ->join('siswa', 'siswa.id', '=', 'cbt_nilai.siswa_id')
            ->where('cbt_nilai.jadwal_id', $jadwal->id)
            ->select('cbt_nilai.*', 'siswa.nama', 'siswa.nis')
            ->orderBy('siswa.nama')
            ->get();

        $nilais = $nilais->map(function ($n) {
            $n->total_nilai = (float) $n->pg_nilai + (float) $n->kompleks_nilai + (float) $n->jodohkan_nilai + (float) $n->isian_nilai + (float) $n->esai_nilai;

            return $n;
        });

        return Inertia::render('Cbt/Guru/RekapNilai', [
            'nilais' => $nilais,
            'jadwal' => $jadwal,
        ]);
    }

    private function resolveJadwal(Request $request): Jadwal
    {
        $guru = auth()->user()->guru;
        abort_unless($guru, 403);

        $jadwal = Jadwal::with('bankSoal')
            ->whereHas('bankSoal', fn ($q) => $q->where('guru_id', $guru->id))
            ->findOrFail($request->query('jadwal_id'));

        return $jadwal;
    }
}
