<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'registration_code',
        'qr_code_data',
        'qr_security_hash',
        'registration_date',
        'status',
        'notes',
        'type',
        'checkin_type',
    ];

    protected function casts(): array
    {
        return [
            'registration_date' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($registration) {
            // Generate unique registration code if not provided
            if (empty($registration->registration_code)) {
                $registration->registration_code = $registration->generateUniqueRegistrationCode();
            }

            // Set registration date if not provided
            if (empty($registration->registration_date)) {
                $registration->registration_date = now();
            }

            // Generate QR code data
            $registration->qr_code_data = $registration->generateQRCodeData();
        });

        static::created(function ($registration) {
            Log::info('Registration created', [
                'registration_id' => $registration->id,
                'user_id' => $registration->user_id,
                'event_id' => $registration->event_id,
                'registration_code' => $registration->registration_code,
            ]);
        });
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Get the event this registration belongs to.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user this registration belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the check-in record for this registration.
     */
    public function checkIn()
    {
        return $this->hasOne(CheckIn::class);
    }

    // =====================================================
    // SCOPES
    // =====================================================

    /**
     * Scope for confirmed registrations.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope for pending registrations.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for cancelled registrations.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope for registrations by event.
     */
    public function scopeForEvent($query, $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    /**
     * Scope for registrations by user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for checked-in registrations.
     */
    public function scopeCheckedIn($query)
    {
        return $query->whereHas('checkIn');
    }

    /**
     * Scope for not checked-in registrations.
     */
    public function scopeNotCheckedIn($query)
    {
        return $query->whereDoesntHave('checkIn');
    }

    /**
     * Scope for registrations in date range.
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('registration_date', [$startDate, $endDate]);
    }

    /**
     * Scope for recent registrations.
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('registration_date', '>=', now()->subDays($days));
    }

    // =====================================================
    // ACCESSORS
    // =====================================================

    /**
     * Get formatted registration date.
     */
    protected function formattedRegistrationDate(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->registration_date?->format('M j, Y H:i')
        );
    }

    /**
     * Get status badge color.
     */
    protected function statusBadgeColor(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match($this->status) {
                    'confirmed' => 'green',
                    'pending' => 'yellow',
                    'cancelled' => 'red',
                    default => 'gray'
                };
            }
        );
    }

    /**
     * Get status display name.
     */
    protected function statusDisplayName(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match($this->status) {
                    'confirmed' => 'Confirmed',
                    'pending' => 'Pending',
                    'cancelled' => 'Cancelled',
                    default => 'Unknown'
                };
            }
        );
    }

    /**
     * Get check-in status.
     */
    protected function checkInStatus(): Attribute
    {
        return Attribute::make(
            get: function () {
                $checkIn = $this->checkIn;
                
                if (!$checkIn) {
                    return [
                        'is_checked_in' => false,
                        'checked_in_at' => null,
                        'check_in_method' => null,
                        'checked_in_by' => null,
                    ];
                }

                return [
                    'is_checked_in' => true,
                    'checked_in_at' => $checkIn->checked_in_at,
                    'check_in_method' => $checkIn->check_in_method,
                    'checked_in_by' => $checkIn->checkedInBy?->full_name,
                ];
            }
        );
    }

    // =====================================================
    // USER DATA ACCESSORS (from user relationship)
    // =====================================================

    /**
     * Get user's first name (from user relationship).
     */
    public function getFirstNameAttribute(): string
    {
        return $this->user?->first_name ?? '';
    }

    /**
     * Get user's last name (from user relationship).
     */
    public function getLastNameAttribute(): string
    {
        return $this->user?->last_name ?? '';
    }

    /**
     * Get user's full name (from user relationship).
     */
    public function getFullNameAttribute(): string
    {
        return $this->user?->full_name ?? '';
    }

    /**
     * Get user's email (from user relationship).
     */
    public function getEmailAttribute(): string
    {
        return $this->user?->email ?? '';
    }

    /**
     * Get user's phone (from user relationship).
     */
    public function getPhoneAttribute(): string
    {
        return $this->user?->phone ?? '';
    }

    /**
     * Get user's organization name (from user relationship).
     */
    public function getOrganizationNameAttribute(): string
    {
        return $this->user?->organization_name ?? '';
    }

    /**
     * Get user's title (from user relationship).
     */
    public function getTitleAttribute(): string
    {
        return $this->user?->title ?? '';
    }

    /**
     * Get user's NAICS codes (from user relationship).
     */
    public function getNaicsCodesAttribute(): string
    {
        return $this->user?->naics_codes ?? '';
    }

    /**
     * Get user's industry connections (from user relationship).
     */
    public function getIndustryConnectionsAttribute(): string
    {
        return $this->user?->industry_connections ?? '';
    }

    /**
     * Get user's core specialty area (from user relationship).
     */
    public function getCoreSpecialtyAreaAttribute(): string
    {
        return $this->user?->core_specialty_area ?? '';
    }

    /**
     * Get user's contract vehicles (from user relationship).
     */
    public function getContractVehiclesAttribute(): string
    {
        return $this->user?->contract_vehicles ?? '';
    }

    /**
     * Get user's meeting preference (from user relationship).
     */
    public function getMeetingPreferenceAttribute(): string
    {
        return $this->user?->meeting_preference ?? 'no_preference';
    }

    /**
     * Get user's small business forum preference (from user relationship).
     */
    public function getSmallBusinessForumAttribute(): bool
    {
        return $this->user?->small_business_forum ?? false;
    }

    /**
     * Get user's small business matchmaker preference (from user relationship).
     */
    public function getSmallBusinessMatchmakerAttribute(): bool
    {
        return $this->user?->small_business_matchmaker ?? false;
    }

    // =====================================================
    // METHODS
    // =====================================================

    /**
     * Check if the user is checked in.
     */
    public function isCheckedIn(): bool
    {
        return $this->checkIn()->exists();
    }

    /**
     * Check if registration is confirmed.
     */
    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    /**
     * Check if registration is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if registration is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Check if registration can be cancelled.
     */
    public function canBeCancelled(): bool
    {
        if ($this->isCancelled()) {
            return false;
        }

        // Can't cancel if already checked in
        if ($this->isCheckedIn()) {
            return false;
        }

        // Can't cancel if event has started
        if ($this->event && $this->event->hasStarted()) {
            return false;
        }

        return true;
    }

    /**
     * Generate unique registration code.
     */
    public function generateUniqueRegistrationCode(): string
    {
        do {
            // Generate 8-character alphanumeric code
            $code = strtoupper(Str::random(8));
            
            // Ensure it doesn't exist
        } while (static::where('registration_code', $code)->exists());

        return $code;
    }

    /**
     * Generate security hash for QR code.
     */
    private function generateSecurityHash(): string
    {
        $data = [
            $this->id,
            $this->registration_code,
            $this->event_id,
            $this->user_id,
            $this->registration_date?->timestamp ?? now()->timestamp,
            'af-events-qr-security-2024', // SECURITY_SALT from QRCodeService
            config('app.key'),
        ];

        return hash('sha256', implode('|', $data));
    }

    /**
     * Generate QR code data.
     */
    public function generateQRCodeData(): string
    {
        return json_encode([
            'type' => 'airforce_event_registration',
            'version' => '2.0',
            'registration_id' => $this->id ?? 'pending',
            'registration_code' => $this->registration_code,
            'event_id' => $this->event_id,
            'user_id' => $this->user_id,
            'security_hash' => $this->qr_security_hash ?? $this->generateSecurityHash(),
            'generated_at' => now()->toISOString(),
            'expires_at' => now()->addYears(5)->toISOString(), // QR codes valid for 5 years
        ]);
    }

    /**
     * Generate QR code (returns QR code data string for external QR generator).
     */
    public function generateQRCode(): string
    {
        if (empty($this->qr_code_data)) {
            $this->qr_code_data = $this->generateQRCodeData();
            $this->save();
        }

        return $this->qr_code_data;
    }

    /**
     * Confirm the registration.
     */
    public function confirm(): bool
    {
        $this->status = 'confirmed';
        return $this->save();
    }

    /**
     * Cancel the registration.
     */
    public function cancel(): bool
    {
        if (!$this->canBeCancelled()) {
            return false;
        }

        $this->status = 'cancelled';
        $result = $this->save();

        if ($result) {
            Log::info('Registration cancelled', [
                'registration_id' => $this->id,
                'user_id' => $this->user_id,
                'event_id' => $this->event_id,
            ]);
        }

        return $result;
    }

    /**
     * Set registration as pending.
     */
    public function setPending(): bool
    {
        $this->status = 'pending';
        return $this->save();
    }

    /**
     * Check if this registration can be checked in.
     */
    public function canCheckIn(): bool
    {
        // Must be confirmed
        if (!$this->isConfirmed()) {
            Log::info('Registration cannot check in - not confirmed', [
                'registration_id' => $this->id,
                'status' => $this->status,
            ]);
            return false;
        }

        // Can't check in if already checked in
        if ($this->isCheckedIn()) {
            Log::info('Registration cannot check in - already checked in', [
                'registration_id' => $this->id,
            ]);
            return false;
        }

        // Check event status
        if (!$this->event) {
            Log::info('Registration cannot check in - no event', [
                'registration_id' => $this->id,
            ]);
            return false;
        }

        // Event should be published
        if (!$this->event->isPublished()) {
            Log::info('Registration cannot check in - event not published', [
                'registration_id' => $this->id,
                'event_status' => $this->event->status,
            ]);
            return false;
        }

        // Can check in if event is in progress or about to start (within 2 hours)
        if ($this->event->isInProgress()) {
            Log::info('Registration can check in - event in progress', [
                'registration_id' => $this->id,
                'event_id' => $this->event->id,
            ]);
            return true;
        }

        if ($this->event->hasStarted()) {
            Log::info('Registration can check in - event has started', [
                'registration_id' => $this->id,
                'event_id' => $this->event->id,
            ]);
            return true;
        }

        // Allow check-in up to 2 hours before event starts
        try {
            // Use the Event model's helper method for start datetime
            $startDateTime = $this->event->start_datetime;
            
            if (!$startDateTime) {
                Log::warning('Registration cannot check in - invalid event start time', [
                    'registration_id' => $this->id,
                    'event_id' => $this->event->id,
                ]);
                return false;
            }
            
            // Calculate 2 hours before the event starts
            $twoHoursBefore = $startDateTime->copy()->subHours(2);
            
            // Check if current time is after the 2-hour window
            $canCheckIn = now()->gte($twoHoursBefore);
            
            // For testing purposes, also allow check-in if event is within 90 days
            // This helps with testing events that are scheduled far in the future
            if (!$canCheckIn && now()->diffInDays($startDateTime) <= 90) {
                $canCheckIn = true;
                Log::info('Registration check-in allowed - within 90 days of event', [
                    'registration_id' => $this->id,
                    'event_id' => $this->event->id,
                    'days_until_event' => now()->diffInDays($startDateTime),
                ]);
            }
            
            Log::info('Registration check-in time check', [
                'registration_id' => $this->id,
                'event_id' => $this->event->id,
                'start_date' => $this->event->start_date->format('Y-m-d'),
                'start_time' => $this->event->formatted_start_time,
                'start_datetime' => $startDateTime->format('Y-m-d H:i:s'),
                'two_hours_before' => $twoHoursBefore->format('Y-m-d H:i:s'),
                'now' => now()->format('Y-m-d H:i:s'),
                'timezone' => config('app.timezone'),
                'can_check_in' => $canCheckIn,
                'days_until_event' => now()->diffInDays($startDateTime),
            ]);
            
            return $canCheckIn;
        } catch (\Exception $e) {
            Log::error('Error checking registration check-in time', [
                'registration_id' => $this->id,
                'event_id' => $this->event->id,
                'error' => $e->getMessage(),
                'start_date' => $this->event->start_date?->format('Y-m-d'),
                'start_time' => $this->event->start_time?->format('H:i:s'),
            ]);
            return false;
        }
    }

    /**
     * Get registration summary for display.
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'registration_code' => $this->registration_code,
            'status' => $this->status,
            'status_display' => $this->status_display_name,
            'registration_date' => $this->formatted_registration_date,
            'is_checked_in' => $this->isCheckedIn(),
            'can_cancel' => $this->canBeCancelled(),
            'can_check_in' => $this->canCheckIn(),
            'event' => [
                'id' => $this->event->id,
                'title' => $this->event->title,
                'slug' => $this->event->slug,
                'date_range' => $this->event->formatted_date_range,
                'venue' => $this->event->venue,
            ],
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->full_name,
                'email' => $this->user->email,
            ],
        ];
    }

    /**
     * Get check-in URL for this registration.
     */
    public function getCheckInUrl(): string
    {
        return route('admin.check-in.index', ['code' => $this->registration_code]);
    }

    /**
     * Get QR code URL for generating QR image.
     */
    public function getQRCodeUrl(): string
    {
        // This would integrate with a QR code service or library
        // For now, return the registration code as data
        return "data:text/plain;base64," . base64_encode($this->registration_code);
    }

    /**
     * Send confirmation email (placeholder for email functionality).
     */
    public function sendConfirmationEmail(): bool
    {
        // This would integrate with Laravel's mail system
        // For now, just log the action
        Log::info('Registration confirmation email sent', [
            'registration_id' => $this->id,
            'user_email' => $this->user->email,
            'event_title' => $this->event->title,
        ]);

        return true;
    }

    /**
     * Send cancellation email (placeholder for email functionality).
     */
    public function sendCancellationEmail(): bool
    {
        // This would integrate with Laravel's mail system
        Log::info('Registration cancellation email sent', [
            'registration_id' => $this->id,
            'user_email' => $this->user->email,
            'event_title' => $this->event->title,
        ]);

        return true;
    }

    /**
     * Get the number of days until the event.
     */
    public function getDaysUntilEvent(): int
    {
        if (!$this->event || !$this->event->start_date) {
            return 0;
        }

        return (int) max(0, now()->diffInDays($this->event->start_date, false));
    }

    /**
     * Check if this is a recent registration.
     */
    public function isRecent(int $days = 7): bool
    {
        return $this->registration_date && $this->registration_date->gte(now()->subDays($days));
    }

    /**
     * Get status badge color for display.
     */
    public function getStatusBadgeColorAttribute(): string
    {
        return match($this->status) {
            'confirmed' => 'bg-green-100 text-green-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            'cancelled' => 'bg-red-100 text-red-800',
            'waitlisted' => 'bg-purple-100 text-purple-800',
            default => 'bg-gray-100 text-gray-800',
        };
    }

    /**
     * Get formatted registration date for display.
     */
    public function getFormattedRegistrationDateAttribute(): string
    {
        if (!$this->registration_date) {
            return 'N/A';
        }
        
        return $this->registration_date->format('M j, Y \a\t H:i');
    }

    /**
     * Get the display name (first + last or user name)
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->user?->full_name ?? 'Unknown';
    }

    /**
     * Get the contact email (from user)
     */
    public function getContactEmailAttribute(): string
    {
        return $this->user?->email ?? 'No email';
    }

    /**
     * Get the contact phone (from user)
     */
    public function getContactPhoneAttribute(): string
    {
        return $this->user?->phone ?? 'No phone';
    }
}
