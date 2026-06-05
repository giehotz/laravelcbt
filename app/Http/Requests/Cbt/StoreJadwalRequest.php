<?php

namespace App\Http\Requests\Cbt;

use Illuminate\Foundation\Http\FormRequest;

class StoreJadwalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasRole(['superadmin', 'operator', 'guru']);
    }

    public function rules(): array
    {
        return [
            'bank_id' => 'required|exists:cbt_bank_soal,id',
            'jenis_id' => 'required|exists:cbt_jenis,id',
            'tgl_mulai' => 'required|date',
            'tgl_selesai' => 'required|date|after_or_equal:tgl_mulai',
            'durasi_ujian' => 'required|integer|min:1',
            'acak_soal' => 'boolean',
            'acak_opsi' => 'boolean',
            'hasil_tampil' => 'boolean',
            'token' => 'boolean',
            'status' => 'required|integer|in:0,1',
            'ulang' => 'boolean',
            'reset_login' => 'boolean',
            'rekap' => 'boolean',
            'jam_ke' => 'nullable|integer|min:0',
            'jarak' => 'nullable|integer|min:0',
            'pengawas' => 'nullable|array',
            'pengawas.*' => 'exists:guru,id',
        ];
    }
}
