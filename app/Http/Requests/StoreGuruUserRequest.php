<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGuruUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['superadmin', 'operator']);
    }

    public function rules(): array
    {
        return [
            'nama_guru' => 'required|string|max:100',
            'email' => 'nullable|email|max:254|unique:users,email',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:8',
            'nip' => 'nullable|string|max:30',
            'kode_guru' => 'nullable|string|max:10|unique:guru,kode_guru',
            'no_ktp' => 'nullable|string|max:16',
            'tempat_lahir' => 'nullable|string|max:50',
            'tgl_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|string|max:10',
            'agama' => 'nullable|string|max:15',
            'no_hp' => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'nuptk' => 'nullable|string|max:20',
            'jenis_ptk' => 'nullable|string|max:50',
            'status_pegawai' => 'nullable|string|max:50',
            'status_aktif' => 'nullable|string|max:20',
        ];
    }
}
