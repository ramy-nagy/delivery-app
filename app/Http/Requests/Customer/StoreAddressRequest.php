<?php

namespace App\Http\Requests\Customer;

use Illuminate\Foundation\Http\FormRequest;

class StoreAddressRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'label' => ['nullable', 'string', 'max:255'],
            'line1' => ['required', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:128'],
            'governorate' => ['nullable', 'string', 'max:128'],
            'is_default' => ['sometimes', 'boolean'],
        ];
    }
}
