<?php

namespace App\Http\Requests\Cbt;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Cbt\Ruang;

class StoreRuangRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create', Ruang::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'nama_ruang' => ['required', 'string', 'max:80'],
            'kode_ruang' => ['required', 'string', 'max:15', 'unique:cbt_ruang,kode_ruang'],
        ];
    }
}
