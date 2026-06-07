<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTahunPelajaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['superadmin', 'operator']);
    }

    public function rules(): array
    {
        $tahunPelajaran = $this->route('tahun_pelajaran');
        $id = is_object($tahunPelajaran) ? $tahunPelajaran->id : $tahunPelajaran;

        return [
            'tahun' => 'required|string|max:20|unique:tahun_pelajaran,tahun,'.$id,
            'active' => 'sometimes|boolean',
        ];
    }
}
