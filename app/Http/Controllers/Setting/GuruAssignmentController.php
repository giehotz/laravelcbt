<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Master\Guru;
use App\Http\Requests\UpdateProfileGuruRequest;
use App\Models\Master\Ekstra;
use App\Models\Master\Kelas;
use App\Models\Master\Mapel;
use App\Services\AcademicPeriodService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class GuruAssignmentController extends Controller
{
    public function edit(Guru $guru, AcademicPeriodService $periodService): Response
    {
        $tpId = $periodService->activeTpId();
        $smtId = $periodService->activeSmtId();

        $guru->load([
            'user',
            'jabatanGuru' => fn($q) => $q->where('tahun_pelajaran_id', $tpId)->where('semester_id', $smtId),
            'guruMapelKelas' => fn($q) => $q->where('tahun_pelajaran_id', $tpId)->where('semester_id', $smtId),
            'ekstraPembina' => fn($q) => $q->where('tahun_pelajaran_id', $tpId)->where('semester_id', $smtId),
        ]);

        return Inertia::render('Setting/User/GuruAssignment', [
            'guru' => $guru,
            'mapel' => Mapel::orderBy('nama_mapel')->get(['id', 'nama_mapel']),
            'kelas' => Kelas::orderBy('nama_kelas')->get(['id', 'nama_kelas']),
            'ekstra' => Ekstra::orderBy('nama_ekstra')->get(['id', 'nama_ekstra']),
        ]);
    }

    public function update(UpdateProfileGuruRequest $request, Guru $guru, AcademicPeriodService $periodService): RedirectResponse
    {
        $data = $request->validated();
        $tpId = $periodService->activeTpId();
        $smtId = $periodService->activeSmtId();

        if (!$tpId || !$smtId) {
            return back()->withErrors(['message' => 'Tahun Pelajaran atau Semester aktif belum diatur.']);
        }

        try {
            DB::transaction(function () use ($guru, $data, $tpId, $smtId) {
                // Update Jabatan
                if (!empty($data['jabatan'])) {
                    $guru->jabatanGuru()->updateOrCreate(
                        ['tahun_pelajaran_id' => $tpId, 'semester_id' => $smtId],
                        ['jabatan' => $data['jabatan'], 'is_aktif' => $data['is_aktif_jabatan'] ?? true]
                    );
                } else {
                    $guru->jabatanGuru()->where('tahun_pelajaran_id', $tpId)->where('semester_id', $smtId)->delete();
                }

                // Update Mapel Kelas
                $guru->guruMapelKelas()->where('tahun_pelajaran_id', $tpId)->where('semester_id', $smtId)->delete();
                if (!empty($data['mapel_kelas'])) {
                    $insertData = [];
                    foreach ($data['mapel_kelas'] as $mk) {
                        foreach ($mk['kelas'] as $kelasId) {
                            $insertData[] = [
                                'guru_id' => $guru->id,
                                'mapel_id' => $mk['mapel_id'],
                                'kelas_id' => $kelasId,
                                'tahun_pelajaran_id' => $tpId,
                                'semester_id' => $smtId,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ];
                        }
                    }
                    $guru->guruMapelKelas()->insert($insertData);
                }

                // Update Ekstra
                $guru->ekstraPembina()->where('tahun_pelajaran_id', $tpId)->where('semester_id', $smtId)->delete();
                if (!empty($data['ekstra'])) {
                    $insertData = [];
                    foreach ($data['ekstra'] as $ekstraId) {
                        $insertData[] = [
                            'guru_id' => $guru->id,
                            'ekstra_id' => $ekstraId,
                            'tahun_pelajaran_id' => $tpId,
                            'semester_id' => $smtId,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }
                    $guru->ekstraPembina()->insert($insertData);
                }
            });

            return back()->with('success', 'Penugasan guru berhasil disimpan.');
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->withErrors(['message' => 'Terjadi konflik data (Race Condition) atau duplikasi pada database saat menyimpan penugasan.'])->withInput();
        } catch (\Exception $e) {
            return back()->withErrors(['message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()])->withInput();
        }
    }
}
