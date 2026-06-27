<?php

namespace App\Http\Controllers\Cbt;

use App\Http\Controllers\Controller;
use App\Models\CbtKopKartu;
use App\Models\Master\Kelas;
use App\Models\Semester;
use App\Models\TahunPelajaran;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CetakKartuController extends Controller
{
    /**
     * Tampilkan halaman Setting Cetak Kartu
     */
    public function index()
    {
        $kop = CbtKopKartu::first();
        if (! $kop) {
            $kop = CbtKopKartu::create([
                'header_1' => '<b>KARTU PESERTA</b>',
                'header_2' => 'ASESMEN AKHIR SEMESTER',
                'header_3' => 'NAMA SEKOLAH',
                'header_4' => 'TAHUN PELAJARAN 2024/2025',
                'tanggal' => date('d F Y'),
            ]);
        }

        $kelasList = Kelas::orderBy('nama_kelas')->get(['id', 'nama_kelas']);

        return Inertia::render('Cbt/Guru/CetakKartu/Index', [
            'kop' => $kop,
            'kelasList' => $kelasList,
        ]);
    }

    /**
     * Simpan pengaturan kop kartu
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'header_1' => 'nullable|string|max:150',
            'header_2' => 'nullable|string|max:150',
            'header_3' => 'nullable|string|max:150',
            'header_4' => 'nullable|string|max:150',
            'tanggal' => 'nullable|string|max:100',
        ]);

        $kop = CbtKopKartu::first();
        if ($kop) {
            $kop->update($validated);
        } else {
            CbtKopKartu::create($validated);
        }

        return redirect()->back()->with('success', 'Pengaturan kartu berhasil disimpan.');
    }

    /**
     * Cetak Kartu Peserta berdasarkan Kelas
     */
    public function print(Request $request)
    {
        $kelasId = $request->query('kelas_id');
        if (! $kelasId) {
            abort(404, 'Kelas belum dipilih.');
        }

        $tpAktif = TahunPelajaran::where('active', true)->firstOrFail();
        $smtAktif = Semester::where('active', true)->firstOrFail();

        // Ambil data siswa di kelas yang dipilih pada TP dan Semester aktif
        $siswas = DB::table('cbt_sesi_siswa')
            ->join('siswa', 'siswa.id', '=', 'cbt_sesi_siswa.siswa_id')
            ->join('users', 'users.id', '=', 'siswa.user_id')
            ->leftJoin('kelas', 'kelas.id', '=', 'cbt_sesi_siswa.kelas_id')
            ->leftJoin('cbt_ruang', 'cbt_ruang.id', '=', 'cbt_sesi_siswa.ruang_id')
            ->leftJoin('cbt_sesi', 'cbt_sesi.id', '=', 'cbt_sesi_siswa.sesi_id')
            ->where('cbt_sesi_siswa.kelas_id', $kelasId)
            ->where('cbt_sesi_siswa.tp_id', $tpAktif->id)
            ->where('cbt_sesi_siswa.smt_id', $smtAktif->id)
            ->select(
                'siswa.*',
                'users.username',
                'kelas.nama_kelas',
                'cbt_ruang.nama_ruang',
                'cbt_sesi.nama_sesi'
            )
            ->orderBy('siswa.nama')
            ->get();

        $kop = CbtKopKartu::first();

        return Inertia::render('Cbt/Guru/PrintKartu', [
            'siswas' => $siswas,
            'kop' => $kop,
            'jadwal' => null,
        ]);
    }
}
