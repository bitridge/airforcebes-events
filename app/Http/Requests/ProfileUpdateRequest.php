<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:255', 'min:2'],
            'last_name' => ['required', 'string', 'max:255', 'min:2'],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[\+]?[0-9\s\-\(\)]+$/'],
            'organization_name' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'naics_codes' => ['nullable', 'string', 'max:500'],
            'industry_connections' => ['nullable', 'string', 'max:500'],
            'core_specialty_area' => ['nullable', 'string', 'max:500'],
            'contract_vehicles' => ['nullable', 'string', 'max:500'],
            'meeting_preference' => ['nullable', 'string', 'in:in_person,virtual,hybrid,no_preference,prefer_morning,prefer_afternoon,prefer_evening'],
            'small_business_forum' => ['nullable', 'boolean'],
            'small_business_matchmaker' => ['nullable', 'boolean'],
            'bio' => ['nullable', 'string', 'max:1000'],
            'profile_picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif', 'max:2048'],
        ];
    }
}
