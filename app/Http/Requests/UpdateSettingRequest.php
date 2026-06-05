<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole('superadmin');
    }

    public function rules(): array
    {
        return [
            'nama_sekolah' => 'required|string|max:255',
            'nss' => 'nullable|string|max:30',
            'npsn' => 'nullable|string|max:15',
            'alamat' => 'nullable|string|max:255',
            'kecamatan' => 'nullable|string|max:100',
            'kabupaten' => 'nullable|string|max:100',
            'provinsi' => 'nullable|string|max:100',
            'kode_pos' => 'nullable|string|max:10',
            'telp' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:100',
            'website' => 'nullable|string|max:100',
            'logo' => 'nullable|sometimes|image|mimes:jpg,jpeg,png,webp|max:2048|dimensions:min_width=100,min_height=100',
            'kepala_sekolah' => 'nullable|string|max:100',
            'nip_kepsek' => 'nullable|string|max:30',
            'running_text' => 'nullable|string',
        ];
    }
}
