<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['sometimes', 'required', 'email', 'unique:users,email,'.$this->route('uuid').',uuid'],
            'password' => ['sometimes', 'nullable', 'string', 'min:8'],
            'firstname' => ['sometimes', 'required', 'string', 'max:255'],
            'lastname' => ['sometimes', 'required', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
