<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEkstraRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['superadmin', 'operator']);
    }

    public function rules(): array
    {
        $ekstra = $this->route('ekstra');
        $id = is_object($ekstra) ? $ekstra->id : $ekstra;

        return [
            'nama_ekstra' => 'required|string|max:150',
            'kode_ekstra' => 'required|string|max:25|unique:ekstra,kode_ekstra,' . $id,
        ];
    }
}
