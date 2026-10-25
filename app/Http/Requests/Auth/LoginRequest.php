<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],

            // Optional details the app reports about the device it's running on.
            // A browser can't read the MAC address, so `device.id` is the app's
            // own persistent ID for the device instead.
            'device' => ['sometimes', 'array'],
            'device.id' => ['nullable', 'string', 'max:64'],
            'device.model' => ['nullable', 'string', 'max:100'],
            'device.platform' => ['nullable', 'string', 'max:100'],
            'device.platform_version' => ['nullable', 'string', 'max:50'],
            'device.mobile' => ['nullable', 'boolean'],
            'device.touch' => ['nullable', 'boolean'],
            'device.screen' => ['nullable', 'string', 'max:30'],
            'device.viewport' => ['nullable', 'string', 'max:30'],
            'device.timezone' => ['nullable', 'string', 'max:64'],
            'device.language' => ['nullable', 'string', 'max:35'],
            'device.app_version' => ['nullable', 'string', 'max:30'],
            'device.standalone' => ['nullable', 'boolean'],
        ];
    }
}
