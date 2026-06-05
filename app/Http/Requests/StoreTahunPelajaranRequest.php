<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTahunPelajaranRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['superadmin', 'operator']);
    }

    public function rules(): array
    {
        return [
            'tahun' => 'required|string|max:20|unique:tahun_pelajaran,tahun',
            'active' => 'sometimes|boolean',
        ];
    }
}
