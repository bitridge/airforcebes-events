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
            // Personal Information
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'organization_name' => ['required', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            // Registration Type
            'type' => ['required', 'in:registration,checkin'],
            'checkin_type' => ['nullable', 'in:in_person,virtual,hybrid'],
            // Business Information
            'naics_codes' => ['nullable', 'string', 'max:1000'],
            'industry_connections' => ['nullable', 'string', 'max:1000'],
            'core_specialty_area' => ['nullable', 'string', 'max:1000'],
            'contract_vehicles' => ['nullable', 'string', 'max:1000'],
            // Preferences
            'meeting_preference' => ['required', 'in:in_person,virtual,hybrid,no_preference'],
            // Event Specific
            'small_business_forum' => ['boolean'],
            'small_business_matchmaker' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Registration status is required.',
            'status.in' => 'Invalid registration status.',
            'notes.max' => 'Notes cannot exceed 1000 characters.',
            // Personal Information
            'first_name.required' => 'First name is required.',
            'first_name.max' => 'First name cannot exceed 255 characters.',
            'last_name.required' => 'Last name is required.',
            'last_name.max' => 'Last name cannot exceed 255 characters.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'Email address cannot exceed 255 characters.',
            'phone.max' => 'Phone number cannot exceed 20 characters.',
            'organization_name.required' => 'Organization name is required.',
            'organization_name.max' => 'Organization name cannot exceed 255 characters.',
            'title.max' => 'Title cannot exceed 255 characters.',
            // Registration Type
            'type.required' => 'Registration type is required.',
            'type.in' => 'Please select a valid registration type.',
            'checkin_type.in' => 'Please select a valid check-in type.',
            // Business Information
            'naics_codes.max' => 'NAICS codes cannot exceed 1,000 characters.',
            'industry_connections.max' => 'Industry connections cannot exceed 1,000 characters.',
            'core_specialty_area.max' => 'Core specialty area cannot exceed 1,000 characters.',
            'contract_vehicles.max' => 'Contract vehicles cannot exceed 1,000 characters.',
            // Preferences
            'meeting_preference.required' => 'Meeting preference is required.',
            'meeting_preference.in' => 'Please select a valid meeting preference.',
        ];
    }

    public function attributes(): array
    {
        return [
            'status' => 'registration status',
            'notes' => 'notes',
            'first_name' => 'first name',
            'last_name' => 'last name',
            'email' => 'email address',
            'phone' => 'phone number',
            'organization_name' => 'organization name',
            'title' => 'job title',
            'type' => 'registration type',
            'checkin_type' => 'check-in type',
            'naics_codes' => 'NAICS codes',
            'industry_connections' => 'industry connections',
            'core_specialty_area' => 'core specialty area',
            'contract_vehicles' => 'contract vehicles',
            'meeting_preference' => 'meeting preference',
            'small_business_forum' => 'small business forum',
            'small_business_matchmaker' => 'small business matchmaker',
        ];
    }
}
