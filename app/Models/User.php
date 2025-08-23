<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
        'organization',
        'is_active',
        'created_by',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Get the registrations for the user.
     */
    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * Get the events created by this user.
     */
    public function createdEvents()
    {
        return $this->hasMany(Event::class, 'created_by');
    }

    /**
     * Get the check-ins for this user through registrations.
     */
    public function checkIns()
    {
        return $this->hasManyThrough(CheckIn::class, Registration::class);
    }

    /**
     * Get the user who created this user.
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the users created by this user.
     */
    public function createdUsers()
    {
        return $this->hasMany(User::class, 'created_by');
    }

    /**
     * Get the check-ins performed by this user (as admin).
     */
    public function performedCheckIns()
    {
        return $this->hasMany(CheckIn::class, 'checked_in_by');
    }

    /**
     * Get the waitlist entries for this user.
     */
    public function waitlistEntries()
    {
        return $this->hasMany(Waitlist::class);
    }

    /**
     * Get the feedback provided by this user.
     */
    public function eventFeedback()
    {
        return $this->hasMany(EventFeedback::class);
    }

    /**
     * Get the photos uploaded by this user.
     */
    public function uploadedPhotos()
    {
        return $this->hasMany(EventPhoto::class, 'uploaded_by');
    }

    /**
     * Get the event templates created by this user.
     */
    public function eventTemplates()
    {
        return $this->hasMany(EventTemplate::class, 'created_by');
    }

    /**
     * Get the events archived by this user.
     */
    public function archivedEvents()
    {
        return $this->hasMany(Event::class, 'archived_by');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    /**
     * Scope to get only admin users.
     */
    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    /**
     * Scope to get only attendee users.
     */
    public function scopeAttendees($query)
    {
        return $query->where('role', 'attendee')->orWhereNull('role');
    }

    /**
     * Scope to get only active users.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get inactive users.
     */
    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Scope to get users with registrations.
     */
    public function scopeWithRegistrations($query)
    {
        return $query->whereHas('registrations');
    }

    /**
     * Scope to get users by organization.
     */
    public function scopeByOrganization($query, $organization)
    {
        return $query->where('organization', $organization);
    }

    // =====================================================
    // ACCESSORS & MUTATORS
    // =====================================================

    /**
     * Get the user's full name.
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->name,
        );
    }

    /**
     * Get the user's formatted phone number.
     */
    protected function formattedPhone(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->phone) {
                    return null;
                }
                
                // Remove all non-numeric characters
                $phone = preg_replace('/[^0-9]/', '', $this->phone);
                
                // Format US phone numbers
                if (strlen($phone) === 10) {
                    return sprintf('(%s) %s-%s', 
                        substr($phone, 0, 3),
                        substr($phone, 3, 3),
                        substr($phone, 6, 4)
                    );
                }
                
                // For international or other formats, return as-is
                return $this->phone;
            }
        );
    }

    /**
     * Set the phone attribute with basic formatting.
     */
    protected function phone(): Attribute
    {
        return Attribute::make(
            set: function ($value) {
                if (!$value) {
                    return null;
                }
                
                // Store phone with minimal formatting
                return preg_replace('/[^0-9+\-\s\(\)]/', '', $value);
            }
        );
    }

    /**
     * Get the user's initials.
     */
    protected function initials(): Attribute
    {
        return Attribute::make(
            get: function () {
                $nameParts = explode(' ', trim($this->name));
                $initials = '';
                
                foreach ($nameParts as $part) {
                    if (!empty($part)) {
                        $initials .= strtoupper(substr($part, 0, 1));
                    }
                }
                
                return $initials ?: strtoupper(substr($this->name, 0, 2));
            }
        );
    }

    // =====================================================
    // METHODS
    // =====================================================

    /**
     * Check if user is an admin.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user is an attendee.
     */
    public function isAttendee(): bool
    {
        return $this->role === 'attendee' || $this->role === null;
    }

    /**
     * Check if user is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Get the count of events user has registered for.
     */
    public function getRegistrationCount(): int
    {
        return $this->registrations()->confirmed()->count();
    }

    /**
     * Get the count of events user has checked into.
     */
    public function getCheckInCount(): int
    {
        return $this->checkIns()->count();
    }

    /**
     * Check if user is registered for a specific event.
     */
    public function isRegisteredFor(Event $event): bool
    {
        return $this->registrations()
            ->where('event_id', $event->id)
            ->where('status', 'confirmed')
            ->exists();
    }

    /**
     * Check if user is on waitlist for a specific event.
     */
    public function isOnWaitlistFor(Event $event): bool
    {
        return $this->waitlistEntries()
            ->where('event_id', $event->id)
            ->where('status', 'waiting')
            ->exists();
    }

    /**
     * Check if user has provided feedback for a specific event.
     */
    public function hasProvidedFeedbackFor(Event $event): bool
    {
        return $this->eventFeedback()
            ->where('event_id', $event->id)
            ->exists();
    }

    /**
     * Get user's waitlist position for a specific event.
     */
    public function getWaitlistPosition(Event $event): ?int
    {
        $waitlistEntry = $this->waitlistEntries()
            ->where('event_id', $event->id)
            ->where('status', 'waiting')
            ->first();

        if (!$waitlistEntry) {
            return null;
        }

        return $waitlistEntry->position;
    }

    /**
     * Get user's feedback for a specific event.
     */
    public function getFeedbackFor(Event $event)
    {
        return $this->eventFeedback()
            ->where('event_id', $event->id)
            ->first();
    }

    /**
     * Check if user is checked in for a specific event.
     */
    public function isCheckedInFor(Event $event): bool
    {
        return $this->registrations()
            ->where('event_id', $event->id)
            ->whereHas('checkIn')
            ->exists();
    }

    /**
     * Get user's upcoming registrations.
     */
    public function getUpcomingRegistrations()
    {
        return $this->registrations()
            ->with('event')
            ->whereHas('event', function ($query) {
                $query->where('start_date', '>=', now()->toDateString());
            })
            ->where('status', 'confirmed')
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get user's past registrations.
     */
    public function getPastRegistrations()
    {
        return $this->registrations()
            ->with('event')
            ->whereHas('event', function ($query) {
                $query->where('start_date', '<', now()->toDateString());
            })
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Activate the user account.
     */
    public function activate(): bool
    {
        $this->is_active = true;
        return $this->save();
    }

    /**
     * Deactivate the user account.
     */
    public function deactivate(): bool
    {
        $this->is_active = false;
        return $this->save();
    }

    /**
     * Get user's role display name.
     */
    public function getRoleDisplayName(): string
    {
        return match($this->role) {
            'admin' => 'Administrator',
            'attendee' => 'Attendee',
            default => 'User'
        };
    }
}
