<?php

namespace App\Http\Requests\Cbt;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Cbt\Jenis;

class StoreJenisRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Jenis::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'nama_jenis' => ['required', 'string', 'max:80'],
            'kode_jenis' => ['required', 'string', 'max:15', 'unique:cbt_jenis,kode_jenis'],
        ];
    }
}
