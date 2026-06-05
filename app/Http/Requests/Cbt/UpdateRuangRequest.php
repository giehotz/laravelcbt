<?php

namespace App\Http\Requests\Cbt;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRuangRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('ruang'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $id = $this->route('ruang') ? $this->route('ruang')->id : null;
        return [
            'nama_ruang' => ['required', 'string', 'max:80'],
            'kode_ruang' => ['required', 'string', 'max:15', Rule::unique('cbt_ruang', 'kode_ruang')->ignore($id)],
        ];
    }
}
