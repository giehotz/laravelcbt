<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMapelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['superadmin', 'operator']);
    }

    public function rules(): array
    {
        $mapel = $this->route('mapel');
        $id = is_object($mapel) ? $mapel->id : $mapel;

        return [
            'nama_mapel' => 'required|string|max:100',
            'kode' => 'required|string|max:20|unique:mapel,kode,' . $id,
            'kelompok' => 'required|string|max:10',
            'bobot_p' => 'required|integer|min:0|max:100',
            'bobot_k' => 'required|integer|min:0|max:100',
            'jenjang' => 'nullable|integer|min:0',
            'urutan' => 'nullable|integer|min:0',
            'status' => 'sometimes|boolean',
        ];
    }
}
