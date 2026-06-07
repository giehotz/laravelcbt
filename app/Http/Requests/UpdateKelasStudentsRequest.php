<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

class UpdateKelasStudentsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $kelas = $this->route('kelas') ?? $this->route('kela');

        return Gate::allows('manageStudents', $kelas);
    }

    public function rules(): array
    {
        return [
            'siswa_ids' => 'present|array',
            'siswa_ids.*' => 'required|exists:siswa,id',
        ];
    }
}
