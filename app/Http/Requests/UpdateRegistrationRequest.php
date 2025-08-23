<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRegistrationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:pending,confirmed,cancelled'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Registration status is required.',
            'status.in' => 'Invalid registration status.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
        ];
    }

    public function attributes(): array
    {
        return [
            'status' => 'registration status',
            'notes' => 'notes',
        ];
    }
}
