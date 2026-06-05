<?php

namespace App\Http\Requests\Cbt;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJenisRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('jenis'));
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $id = $this->route('jenis') ? $this->route('jenis')->id : null;
        return [
            'nama_jenis' => ['required', 'string', 'max:80'],
            'kode_jenis' => ['required', 'string', 'max:15', Rule::unique('cbt_jenis', 'kode_jenis')->ignore($id)],
        ];
    }
}
