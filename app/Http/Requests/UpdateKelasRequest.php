<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKelasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['superadmin', 'operator']);
    }

    public function rules(): array
    {
        $kelas = $this->route('kelas') ?? $this->route('kela');
        $id = is_object($kelas) ? $kelas->id : $kelas;

        return [
            'nama_kelas' => [
                'required',
                'string',
                'max:50',
                Rule::unique('kelas')->where(fn ($q) => $q
                    ->where('tahun_pelajaran_id', $this->tahun_pelajaran_id)
                    ->where('semester_id', $this->semester_id)
                )->ignore($id),
            ],
            'kode_kelas' => 'required|string|max:20',
            'tahun_pelajaran_id' => 'required|exists:tahun_pelajaran,id',
            'semester_id' => 'required|exists:semesters,id',
            'jurusan_id' => 'nullable|exists:jurusan,id',
            'level_id' => 'required|exists:level_kelas,id',
            'guru_id' => 'nullable|exists:guru,id',
            'set_siswa' => 'sometimes|string|max:1',
        ];
    }
}
