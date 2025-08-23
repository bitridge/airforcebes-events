<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        $eventId = $this->route('event')->id;
        
        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => [
                'nullable', 
                'string', 
                'max:255',
                Rule::unique('events')->ignore($eventId)
            ],
            'description' => ['required', 'string', 'min:10'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i', 'after:start_time'],
            'venue' => ['required', 'string', 'max:255'],
            'max_capacity' => ['nullable', 'integer', 'min:1'],
            'registration_deadline' => [
                'nullable', 
                'date', 
                'before:start_date'
            ],
            'status' => ['required', 'in:draft,published,completed,cancelled'],
            'is_featured' => ['boolean'],
            'featured_image' => [
                'nullable',
                'image',
                'mimes:jpeg,png,jpg,gif,webp',
                'max:2048'
            ],
            'meta_description' => ['nullable', 'string', 'max:160'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Event title is required.',
            'title.max' => 'Event title cannot exceed 255 characters.',
            'slug.unique' => 'This URL slug is already taken.',
            'description.required' => 'Event description is required.',
            'description.min' => 'Event description must be at least 10 characters.',
            'start_date.required' => 'Start date is required.',
            'start_date.after_or_equal' => 'Start date must be today or in the future.',
            'end_date.required' => 'End date is required.',
            'end_date.after' => 'End date must be after start date.',
            'start_time.date_format' => 'Start time must be in HH:MM format.',
            'end_time.date_format' => 'End time must be in HH:MM format.',
            'end_time.after' => 'End time must be after start time.',
            'venue.required' => 'Venue is required.',
            'max_capacity.integer' => 'Maximum capacity must be a number.',
            'max_capacity.min' => 'Maximum capacity must be at least 1.',
            'registration_deadline.before' => 'Registration deadline must be before event start date.',
            'status.required' => 'Event status is required.',
            'status.in' => 'Invalid event status.',
            'featured_image.image' => 'Featured image must be an image file.',
            'featured_image.mimes' => 'Featured image must be a JPEG, PNG, JPG, GIF, or WebP file.',
            'featured_image.max' => 'Featured image cannot exceed 2MB.',
            'meta_description.max' => 'Meta description cannot exceed 160 characters.',
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => 'event title',
            'slug' => 'URL slug',
            'description' => 'event description',
            'start_date' => 'start date',
            'end_date' => 'end date',
            'start_time' => 'start time',
            'end_time' => 'end time',
            'venue' => 'venue',
            'max_capacity' => 'maximum capacity',
            'registration_deadline' => 'registration deadline',
            'status' => 'event status',
            'is_featured' => 'featured event',
            'featured_image' => 'featured image',
            'meta_description' => 'meta description',
        ];
    }
}
