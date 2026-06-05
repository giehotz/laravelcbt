<?php

namespace App\Http\Controllers\Rapor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Master\Kelas;
use App\Models\Master\Mapel;
use App\Models\Master\Siswa;
use App\Models\Cbt\CbtJenis;
use App\Models\Akademik\RaporConfig;
use App\Models\Akademik\RaporKkm;
use App\Models\Akademik\RaporNilaiAkhir;
use App\Services\RaporService;
use App\Http\Requests\Rapor\ImportCbtRequest;
use App\Http\Requests\Rapor\UpdateNilaiRequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;

class RaporController extends Controller
{
    protected RaporService $raporService;

    public function __construct(RaporService $raporService)
    {
        $this->raporService = $raporService;
    }

    public function setting(Request $request)
    {
        // Only superadmin and operator should edit setting
        $tp = $request->get('tp_aktif');
        $smt = $request->get('smt_aktif');

        if (!$tp || !$smt) {
            return redirect()->back()->with('error', 'Tahun pelajaran atau semester aktif belum diset.');
        }

        $config = RaporConfig::firstOrCreate(
            ['tp_id' => $tp->id, 'smt_id' => $smt->id],
            ['jenis_ph_id' => null, 'jenis_pts_id' => null, 'jenis_pas_id' => null]
        );

        $kkm = RaporKkm::with(['kelas', 'mapel'])
            ->where('tp_id', $tp->id)
            ->where('smt_id', $smt->id)
            ->get();

        $cbtJenis = CbtJenis::all();
        $kelas = Kelas::all();
        $mapel = Mapel::all();

        return Inertia::render('Rapor/Setting', [
            'config' => $config,
            'kkm' => $kkm,
            'cbtJenis' => $cbtJenis,
            'kelas' => $kelas,
            'mapel' => $mapel,
        ]);
    }

    public function updateSetting(Request $request)
    {
        $tp = $request->get('tp_aktif');
        $smt = $request->get('smt_aktif');

        $request->validate([
            'jenis_ph_id' => 'nullable|exists:cbt_jenis,id',
            'jenis_pts_id' => 'nullable|exists:cbt_jenis,id',
            'jenis_pas_id' => 'nullable|exists:cbt_jenis,id',
            'kkm' => 'array',
            'kkm.*.kelas_id' => 'required|exists:kelas,id',
            'kkm.*.mapel_id' => 'required|exists:mapel,id',
            'kkm.*.kkm' => 'required|numeric',
            'kkm.*.bobot_ph' => 'required|numeric',
            'kkm.*.bobot_pts' => 'required|numeric',
            'kkm.*.bobot_pas' => 'required|numeric',
        ]);

        RaporConfig::updateOrCreate(
            ['tp_id' => $tp->id, 'smt_id' => $smt->id],
            [
                'jenis_ph_id' => $request->jenis_ph_id,
                'jenis_pts_id' => $request->jenis_pts_id,
                'jenis_pas_id' => $request->jenis_pas_id,
            ]
        );

        foreach ($request->kkm as $k) {
            RaporKkm::updateOrCreate(
                [
                    'tp_id' => $tp->id, 
                    'smt_id' => $smt->id, 
                    'kelas_id' => $k['kelas_id'], 
                    'mapel_id' => $k['mapel_id']
                ],
                [
                    'kkm' => $k['kkm'],
                    'bobot_ph' => $k['bobot_ph'],
                    'bobot_pts' => $k['bobot_pts'],
                    'bobot_pas' => $k['bobot_pas'],
                ]
            );
        }

        return redirect()->back()->with('success', 'Setting rapor berhasil disimpan.');
    }

    public function inputNilai(Request $request)
    {
        $tp = $request->get('tp_aktif');
        $smt = $request->get('smt_aktif');
        
        $kelas = Kelas::all();
        $mapel = Mapel::all();

        $selectedKelasId = $request->query('kelas_id');
        $selectedMapelId = $request->query('mapel_id');

        $siswaList = [];
        $kkm = null;

        if ($selectedKelasId && $selectedMapelId) {
            $kkm = RaporKkm::where('tp_id', $tp->id)
                ->where('smt_id', $smt->id)
                ->where('kelas_id', $selectedKelasId)
                ->where('mapel_id', $selectedMapelId)
                ->first();

            // Ambil semua siswa di kelas tersebut (dari tabel siswa / kelas_siswa)
            // Di sini kita anggap menggunakan field 'kelas_id' dari tabel siswa untuk kesederhanaan,
            // atau jika Anda memakai kelas_siswa, sesuaikan join-nya.
            // Sesuai sistem sebelumnya: Siswa memiliki relasi ke BukuInduk/kelas
            $siswaQuery = Siswa::whereHas('kelasAktif', function($q) use ($selectedKelasId) {
                $q->where('kelas_id', $selectedKelasId);
            })->get();

            foreach ($siswaQuery as $siswa) {
                $nilai = RaporNilaiAkhir::firstOrCreate(
                    [
                        'tp_id' => $tp->id,
                        'smt_id' => $smt->id,
                        'kelas_id' => $selectedKelasId,
                        'mapel_id' => $selectedMapelId,
                        'siswa_id' => $siswa->id
                    ],
                    [
                        'nilai_ph' => 0,
                        'nilai_pts' => 0,
                        'nilai_pas' => 0,
                        'sumber_ph' => 'manual',
                        'sumber_pts' => 'manual',
                        'sumber_pas' => 'manual',
                        'final' => false,
                    ]
                );

                $siswaList[] = [
                    'siswa' => $siswa,
                    'nilai' => $nilai
                ];
            }
        }

        return Inertia::render('Rapor/InputNilai', [
            'kelas' => $kelas,
            'mapel' => $mapel,
            'selectedKelasId' => $selectedKelasId,
            'selectedMapelId' => $selectedMapelId,
            'siswaList' => $siswaList,
            'kkm' => $kkm,
        ]);
    }

    public function importCbt(ImportCbtRequest $request)
    {
        $tp = $request->get('tp_aktif');
        $smt = $request->get('smt_aktif');
        
        $kelasId = $request->kelas_id;
        $mapelId = $request->mapel_id;
        $komponen = $request->komponen; // ph, pts, pas

        $mapel = Mapel::findOrFail($mapelId);
        
        $siswaQuery = Siswa::whereHas('kelasAktif', function($q) use ($kelasId) {
            $q->where('kelas_id', $kelasId);
        })->get();

        foreach ($siswaQuery as $siswa) {
            $nilaiCbt = $this->raporService->importDariCbt($siswa, $mapel, $tp, $smt, $komponen);

            $nilai = RaporNilaiAkhir::where([
                'tp_id' => $tp->id,
                'smt_id' => $smt->id,
                'kelas_id' => $kelasId,
                'mapel_id' => $mapelId,
                'siswa_id' => $siswa->id
            ])->first();

            if ($nilai && !$nilai->final) {
                $nilai->{'nilai_' . $komponen} = $nilaiCbt;
                $nilai->{'sumber_' . $komponen} = 'cbt';
                $nilai->save();
            }
        }

        return redirect()->back()->with('success', "Nilai {$komponen} berhasil di-import dari CBT.");
    }

    public function simpanNilai(UpdateNilaiRequest $request)
    {
        $tp = $request->get('tp_aktif');
        $smt = $request->get('smt_aktif');
        $kelasId = $request->kelas_id;
        $mapelId = $request->mapel_id;

        $kkm = RaporKkm::where('tp_id', $tp->id)
                ->where('smt_id', $smt->id)
                ->where('kelas_id', $kelasId)
                ->where('mapel_id', $mapelId)
                ->first();

        if (!$kkm) {
            return redirect()->back()->with('error', 'KKM belum disetting untuk kelas dan mapel ini.');
        }

        foreach ($request->nilai as $n) {
            $nilaiRecord = RaporNilaiAkhir::where([
                'tp_id' => $tp->id,
                'smt_id' => $smt->id,
                'kelas_id' => $kelasId,
                'mapel_id' => $mapelId,
                'siswa_id' => $n['siswa_id']
            ])->first();

            if ($nilaiRecord && !$nilaiRecord->final) {
                $nilaiRecord->nilai_ph = $n['nilai_ph'];
                $nilaiRecord->nilai_pts = $n['nilai_pts'];
                $nilaiRecord->nilai_pas = $n['nilai_pas'];
                $nilaiRecord->sumber_ph = $n['sumber_ph'];
                $nilaiRecord->sumber_pts = $n['sumber_pts'];
                $nilaiRecord->sumber_pas = $n['sumber_pas'];

                $nilaiAkhir = $this->raporService->hitungNilaiAkhir($nilaiRecord, $kkm);
                $predikat = $this->raporService->predikat($nilaiAkhir, $kkm->kkm);

                $nilaiRecord->nilai_akhir = $nilaiAkhir;
                $nilaiRecord->predikat = $predikat;
                $nilaiRecord->save();
            }
        }

        return redirect()->back()->with('success', 'Nilai rapor berhasil disimpan.');
    }

    public function kunci(Request $request, Kelas $kelas, Mapel $mapel)
    {
        $tp = $request->get('tp_aktif');
        $smt = $request->get('smt_aktif');

        RaporNilaiAkhir::where([
            'tp_id' => $tp->id,
            'smt_id' => $smt->id,
            'kelas_id' => $kelas->id,
            'mapel_id' => $mapel->id,
        ])->update(['final' => true]);

        return redirect()->back()->with('success', 'Nilai berhasil dikunci. Data tidak dapat diubah lagi.');
    }

    public function cetakSiswa(Request $request, Siswa $siswa)
    {
        $tp = $request->get('tp_aktif');
        $smt = $request->get('smt_aktif');

        $nilai = RaporNilaiAkhir::with('mapel')
            ->where('tp_id', $tp->id)
            ->where('smt_id', $smt->id)
            ->where('siswa_id', $siswa->id)
            ->get();

        $sekolah = \App\Models\Master\Setting::first();

        // In production, we usually use view that renders html and converts to pdf
        $pdf = Pdf::loadView('pdf.rapor_siswa', [
            'siswa' => $siswa,
            'nilai' => $nilai,
            'tp' => $tp,
            'smt' => $smt,
            'sekolah' => $sekolah,
        ]);

        return $pdf->stream('Rapor_' . $siswa->nis . '.pdf');
    }

    public function cetakKelas(Request $request, Kelas $kelas)
    {
        $tp = $request->get('tp_aktif');
        $smt = $request->get('smt_aktif');

        $siswaList = Siswa::whereHas('kelasAktif', function($q) use ($kelas) {
            $q->where('kelas_id', $kelas->id);
        })->get();

        $sekolah = \App\Models\Master\Setting::first();

        $data = [];
        foreach ($siswaList as $siswa) {
            $nilai = RaporNilaiAkhir::with('mapel')
                ->where('tp_id', $tp->id)
                ->where('smt_id', $smt->id)
                ->where('siswa_id', $siswa->id)
                ->get();
                
            $data[] = [
                'siswa' => $siswa,
                'nilai' => $nilai
            ];
        }

        $pdf = Pdf::loadView('pdf.rapor_kelas', [
            'data' => $data,
            'kelas' => $kelas,
            'tp' => $tp,
            'smt' => $smt,
            'sekolah' => $sekolah,
        ]);

        return $pdf->stream('Rapor_Kelas_' . $kelas->nama . '.pdf');
    }
}
