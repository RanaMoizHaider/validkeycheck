<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider' => 'required|string|max:255',
            'credentials' => 'required|array',
            'credentials.*' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'provider.required' => 'Provider is required.',
            'provider.string' => 'Provider must be a string.',
            'credentials.required' => 'Credentials are required.',
            'credentials.array' => 'Credentials must be an array.',
            'credentials.*.required' => 'All credential fields are required.',
            'credentials.*.string' => 'All credential fields must be strings.',
        ];
    }
} 