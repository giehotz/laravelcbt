<?php

namespace App\Http\Requests\Rapor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNilaiRequest extends FormRequest
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
            'nilai' => 'required|array',
            'nilai.*.siswa_id' => 'required|exists:siswa,id',
            'nilai.*.nilai_ph' => 'required|numeric|min:0|max:100',
            'nilai.*.nilai_pts' => 'required|numeric|min:0|max:100',
            'nilai.*.nilai_pas' => 'required|numeric|min:0|max:100',
            'nilai.*.sumber_ph' => 'required|string|in:manual,cbt',
            'nilai.*.sumber_pts' => 'required|string|in:manual,cbt',
            'nilai.*.sumber_pas' => 'required|string|in:manual,cbt',
        ];
    }
}
