<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreJurusanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['superadmin', 'operator']);
    }

    public function rules(): array
    {
        return [
            'nama_jurusan' => 'required|string|max:80',
            'kode_jurusan' => 'required|string|max:10|unique:jurusan,kode_jurusan',
            'mapel_peminatan' => 'nullable|array',
            'mapel_peminatan.*' => 'exists:mapel,id',
            'status' => 'sometimes|boolean',
        ];
    }
}
