<?php

namespace App\Http\Requests\Cbt;

use App\Models\Cbt\BankSoal;
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

    public function rules(): array
    {
        $jenis = (int) $this->input('jenis');

        $bank = $this->route('bank');
        if (is_numeric($bank)) {
            $bank = BankSoal::find($bank);
        }
        $opsiCount = $bank instanceof BankSoal ? $bank->opsi : 5;

        $rules = [
            'jenis' => 'required|in:1,2,3,4,5',
            'nomor_soal' => 'required|integer|min:0',
            'soal' => 'required|string',
            'opsi_a' => ($jenis === 1 && $opsiCount >= 1) ? 'required|string' : 'nullable|string',
            'opsi_b' => ($jenis === 1 && $opsiCount >= 2) ? 'required|string' : 'nullable|string',
            'opsi_c' => ($jenis === 1 && $opsiCount >= 3) ? 'required|string' : 'nullable|string',
            'opsi_d' => ($jenis === 1 && $opsiCount >= 4) ? 'required|string' : 'nullable|string',
            'opsi_e' => ($jenis === 1 && $opsiCount >= 5) ? 'required|string' : 'nullable|string',
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
