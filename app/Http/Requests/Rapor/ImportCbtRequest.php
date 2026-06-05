<?php

namespace App\Http\Requests\Rapor;

use Illuminate\Foundation\Http\FormRequest;

class ImportCbtRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('guru') || $this->user()->hasRole('superadmin');
    }

    public function rules(): array
    {
        return [
            'kelas_id' => 'required|exists:kelas,id',
            'mapel_id' => 'required|exists:mapel,id',
            'komponen' => 'required|in:ph,pts,pas',
        ];
    }
}
