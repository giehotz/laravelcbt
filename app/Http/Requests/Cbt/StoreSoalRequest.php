<?php

namespace App\Http\Requests\Cbt;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreSoalRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $jenis = (int) $this->input('jenis');

        $rules = [
            'jenis' => 'required|in:1,2,3,4,5',
            'nomor_soal' => 'required|integer|min:0',
            'soal' => 'required|string',
            'opsi_a' => 'required_if:jenis,1|nullable|string',
            'opsi_b' => 'required_if:jenis,1|nullable|string',
            'opsi_c' => 'required_if:jenis,1|nullable|string',
            'opsi_d' => 'required_if:jenis,1|nullable|string',
            'opsi_e' => 'required_if:jenis,1|nullable|string',
            'kesulitan' => 'required|integer|min:1|max:10',
            'timer' => 'required|boolean',
            'timer_menit' => 'required_if:timer,true|integer|min:0',
            'tampilkan' => 'required|boolean',
        ];

        if ($jenis === 2) {
            // PG Kompleks: must be an array of valid option letters
            $rules['jawaban'] = 'required|array|min:1';
            $rules['jawaban.*'] = 'required|string|in:A,B,C,D,E';
        } elseif ($jenis === 3) {
            // Menjodohkan: jawaban is an array of pairs
            $rules['jawaban'] = 'required|array|min:1';
            $rules['jawaban.*.kiri'] = 'required|string';
            $rules['jawaban.*.kanan'] = 'required|string';
        } else {
            $rules['jawaban'] = 'required|string';
        }

        return $rules;
    }
}
