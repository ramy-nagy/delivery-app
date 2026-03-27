<?php

namespace App\Http\Requests\Auth;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['nullable', 'string', 'max:32'],
            'role' => [
                'required',
                Rule::in([
                    UserRole::CUSTOMER->value,
                    UserRole::DRIVER->value,
                    UserRole::RESTAURANT->value,
                ]),
            ],
            'business_name' => [
                Rule::requiredIf(fn () => $this->input('role') === UserRole::RESTAURANT->value),
                'nullable',
                'string',
                'max:255',
            ],
            'device_name' => ['nullable', 'string', 'max:255'],
        ];
    }
}
