<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

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
        'category_id',
        'tags',
        'series_id',
        'series_order',
        'has_waitlist',
        'waitlist_capacity',
        'early_bird_enabled',
        'early_bird_deadline',
        'early_bird_price',
        'regular_price',
        'requires_confirmation',
        'confirmation_message',
        'has_custom_fields',
        'is_archived',
        'archived_at',
        'archived_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'start_time' => 'string',
            'end_time' => 'string',
            'registration_deadline' => 'datetime',
            'max_capacity' => 'integer',
            'tags' => 'array',
            'has_waitlist' => 'boolean',
            'early_bird_enabled' => 'boolean',
            'early_bird_deadline' => 'datetime',
            'early_bird_price' => 'decimal:2',
            'regular_price' => 'decimal:2',
            'requires_confirmation' => 'boolean',
            'has_custom_fields' => 'boolean',
            'is_archived' => 'boolean',
            'archived_at' => 'datetime',
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

        static::updating(function ($event) {
            if ($event->isDirty('title') && empty($event->slug)) {
                $event->slug = Str::slug($event->title);
            }
        });
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

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
     * Get cancelled registrations for this event.
     */
    public function cancelledRegistrations()
    {
        return $this->hasMany(Registration::class)->where('status', 'cancelled');
    }

    /**
     * Get check-ins for this event through registrations.
     */
    public function checkIns()
    {
        return $this->hasManyThrough(CheckIn::class, Registration::class);
    }

    /**
     * Get users who registered for this event.
     */
    public function registeredUsers()
    {
        return $this->belongsToMany(User::class, 'registrations')
            ->wherePivot('status', 'confirmed')
            ->withPivot(['registration_code', 'registration_date', 'status'])
            ->withTimestamps();
    }

    /**
     * Get users who checked in for this event.
     */
    public function checkedInUsers()
    {
        return $this->belongsToMany(User::class, 'registrations')
            ->whereHas('checkIn')
            ->withPivot(['registration_code', 'registration_date'])
            ->withTimestamps();
    }

    /**
     * Get the category for this event.
     */
    public function category()
    {
        return $this->belongsTo(EventCategory::class);
    }

    /**
     * Get the series for this event.
     */
    public function series()
    {
        return $this->belongsTo(EventSeries::class);
    }

    /**
     * Get the waitlist entries for this event.
     */
    public function waitlist()
    {
        return $this->hasMany(Waitlist::class);
    }

    /**
     * Get the feedback for this event.
     */
    public function feedback()
    {
        return $this->hasMany(EventFeedback::class);
    }

    /**
     * Get the photos for this event.
     */
    public function photos()
    {
        return $this->hasMany(EventPhoto::class);
    }

    /**
     * Get the custom registration fields for this event.
     */
    public function customFields()
    {
        return $this->hasMany(CustomRegistrationField::class);
    }

    /**
     * Get the user who archived this event.
     */
    public function archivedBy()
    {
        return $this->belongsTo(User::class, 'archived_by');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    /**
     * Scope for published events.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope for draft events.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope for completed events.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope for cancelled events.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope for upcoming events.
     */
    public function scopeUpcoming($query)
    {
        return $query->where('start_date', '>=', now()->toDateString());
    }

    /**
     * Scope for past events.
     */
    public function scopePast($query)
    {
        return $query->where('end_date', '<', now()->toDateString());
    }

    /**
     * Scope for active events (published and upcoming).
     */
    public function scopeActive($query)
    {
        return $query->published()->upcoming();
    }

    /**
     * Scope for events with open registration.
     */
    public function scopeOpenForRegistration($query)
    {
        return $query->published()
            ->upcoming()
            ->where(function ($q) {
                $q->whereNull('registration_deadline')
                  ->orWhere('registration_deadline', '>', now());
            });
    }

    /**
     * Scope for events with capacity.
     */
    public function scopeWithCapacity($query)
    {
        return $query->whereNotNull('max_capacity');
    }

    /**
     * Scope for events by venue.
     */
    public function scopeByVenue($query, $venue)
    {
        return $query->where('venue', 'like', "%{$venue}%");
    }

    /**
     * Scope for events in date range.
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->where('start_date', '>=', $startDate)
            ->where('end_date', '<=', $endDate);
    }

    /**
     * Scope for events created by user.
     */
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope for events by category.
     */
    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    /**
     * Scope for events in series.
     */
    public function scopeInSeries($query, $seriesId)
    {
        return $query->where('series_id', $seriesId);
    }

    /**
     * Scope for events with waitlist.
     */
    public function scopeWithWaitlist($query)
    {
        return $query->where('has_waitlist', true);
    }

    /**
     * Scope for events with early bird pricing.
     */
    public function scopeWithEarlyBird($query)
    {
        return $query->where('early_bird_enabled', true);
    }

    /**
     * Scope for events requiring confirmation.
     */
    public function scopeRequiringConfirmation($query)
    {
        return $query->where('requires_confirmation', true);
    }

    /**
     * Scope for events with custom fields.
     */
    public function scopeWithCustomFields($query)
    {
        return $query->where('has_custom_fields', true);
    }

    /**
     * Scope for archived events.
     */
    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    /**
     * Scope for non-archived events.
     */
    public function scopeNotArchived($query)
    {
        return $query->where('is_archived', false);
    }

    /**
     * Scope for events by tags.
     */
    public function scopeByTags($query, array $tags)
    {
        return $query->whereJsonContains('tags', $tags);
    }

    /**
     * Scope for events with any of the given tags.
     */
    public function scopeWithAnyTags($query, array $tags)
    {
        return $query->where(function ($q) use ($tags) {
            foreach ($tags as $tag) {
                $q->orWhereJsonContains('tags', $tag);
            }
        });
    }

    // =====================================================
    // ACCESSORS
    // =====================================================

    /**
     * Get formatted start date.
     */
    protected function formattedStartDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->start_date?->format('M j, Y')
        );
    }

    /**
     * Get formatted end date.
     */
    protected function formattedEndDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->end_date?->format('M j, Y')
        );
    }

    /**
     * Get formatted date range.
     */
    protected function formattedDateRange(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->start_date) return null;
                
                $start = $this->start_date->format('M j, Y');
                
                if ($this->start_date->isSameDay($this->end_date)) {
                    return $start;
                }
                
                return $start . ' - ' . $this->end_date->format('M j, Y');
            }
        );
    }

    /**
     * Get formatted start time in 24-hour format.
     */
    protected function formattedStartTime(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->start_time ? Carbon::createFromFormat('H:i:s', $this->start_time)->format('H:i') : null
        );
    }

    /**
     * Get formatted end time in 24-hour format.
     */
    protected function formattedEndTime(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->end_time ? Carbon::createFromFormat('H:i:s', $this->end_time)->format('H:i') : null
        );
    }

    /**
     * Get formatted time range in 24-hour format.
     */
    protected function formattedTimeRange(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->start_time) return null;
                
                $start = Carbon::createFromFormat('H:i:s', $this->start_time)->format('H:i');
                $end = $this->end_time ? Carbon::createFromFormat('H:i:s', $this->end_time)->format('H:i') : null;
                
                return $end ? "{$start} - {$end}" : $start;
            }
        );
    }

    /**
     * Get capacity status information.
     */
    protected function capacityStatus(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->max_capacity) {
                    return [
                        'total' => null,
                        'registered' => $this->confirmedRegistrations()->count(),
                        'available' => null,
                        'percentage' => 0,
                        'is_full' => false,
                        'has_capacity' => false,
                    ];
                }

                $registered = $this->confirmedRegistrations()->count();
                $available = max(0, $this->max_capacity - $registered);
                $percentage = $this->max_capacity > 0 ? round(($registered / $this->max_capacity) * 100, 1) : 0;

                return [
                    'total' => $this->max_capacity,
                    'registered' => $registered,
                    'available' => $available,
                    'percentage' => $percentage,
                    'is_full' => $available === 0,
                    'has_capacity' => true,
                ];
            }
        );
    }

    /**
     * Get registration status information.
     */
    protected function registrationStatus(): Attribute
    {
        return Attribute::make(
            get: function () {
                $isOpen = $this->isRegistrationOpen();
                $reason = null;

                if (!$this->isPublished()) {
                    $reason = 'Event not published';
                } elseif ($this->registration_deadline && $this->registration_deadline->isPast()) {
                    $reason = 'Registration deadline passed';
                } elseif ($this->isFull()) {
                    $reason = 'Event is full';
                } elseif ($this->start_date < now()->toDateString()) {
                    $reason = 'Event has started';
                }

                return [
                    'is_open' => $isOpen,
                    'reason' => $reason,
                    'deadline' => $this->registration_deadline,
                    'deadline_formatted' => $this->registration_deadline?->format('M j, Y H:i'),
                ];
            }
        );
    }

    /**
     * Get event duration in human readable format.
     */
    protected function duration(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->start_date || !$this->end_date) {
                    return null;
                }

                $days = $this->start_date->diffInDays($this->end_date) + 1;
                
                if ($days === 1) {
                    return '1 day';
                } else {
                    return "{$days} days";
                }
            }
        );
    }

    /**
     * Get days until event starts.
     */
    protected function daysUntilStart(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->start_date) return null;
                
                $days = now()->diffInDays($this->start_date, false);
                
                if ($days < 0) {
                    return 0; // Event has started
                }
                
                return $days;
            }
        );
    }

    // =====================================================
    // METHODS
    // =====================================================

    /**
     * Check if the event is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Check if the event is draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if the event is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if the event is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if the event is upcoming.
     */
    public function isUpcoming(): bool
    {
        return $this->start_date && $this->start_date >= now()->toDateString();
    }

    /**
     * Check if the event is past.
     */
    public function isPast(): bool
    {
        return $this->end_date && $this->end_date < now()->toDateString();
    }

    /**
     * Check if the event is active (published and upcoming).
     */
    public function isActive(): bool
    {
        return $this->isPublished() && $this->isUpcoming();
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
        if (!$this->isPublished() || !$this->isUpcoming()) {
            return false;
        }

        if ($this->registration_deadline && $this->registration_deadline->isPast()) {
            return false;
        }

        return !$this->isFull();
    }

    /**
     * Check if user can register for this event.
     */
    public function canRegister(?User $user = null): bool
    {
        if (!$this->isRegistrationOpen()) {
            return false;
        }

        if ($user && $user->isRegisteredFor($this)) {
            return false;
        }

        return true;
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
     * Get QR code data for the event.
     */
    public function getQRCodeData(): string
    {
        return json_encode([
            'type' => 'event',
            'event_id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'date' => $this->start_date->toDateString(),
            'venue' => $this->venue,
        ]);
    }

    /**
     * Get check-in statistics.
     */
    public function getCheckInStats(): array
    {
        $totalRegistrations = $this->confirmedRegistrations()->count();
        $checkedIn = $this->checkIns()->count();
        $notCheckedIn = $totalRegistrations - $checkedIn;
        $checkInRate = $totalRegistrations > 0 ? round(($checkedIn / $totalRegistrations) * 100, 1) : 0;

        return [
            'total_registrations' => $totalRegistrations,
            'checked_in' => $checkedIn,
            'not_checked_in' => $notCheckedIn,
            'check_in_rate' => $checkInRate,
        ];
    }

    /**
     * Get formatted date range for display.
     */
    public function getFormattedDateRangeAttribute(): string
    {
        if ($this->start_date->isSameDay($this->end_date)) {
            return $this->start_date->format('F j, Y');
        }
        
        return $this->start_date->format('F j') . ' - ' . $this->end_date->format('F j, Y');
    }

    /**
     * Get formatted time range for display.
     */
    public function getFormattedTimeRangeAttribute(): string
    {
        if ($this->start_time && $this->end_time) {
            return $this->getFormattedStartTimeAttribute() . ' - ' . $this->getFormattedEndTimeAttribute();
        }
        
        return 'Time TBD';
    }

    /**
     * Get registration statistics.
     */
    public function getRegistrationStats(): array
    {
        return [
            'total' => $this->registrations()->count(),
            'confirmed' => $this->confirmedRegistrations()->count(),
            'pending' => $this->registrations()->where('status', 'pending')->count(),
            'cancelled' => $this->cancelledRegistrations()->count(),
        ];
    }

    /**
     * Publish the event.
     */
    public function publish(): bool
    {
        $this->status = 'published';
        return $this->save();
    }

    /**
     * Mark event as completed.
     */
    public function complete(): bool
    {
        $this->status = 'completed';
        return $this->save();
    }

    /**
     * Cancel the event.
     */
    public function cancel(): bool
    {
        $this->status = 'cancelled';
        return $this->save();
    }

    /**
     * Generate unique slug.
     */
    public function generateUniqueSlug(?string $title = null): string
    {
        $title = $title ?: $this->title;
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (static::where('slug', $slug)->where('id', '!=', $this->id)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get formatted start time in 24-hour format.
     */
    public function getFormattedStartTimeAttribute(): string
    {
        if (!$this->start_time) {
            return '';
        }
        
        // If it's already in HH:MM format, return as is
        if (preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $this->start_time)) {
            return $this->start_time;
        }
        
        // If it's in 12-hour format, convert to 24-hour
        if (preg_match('/^([0-9]{1,2}):([0-5][0-9])\s*(AM|PM)$/i', $this->start_time, $matches)) {
            $hour = (int)$matches[1];
            $minute = $matches[2];
            $period = strtoupper($matches[3]);
            
            if ($period === 'PM' && $hour !== 12) {
                $hour += 12;
            } elseif ($period === 'AM' && $hour === 12) {
                $hour = 0;
            }
            
            return sprintf('%02d:%02d', $hour, $minute);
        }
        
        // Return as is if format is unrecognized
        return $this->start_time;
    }

    /**
     * Get formatted end time in 24-hour format.
     */
    public function getFormattedEndTimeAttribute(): string
    {
        if (!$this->end_time) {
            return '';
        }
        
        // If it's already in HH:MM format, return as is
        if (preg_match('/^([01]?[0-9]|2[0-3]):[0-5][0-9]$/', $this->end_time)) {
            return $this->end_time;
        }
        
        // If it's in 12-hour format, convert to 24-hour
        if (preg_match('/^([0-9]{1,2}):([0-5][0-9])\s*(AM|PM)$/i', $this->end_time, $matches)) {
            $hour = (int)$matches[1];
            $minute = $matches[2];
            $period = strtoupper($matches[3]);
            
            if ($period === 'PM' && $hour !== 12) {
                $hour += 12;
            } elseif ($period === 'AM' && $hour === 12) {
                $hour = 0;
            }
            
            return sprintf('%02d:%02d', $hour, $minute);
        }
        
        // Return as is if format is unrecognized
        return $this->end_time;
    }

    /**
     * Get start time in 12-hour format for display.
     */
    public function getDisplayStartTimeAttribute(): string
    {
        if (!$this->start_time) {
            return '';
        }
        
        // Convert 24-hour format to 12-hour display format
        if (preg_match('/^([01]?[0-9]|2[0-3]):([0-5][0-9])$/', $this->start_time, $matches)) {
            $hour = (int)$matches[1];
            $minute = $matches[2];
            
            if ($hour === 0) {
                return sprintf('12:%02d AM', $minute);
            } elseif ($hour === 12) {
                return sprintf('12:%02d PM', $minute);
            } elseif ($hour > 12) {
                return sprintf('%d:%02d PM', $hour - 12, $minute);
            } else {
                return sprintf('%d:%02d AM', $hour, $minute);
            }
        }
        
        // If it's already in 12-hour format, return as is
        if (preg_match('/^([0-9]{1,2}):([0-5][0-9])\s*(AM|PM)$/i', $this->start_time)) {
            return $this->start_time;
        }
        
        return $this->start_time;
    }

    /**
     * Get end time in 12-hour format for display.
     */
    public function getDisplayEndTimeAttribute(): string
    {
        if (!$this->end_time) {
            return '';
        }
        
        // Convert 24-hour format to 12-hour display format
        if (preg_match('/^([01]?[0-9]|2[0-3]):([0-5][0-9])$/', $this->end_time, $matches)) {
            $hour = (int)$matches[1];
            $minute = $matches[2];
            
            if ($hour === 0) {
                return sprintf('12:%02d AM', $minute);
            } elseif ($hour === 12) {
                return sprintf('12:%02d PM', $minute);
            } elseif ($hour > 12) {
                return sprintf('%d:%02d PM', $hour - 12, $minute);
            } else {
                return sprintf('%d:%02d AM', $hour, $minute);
            }
        }
        
        // If it's already in 12-hour format, return as is
        if (preg_match('/^([0-9]{1,2}):([0-5][0-9])\s*(AM|PM)$/i', $this->end_time)) {
            return $this->end_time;
        }
        
        return $this->end_time;
    }

    /**
     * Get formatted time range in 12-hour format for display.
     */
    public function getDisplayTimeRangeAttribute(): string
    {
        if ($this->start_time && $this->end_time) {
            return $this->getDisplayStartTimeAttribute() . ' - ' . $this->getDisplayEndTimeAttribute();
        }
        
        return 'Time TBD';
    }

    /**
     * Get start datetime as Carbon instance.
     */
    public function getStartDateTimeAttribute(): ?Carbon
    {
        if (!$this->start_date || !$this->start_time) {
            return null;
        }

        return Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $this->start_date->format('Y-m-d') . ' ' . $this->start_time,
            config('app.timezone')
        );
    }

    /**
     * Get end datetime as Carbon instance.
     */
    public function getEndDateTimeAttribute(): ?Carbon
    {
        if (!$this->end_date || !$this->end_time) {
            return null;
        }

        return Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $this->end_date->format('Y-m-d') . ' ' . $this->end_time,
            config('app.timezone')
        );
    }

    /**
     * Check if event has started.
     */
    public function hasStarted(): bool
    {
        $startDateTime = $this->start_datetime;
        return $startDateTime ? now()->gte($startDateTime) : false;
    }

    /**
     * Check if event has ended.
     */
    public function hasEnded(): bool
    {
        $endDateTime = $this->end_datetime;
        return $endDateTime ? now()->gt($endDateTime) : false;
    }

    /**
     * Check if event is currently in progress.
     */
    public function isInProgress(): bool
    {
        return $this->hasStarted() && !$this->hasEnded();
    }

    /**
     * Get status display name.
     */
    public function getStatusDisplayName(): string
    {
        return match($this->status) {
            'draft' => 'Draft',
            'published' => 'Published',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => 'Unknown'
        };
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColor(): string
    {
        return match($this->status) {
            'draft' => 'gray',
            'published' => 'green',
            'completed' => 'blue',
            'cancelled' => 'red',
            default => 'gray'
        };
    }

    /**
     * Check if the event is archived.
     */
    public function isArchived(): bool
    {
        return $this->is_archived;
    }

    /**
     * Check if the event has waitlist enabled.
     */
    public function hasWaitlist(): bool
    {
        return $this->has_waitlist;
    }

    /**
     * Check if the event has early bird pricing.
     */
    public function hasEarlyBirdPricing(): bool
    {
        return $this->early_bird_enabled;
    }

    /**
     * Check if early bird pricing is still available.
     */
    public function isEarlyBirdAvailable(): bool
    {
        if (!$this->hasEarlyBirdPricing()) {
            return false;
        }

        return $this->early_bird_deadline && $this->early_bird_deadline->isFuture();
    }

    /**
     * Get the current price for the event.
     */
    public function getCurrentPrice(): ?float
    {
        if (!$this->regular_price) {
            return null;
        }

        if ($this->isEarlyBirdAvailable()) {
            return $this->early_bird_price;
        }

        return $this->regular_price;
    }

    /**
     * Check if the event requires confirmation.
     */
    public function requiresConfirmation(): bool
    {
        return $this->requires_confirmation;
    }

    /**
     * Check if the event has custom fields.
     */
    public function hasCustomFields(): bool
    {
        return $this->has_custom_fields;
    }

    /**
     * Get waitlist statistics.
     */
    public function getWaitlistStats(): array
    {
        if (!$this->hasWaitlist()) {
            return [
                'enabled' => false,
                'capacity' => 0,
                'waiting' => 0,
                'available' => 0,
            ];
        }

        $waiting = $this->waitlist()->waiting()->count();
        $capacity = $this->waitlist_capacity ?? 0;
        $available = $capacity > 0 ? max(0, $capacity - $waiting) : null;

        return [
            'enabled' => true,
            'capacity' => $capacity,
            'waiting' => $waiting,
            'available' => $available,
        ];
    }

    /**
     * Check if waitlist is full.
     */
    public function isWaitlistFull(): bool
    {
        if (!$this->hasWaitlist()) {
            return false;
        }

        $stats = $this->getWaitlistStats();
        return $stats['capacity'] > 0 && $stats['available'] === 0;
    }

    /**
     * Check if user can join waitlist.
     */
    public function canJoinWaitlist(?User $user = null): bool
    {
        if (!$this->hasWaitlist() || $this->isFull()) {
            return false;
        }

        if ($user && $user->isOnWaitlistFor($this)) {
            return false;
        }

        return !$this->isWaitlistFull();
    }

    /**
     * Archive the event.
     */
    public function archive(?User $user = null): bool
    {
        $this->is_archived = true;
        $this->archived_at = now();
        $this->archived_by = $user?->id ?? auth()->id();
        return $this->save();
    }

    /**
     * Unarchive the event.
     */
    public function unarchive(): bool
    {
        $this->is_archived = false;
        $this->archived_at = null;
        $this->archived_by = null;
        return $this->save();
    }

    /**
     * Get the event's category name.
     */
    public function getCategoryName(): ?string
    {
        return $this->category?->name;
    }

    /**
     * Get the event's series name.
     */
    public function getSeriesName(): ?string
    {
        return $this->series?->name;
    }

    /**
     * Get the event's tags as an array.
     */
    public function getTagsArray(): array
    {
        return $this->tags ?? [];
    }

    /**
     * Check if the event has a specific tag.
     */
    public function hasTag(string $tag): bool
    {
        return in_array($tag, $this->getTagsArray());
    }

    /**
     * Get the event's average rating.
     */
    public function getAverageRating(): float
    {
        return $this->feedback()->approved()->avg('rating') ?? 0.0;
    }

    /**
     * Get the event's rating count.
     */
    public function getRatingCount(): int
    {
        return $this->feedback()->approved()->count();
    }

    /**
     * Get the event's featured photos.
     */
    public function getFeaturedPhotos()
    {
        return $this->photos()->featured()->ordered()->get();
    }

    /**
     * Get the event's custom registration fields.
     */
    public function getActiveCustomFields()
    {
        return $this->customFields()->active()->ordered()->get();
    }

    /**
     * Get the featured image URL.
     */
    public function getFeaturedImageUrlAttribute(): ?string
    {
        if (!$this->featured_image) {
            return null;
        }

        return Storage::disk('public')->url($this->featured_image);
    }

    /**
     * Get the featured image path.
     */
    public function getFeaturedImagePathAttribute(): ?string
    {
        if (!$this->featured_image) {
            return null;
        }

        return Storage::disk('public')->path($this->featured_image);
    }

    /**
     * Check if the event has a featured image.
     */
    public function getHasFeaturedImageAttribute(): bool
    {
        return !empty($this->featured_image) && Storage::disk('public')->exists($this->featured_image);
    }
}
