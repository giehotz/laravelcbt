<?php

namespace App\Http\Requests\Cbt;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSesiRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('sesi'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $id = $this->route('sesi') ? $this->route('sesi')->id : null;
        return [
            'nama_sesi' => ['required', 'string', 'max:80'],
            'kode_sesi' => ['required', 'string', 'max:15', Rule::unique('cbt_sesi', 'kode_sesi')->ignore($id)],
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
