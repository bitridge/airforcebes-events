<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\Event;

class StoreEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => [
                'required',
                'string',
                'max:255',
                'min:3',
            ],
            'description' => [
                'required',
                'string',
                'min:10',
                'max:10000',
            ],
            'slug' => [
                'nullable',
                'string',
                'max:255',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                Rule::unique('events', 'slug'),
            ],
            'start_date' => [
                'required',
                'date',
                'after_or_equal:today',
            ],
            'end_date' => [
                'required',
                'date',
                'after_or_equal:start_date',
            ],
            'start_time' => [
                'nullable',
            ],
            'end_time' => [
                'nullable',
            ],
            'venue' => [
                'required',
                'string',
                'max:500',
                'min:3',
            ],
            'max_capacity' => [
                'nullable',
                'integer',
                'min:1',
                'max:100000',
            ],
            'registration_deadline' => [
                'nullable',
                'date',
                'before_or_equal:start_date',
                'after_or_equal:today',
            ],
            'status' => [
                'required',
                Rule::in(['draft', 'published', 'completed', 'cancelled']),
            ],
            'featured_image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,webp',
                'max:5120', // 5MB
                'dimensions:min_width=300,min_height=200,max_width=2000,max_height=2000',
            ],
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The event title is required.',
            'title.min' => 'The event title must be at least 3 characters.',
            'title.max' => 'The event title cannot exceed 255 characters.',
            
            'description.required' => 'The event description is required.',
            'description.min' => 'The event description must be at least 10 characters.',
            'description.max' => 'The event description cannot exceed 10,000 characters.',
            
            'slug.regex' => 'The slug must contain only lowercase letters, numbers, and hyphens.',
            'slug.unique' => 'This slug is already taken. Please choose a different one.',
            
            'start_date.required' => 'The start date is required.',
            'start_date.after_or_equal' => 'The start date cannot be in the past.',
            
            'end_date.required' => 'The end date is required.',
            'end_date.after_or_equal' => 'The end date must be on or after the start date.',
            

            
            'venue.required' => 'The venue is required.',
            'venue.min' => 'The venue must be at least 3 characters.',
            'venue.max' => 'The venue cannot exceed 500 characters.',
            
            'max_capacity.integer' => 'The maximum capacity must be a number.',
            'max_capacity.min' => 'The maximum capacity must be at least 1.',
            'max_capacity.max' => 'The maximum capacity cannot exceed 100,000.',
            
            'registration_deadline.date' => 'The registration deadline must be a valid date.',
            'registration_deadline.before_or_equal' => 'The registration deadline must be before or on the event start date.',
            'registration_deadline.after_or_equal' => 'The registration deadline cannot be in the past.',
            
            'status.required' => 'The event status is required.',
            'status.in' => 'The selected status is invalid.',
            
            'featured_image.image' => 'The featured image must be a valid image file.',
            'featured_image.mimes' => 'The featured image must be a JPEG, PNG, JPG, or WebP file.',
            'featured_image.max' => 'The featured image cannot exceed 5MB.',
            'featured_image.dimensions' => 'The featured image must be between 300x200 and 2000x2000 pixels.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'title' => 'event title',
            'description' => 'event description',
            'slug' => 'URL slug',
            'start_date' => 'start date',
            'end_date' => 'end date',
            'start_time' => 'start time',
            'end_time' => 'end time',
            'venue' => 'venue',
            'max_capacity' => 'maximum capacity',
            'registration_deadline' => 'registration deadline',
            'status' => 'event status',
            'featured_image' => 'featured image',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Auto-generate slug if not provided
        if (!$this->has('slug') || empty($this->slug)) {
            $this->merge([
                'slug' => \Illuminate\Support\Str::slug($this->title ?? ''),
            ]);
        }

        // Ensure slug is lowercase
        if ($this->has('slug')) {
            $this->merge([
                'slug' => strtolower($this->slug),
            ]);
        }

        // Set created_by to current user
        $this->merge([
            'created_by' => auth()->id(),
        ]);
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Custom validation: Check if registration deadline makes sense
            if ($this->registration_deadline && $this->start_date) {
                $deadline = \Carbon\Carbon::parse($this->registration_deadline);
                $startDate = \Carbon\Carbon::parse($this->start_date);
                
                if ($deadline->isAfter($startDate)) {
                    $validator->errors()->add('registration_deadline', 'Registration deadline must be before the event starts.');
                }
            }
        });
    }
}
