<?php

namespace App\Http\Requests\Cbt;

use Illuminate\Foundation\Http\FormRequest;

class ImportSoalRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:xlsx,xls|max:5120',
        ];
    }
}
