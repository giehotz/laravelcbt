<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLevelKelasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasAnyRole(['superadmin', 'operator']);
    }

    public function rules(): array
    {
        $levelKelas = $this->route('level_kelas');
        $id = is_object($levelKelas) ? $levelKelas->id : $levelKelas;

        return [
            'level' => 'required|string|max:5|unique:level_kelas,level,'.$id,
        ];
    }
}
