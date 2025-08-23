<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Registration;
use App\Models\CheckIn;

class CheckInRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'registration_code' => [
                'required',
                'string',
                'size:8',
                'regex:/^[A-Z0-9]{8}$/',
                'exists:registrations,registration_code',
            ],
            'check_in_method' => [
                'required',
                Rule::in(['qr', 'manual', 'id']),
            ],
            'qr_data' => [
                'nullable',
                'string',
                'json',
            ],
            'verification_notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'registration_code.required' => 'Registration code is required.',
            'registration_code.size' => 'Registration code must be exactly 8 characters.',
            'registration_code.regex' => 'Registration code must contain only uppercase letters and numbers.',
            'registration_code.exists' => 'Invalid registration code. Please check and try again.',
            
            'check_in_method.required' => 'Check-in method is required.',
            'check_in_method.in' => 'Invalid check-in method selected.',
            
            'qr_data.json' => 'QR code data must be valid JSON.',
            
            'verification_notes.max' => 'Verification notes cannot exceed 1,000 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'registration_code' => 'registration code',
            'check_in_method' => 'check-in method',
            'qr_data' => 'QR code data',
            'verification_notes' => 'verification notes',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Normalize registration code to uppercase
        if ($this->has('registration_code')) {
            $this->merge([
                'registration_code' => strtoupper(trim($this->registration_code)),
            ]);
        }

        // Set default check-in method if not provided
        if (!$this->has('check_in_method')) {
            $this->merge([
                'check_in_method' => 'qr',
            ]);
        }

        // Set checked_in_by for manual/id check-ins
        if (in_array($this->check_in_method, ['manual', 'id'])) {
            $this->merge([
                'checked_in_by' => auth()->id(),
            ]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->registration_code) {
                return;
            }

            // Find the registration
            $registration = Registration::where('registration_code', $this->registration_code)->first();
            
            if (!$registration) {
                $validator->errors()->add('registration_code', 'Registration not found.');
                return;
            }

            // Check if registration is confirmed
            if (!$registration->isConfirmed()) {
                $validator->errors()->add('registration_code', 'This registration is not confirmed and cannot be checked in.');
                return;
            }

            // Check if already checked in
            if ($registration->isCheckedIn()) {
                $checkIn = $registration->checkIn;
                $validator->errors()->add('registration_code', 
                    'This registration is already checked in at ' . 
                    $checkIn->formatted_checked_in_at . ' via ' . 
                    $checkIn->check_in_method_display_name . '.'
                );
                return;
            }

            // Check if registration can be checked in
            if (!$registration->canCheckIn()) {
                $validator->errors()->add('registration_code', 'This registration cannot be checked in at this time.');
                return;
            }

            // Validate QR code data if provided
            if ($this->qr_data) {
                $qrData = CheckIn::validateQRData($this->qr_data);
                if (!$qrData) {
                    $validator->errors()->add('qr_data', 'Invalid QR code data.');
                    return;
                }

                // Verify QR data matches registration
                if ($qrData['registration_code'] !== $this->registration_code) {
                    $validator->errors()->add('qr_data', 'QR code does not match the provided registration code.');
                    return;
                }
            }

            // Admin authorization for manual/id check-ins
            if (in_array($this->check_in_method, ['manual', 'id']) && !auth()->user()->isAdmin()) {
                $validator->errors()->add('check_in_method', 'You are not authorized to perform manual check-ins.');
                return;
            }

            // Store registration for use in controller
            $this->merge(['_registration' => $registration]);
        });
    }

    /**
     * Get the validated registration model.
     */
    public function getRegistration(): ?Registration
    {
        return $this->get('_registration');
    }
}
