<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('admin');

        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $userId,
            'username' => 'nullable|string|max:255|unique:users,username,' . $userId,
            'password' => 'nullable|string|min:8',
            'role' => 'required|string|in:superadmin,operator,proktor,kepsek',
        ];
    }
}
