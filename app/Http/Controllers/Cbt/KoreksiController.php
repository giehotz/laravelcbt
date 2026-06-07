<?php

namespace App\Http\Controllers\Cbt;

use App\Http\Controllers\Controller;
use App\Models\Cbt\Jadwal;
use App\Models\Cbt\Nilai;
use App\Models\Cbt\SoalSiswa;
use App\Models\Master\Siswa;
use App\Services\CbtService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class KoreksiController extends Controller
{
    /**
     * Menampilkan daftar siswa yang butuh koreksi untuk sebuah jadwal
     */
    public function index(Request $request)
    {
        // Ambil jadwal yang memiliki soal essai
        $jadwals = Jadwal::with(['bankSoal' => function ($q) {
            $q->where('jml_esai', '>', 0);
        }])
            ->whereHas('bankSoal', function ($q) {
                $q->where('jml_esai', '>', 0);
            })
            ->orderBy('tgl_mulai', 'desc')
            ->get();

        return Inertia::render('Cbt/Guru/KoreksiList', [
            'jadwals' => $jadwals,
        ]);
    }

    /**
     * Data status koreksi siswa per jadwal
     */
    public function dataSiswa(Request $request, Jadwal $jadwal)
    {
        // Ambil semua siswa yang telah selesai ujian (status durasi = 2)
        $siswaUjian = DB::table('cbt_durasi_siswa')
            ->join('siswa', 'siswa.id', '=', 'cbt_durasi_siswa.siswa_id')
            ->leftJoin('cbt_nilai', function ($join) use ($jadwal) {
                $join->on('cbt_nilai.siswa_id', '=', 'siswa.id')
                    ->where('cbt_nilai.jadwal_id', '=', $jadwal->id);
            })
            ->where('cbt_durasi_siswa.jadwal_id', $jadwal->id)
            ->where('cbt_durasi_siswa.status', 2) // Selesai
            ->select(
                'siswa.id',
                'siswa.nama',
                'siswa.nis',
                'cbt_nilai.esai_nilai',
                'cbt_nilai.pg_nilai',
                'cbt_nilai.dikoreksi'
            )
            ->get();

        return response()->json([
            'data' => $siswaUjian,
        ]);
    }

    /**
     * Menampilkan form koreksi untuk satu siswa
     */
    public function koreksiSiswa(Request $request, Jadwal $jadwal, Siswa $siswa)
    {
        $soalSiswas = SoalSiswa::with('soal')
            ->where('jadwal_id', $jadwal->id)
            ->where('siswa_id', $siswa->id)
            ->where('jenis_soal', 5) // Essai
            ->orderBy('no_soal_alias', 'asc')
            ->get();

        $nilai = Nilai::where('jadwal_id', $jadwal->id)
            ->where('siswa_id', $siswa->id)
            ->first();

        return Inertia::render('Cbt/Guru/KoreksiDetail', [
            'jadwal' => $jadwal->load('bankSoal'),
            'siswa' => $siswa,
            'soalSiswas' => $soalSiswas,
            'nilai' => $nilai,
        ]);
    }

    /**
     * Menyimpan hasil koreksi essai
     */
    public function simpanKoreksi(Request $request, Jadwal $jadwal, Siswa $siswa, CbtService $cbtService)
    {
        $request->validate([
            'koreksi' => 'required|array',
            'koreksi.*.id' => 'required|string',
            'koreksi.*.point_esai' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $jadwal, $siswa) {
            $totalPointEssai = 0;

            foreach ($request->koreksi as $item) {
                SoalSiswa::where('id', $item['id'])
                    ->where('siswa_id', $siswa->id)
                    ->update([
                        'point_esai' => $item['point_esai'],
                    ]);
                $totalPointEssai += $item['point_esai'];
            }

            // Hitung nilai akhir essai berdasarkan bobot
            $bank = $jadwal->bankSoal;
            $esaiNilai = 0.0;
            if ($bank->jml_esai > 0) {
                // Misal maksimum point per soal essai adalah 100
                $maxPoint = $bank->jml_esai * 100;
                $bobot = $bank->bobot_esai ?? 0;
                if ($maxPoint > 0) {
                    $esaiNilai = ($totalPointEssai / $maxPoint) * $bobot;
                }
            }

            // Update nilai di Nilai
            $nilai = Nilai::where('siswa_id', $siswa->id)
                ->where('jadwal_id', $jadwal->id)
                ->first();

            if ($nilai) {
                $nilai->update([
                    'esai_nilai' => number_format($esaiNilai, 2, '.', ''),
                    'dikoreksi' => true,
                ]);
            }
        });

        return response()->json(['message' => 'Nilai essai berhasil disimpan.']);
    }
}
