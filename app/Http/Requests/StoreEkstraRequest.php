<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEkstraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['superadmin', 'operator']);
    }

    public function rules(): array
    {
        return [
            'nama_ekstra' => 'required|string|max:150',
            'kode_ekstra' => 'required|string|max:25|unique:ekstra,kode_ekstra',
        ];
    }
}
