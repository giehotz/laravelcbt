<?php

namespace App\Http\Requests\Cbt;

use App\Models\Cbt\Sesi;
use Illuminate\Foundation\Http\FormRequest;

class StoreSesiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Sesi::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'nama_sesi' => ['required', 'string', 'max:80'],
            'kode_sesi' => ['required', 'string', 'max:15', 'unique:cbt_sesi,kode_sesi'],
            'waktu_mulai' => ['required', 'date_format:H:i'],
            'waktu_akhir' => ['required', 'date_format:H:i', function ($attribute, $value, $fail) {
                if ($value <= $this->input('waktu_mulai')) {
                    $fail('Waktu selesai harus setelah waktu mulai.');
                }
            }],
            'aktif' => ['boolean'],
        ];
    }
}
