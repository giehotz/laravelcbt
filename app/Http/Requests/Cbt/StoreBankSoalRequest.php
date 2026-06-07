<?php

namespace App\Http\Requests\Cbt;

use App\Services\GuruAssignmentService;
use Illuminate\Foundation\Http\FormRequest;

class StoreBankSoalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kode' => 'required|string|max:50',
            'nama' => 'required|string|max:255',
            'jenis_id' => 'required|exists:cbt_jenis,id',
            'level' => 'required|string',
            'kelas' => 'required|array|min:1',
            'mapel_id' => 'required|exists:mapel,id',
            'jurusan_id' => 'nullable|exists:jurusan,id',
            'guru_id' => 'nullable|exists:guru,id',
            'kkm' => 'required|integer|min:0|max:100',
            'status' => 'required|in:0,1',
            'opsi' => 'required|in:3,4,5',
            'skoring_kompleks' => 'required|in:all_or_nothing,partial',

            'tampil_pg' => 'required|integer|min:0',
            'bobot_pg' => 'required|integer|min:0',
            'tampil_esai' => 'required|integer|min:0',
            'bobot_esai' => 'required|integer|min:0',
            'tampil_kompleks' => 'required|integer|min:0',
            'bobot_kompleks' => 'required|integer|min:0',
            'tampil_jodohkan' => 'required|integer|min:0',
            'bobot_jodohkan' => 'required|integer|min:0',
            'tampil_isian' => 'required|integer|min:0',
            'bobot_isian' => 'required|integer|min:0',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $data = $this->all();

            $user = $this->user();
            if ($user->hasRole('guru')) {
                $assignmentService = app(GuruAssignmentService::class);
                if (! $assignmentService->isAllowed($user->guru, $data['mapel_id'], $data['kelas'] ?? [])) {
                    $validator->errors()->add('mapel_id', 'Anda tidak memiliki akses ke mata pelajaran atau kelas yang dipilih.');
                }
            }

            $types = ['pg', 'esai', 'kompleks', 'jodohkan', 'isian'];
            $totalBobot = 0;

            foreach ($types as $type) {
                $tampil = (int) ($data["tampil_{$type}"] ?? 0);
                $bobot = (int) ($data["bobot_{$type}"] ?? 0);
                $jml = (int) ($this->bank_soal ? $this->bank_soal->{"jml_{$type}"} : 0);

                if ($tampil > $jml && ($data['status'] ?? 0) == 1) {
                    $validator->errors()->add("tampil_{$type}", "Tampil {$type} tidak boleh melebihi jumlah soal ({$jml}) untuk bank soal aktif.");
                }

                if ($tampil > 0 && $bobot == 0) {
                    $validator->errors()->add("bobot_{$type}", "Bobot {$type} harus > 0 jika ada soal yang ditampilkan.");
                }

                if ($tampil == 0 && $bobot > 0) {
                    $validator->errors()->add("bobot_{$type}", "Bobot {$type} harus 0 jika tidak ada soal yang ditampilkan.");
                }

                $totalBobot += $bobot;
            }

            if (($data['status'] ?? 0) == 1 && $totalBobot != 100) {
                $validator->errors()->add('status', "Total bobot harus 100 sebelum bank soal bisa diaktifkan (Saat ini: {$totalBobot}).");
            }
        });
    }
}
