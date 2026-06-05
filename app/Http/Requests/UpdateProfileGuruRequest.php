<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileGuruRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole('superadmin|operator|kepsek');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'jabatan' => 'nullable|string|max:50',
            'is_aktif_jabatan' => 'boolean',
            'mapel_kelas' => 'array',
            'mapel_kelas.*.mapel_id' => 'required|exists:mapel,id',
            'mapel_kelas.*.kelas' => 'required|array',
            'mapel_kelas.*.kelas.*' => 'exists:kelas,id',
            'ekstra' => 'array',
            'ekstra.*' => 'exists:ekstra,id',
        ];
    }
}
