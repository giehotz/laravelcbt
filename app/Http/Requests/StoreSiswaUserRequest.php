<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSiswaUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['superadmin', 'operator']);
    }

    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:100',
            'nisn' => 'required|string|max:12|unique:siswa,nisn',
            'nis' => 'required|string|max:20|unique:siswa,nis',
            'jenis_kelamin' => 'nullable|string|max:1',
            'username' => 'required|string|max:50|unique:users,username',
            'password' => 'required|string|min:8',
            'tahun_masuk' => 'nullable|string|max:10',
            'sekolah_asal' => 'nullable|string|max:100',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|string|max:30',
            'agama' => 'nullable|string|max:15',
            'hp' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:254|unique:users,email',
            'anak_ke' => 'nullable|integer',
            'status_keluarga' => 'nullable|string|max:1',
            'alamat' => 'nullable|string',
            'rt' => 'nullable|string|max:5',
            'rw' => 'nullable|string|max:5',
            'kelurahan' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'kabupaten' => 'nullable|string|max:100',
            'provinsi' => 'nullable|string|max:100',
            'kode_pos' => 'nullable|integer',
            // Data Orang Tua
            'nama_ayah' => 'nullable|string|max:150',
            'tgl_lahir_ayah' => 'nullable|string|max:50',
            'pendidikan_ayah' => 'nullable|string|max:50',
            'pekerjaan_ayah' => 'nullable|string|max:100',
            'nohp_ayah' => 'nullable|string|max:20',
            'alamat_ayah' => 'nullable|string',
            'nama_ibu' => 'nullable|string|max:100',
            'tgl_lahir_ibu' => 'nullable|string|max:50',
            'pendidikan_ibu' => 'nullable|string|max:50',
            'pekerjaan_ibu' => 'nullable|string|max:100',
            'nohp_ibu' => 'nullable|string|max:20',
            'alamat_ibu' => 'nullable|string',
            'nama_wali' => 'nullable|string|max:150',
            'tgl_lahir_wali' => 'nullable|string|max:50',
            'pendidikan_wali' => 'nullable|string|max:50',
            'pekerjaan_wali' => 'nullable|string|max:100',
            'nohp_wali' => 'nullable|string|max:20',
            'alamat_wali' => 'nullable|string',
            'nik' => 'nullable|string|max:30',
            'warga_negara' => 'sometimes|string|max:20',
            'uid' => 'nullable|string|max:255|unique:siswa,uid',
        ];
    }
}
