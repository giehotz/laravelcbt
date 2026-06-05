<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSemesterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['superadmin', 'operator']);
    }

    public function rules(): array
    {
        return [
            'smt' => 'required|string|max:10',
            'nama_smt' => 'required|string|max:20',
            'active' => 'sometimes|boolean',
        ];
    }
}
