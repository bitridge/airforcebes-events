<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Event;
use App\Models\Registration;

class StoreRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // User must be authenticated and the event must exist
        if (!auth()->check()) {
            return false;
        }

        $event = $this->route('event');
        
        // Event must exist and be open for registration
        return $event && $event->canRegister(auth()->user());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'event_id' => [
                'required',
                'exists:events,id',
            ],
            'terms_accepted' => [
                'required',
                'accepted',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
            // Personal Information
            'first_name' => [
                'required',
                'string',
                'max:255',
            ],
            'last_name' => [
                'required',
                'string',
                'max:255',
            ],
            'email' => [
                'required',
                'email',
                'max:255',
            ],
            'phone' => [
                'nullable',
                'string',
                'max:20',
            ],
            'organization_name' => [
                'required',
                'string',
                'max:255',
            ],
            'title' => [
                'nullable',
                'string',
                'max:255',
            ],
            // Registration Type
            'type' => [
                'required',
                'in:registration,checkin',
            ],
            'checkin_type' => [
                'nullable',
                'in:in_person,virtual,hybrid',
            ],
            // Business Information
            'naics_codes' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'industry_connections' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'core_specialty_area' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'contract_vehicles' => [
                'nullable',
                'string',
                'max:1000',
            ],
            // Preferences
            'meeting_preference' => [
                'required',
                'in:in_person,virtual,hybrid,no_preference',
            ],
            // Event Specific
            'small_business_forum' => [
                'boolean',
            ],
            'small_business_matchmaker' => [
                'boolean',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'event_id.required' => 'The event ID is required.',
            'event_id.exists' => 'The selected event does not exist.',
            
            'terms_accepted.required' => 'You must accept the terms and conditions.',
            'terms_accepted.accepted' => 'You must accept the terms and conditions to register.',
            
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

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'event_id' => 'event',
            'terms_accepted' => 'terms and conditions',
            'emergency_contact_name' => 'emergency contact name',
            'emergency_contact_phone' => 'emergency contact phone',
            'dietary_requirements' => 'dietary requirements',
            'special_accommodations' => 'special accommodations',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $event = $this->route('event');
        
        if ($event) {
            $this->merge([
                'event_id' => $event->id,
                'user_id' => auth()->id(),
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $event = $this->route('event');
            $user = auth()->user();

            if (!$event || !$user) {
                return;
            }

            // Check if user is already registered
            if ($user->isRegisteredFor($event)) {
                $validator->errors()->add('event_id', 'You are already registered for this event.');
                return;
            }

            // Check if event is full
            if ($event->isFull()) {
                $validator->errors()->add('event_id', 'This event is full. Registration is no longer available.');
                return;
            }

            // Check if registration deadline has passed
            if ($event->registration_deadline && $event->registration_deadline->isPast()) {
                $validator->errors()->add('event_id', 'The registration deadline for this event has passed.');
                return;
            }

            // Check if event is published
            if (!$event->isPublished()) {
                $validator->errors()->add('event_id', 'This event is not currently open for registration.');
                return;
            }

            // Check if event has started
            if ($event->hasStarted()) {
                $validator->errors()->add('event_id', 'This event has already started. Registration is no longer available.');
                return;
            }
        });
    }
}
