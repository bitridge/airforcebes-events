<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Event extends Model
{
    /** @use HasFactory<\Database\Factories\EventFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'slug',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'venue',
        'max_capacity',
        'registration_deadline',
        'status',
        'featured_image',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'registration_deadline' => 'datetime',
            'max_capacity' => 'integer',
        ];
    }

    /**
     * Get the route key for the model.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->slug)) {
                $event->slug = Str::slug($event->title);
            }
        });
    }

    /**
     * Get the user who created this event.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the registrations for this event.
     */
    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * Get confirmed registrations for this event.
     */
    public function confirmedRegistrations()
    {
        return $this->hasMany(Registration::class)->where('status', 'confirmed');
    }

    /**
     * Check if the event is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Check if the event is full.
     */
    public function isFull(): bool
    {
        if (!$this->max_capacity) {
            return false;
        }

        return $this->confirmedRegistrations()->count() >= $this->max_capacity;
    }

    /**
     * Check if registration is open.
     */
    public function isRegistrationOpen(): bool
    {
        if (!$this->isPublished()) {
            return false;
        }

        if ($this->registration_deadline && $this->registration_deadline->isPast()) {
            return false;
        }

        return !$this->isFull();
    }

    /**
     * Get available spots.
     */
    public function getAvailableSpots(): ?int
    {
        if (!$this->max_capacity) {
            return null; // Unlimited
        }

        return max(0, $this->max_capacity - $this->confirmedRegistrations()->count());
    }

    /**
     * Scope for published events.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope for upcoming events.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now()->toDateString());
    }

    /**
     * Scope for events with open registration.
     */
    public function scopeOpenForRegistration($query)
    {
        return $query->published()
            ->where(function ($q) {
                $q->whereNull('registration_deadline')
                  ->orWhere('registration_deadline', '>', now());
            });
    }
}
