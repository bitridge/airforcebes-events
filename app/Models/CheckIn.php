<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class CheckIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'checked_in_at',
        'checked_in_by',
        'check_in_method',
    ];

    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($checkIn) {
            // Set check-in time if not provided
            if (empty($checkIn->checked_in_at)) {
                $checkIn->checked_in_at = now();
            }

            // Set default check-in method if not provided
            if (empty($checkIn->check_in_method)) {
                $checkIn->check_in_method = 'qr';
            }
        });

        static::created(function ($checkIn) {
            Log::info('User checked in', [
                'check_in_id' => $checkIn->id,
                'registration_id' => $checkIn->registration_id,
                'user_id' => $checkIn->registration->user_id ?? null,
                'event_id' => $checkIn->registration->event_id ?? null,
                'method' => $checkIn->check_in_method,
                'checked_in_by' => $checkIn->checked_in_by,
                'checked_in_at' => $checkIn->checked_in_at,
            ]);
        });
    }

    // =====================================================
    // RELATIONSHIPS
    // =====================================================

    /**
     * Get the registration this check-in belongs to.
     */
    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    /**
     * Get the user who performed the check-in (for manual check-ins).
     */
    public function checkedInBy()
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    /**
     * Get the user who was checked in (through registration).
     */
    public function user()
    {
        return $this->hasOneThrough(User::class, Registration::class, 'id', 'id', 'registration_id', 'user_id');
    }

    /**
     * Get the event for this check-in (through registration).
     */
    public function event()
    {
        return $this->hasOneThrough(Event::class, Registration::class, 'id', 'id', 'registration_id', 'event_id');
    }

    // =====================================================
    // SCOPES
    // =====================================================

    /**
     * Scope for QR code check-ins.
     */
    public function scopeQrCode($query)
    {
        return $query->where('check_in_method', 'qr');
    }

    /**
     * Scope for manual check-ins.
     */
    public function scopeManual($query)
    {
        return $query->where('check_in_method', 'manual');
    }

    /**
     * Scope for ID-based check-ins.
     */
    public function scopeById($query)
    {
        return $query->where('check_in_method', 'id');
    }

    /**
     * Scope for check-ins by event.
     */
    public function scopeForEvent($query, $eventId)
    {
        return $query->whereHas('registration', function ($q) use ($eventId) {
            $q->where('event_id', $eventId);
        });
    }

    /**
     * Scope for check-ins by user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->whereHas('registration', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Scope for check-ins by admin.
     */
    public function scopeByAdmin($query, $adminId)
    {
        return $query->where('checked_in_by', $adminId);
    }

    /**
     * Scope for check-ins in date range.
     */
    public function scopeInDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('checked_in_at', [$startDate, $endDate]);
    }

    /**
     * Scope for recent check-ins.
     */
    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('checked_in_at', '>=', now()->subHours($hours));
    }

    /**
     * Scope for today's check-ins.
     */
    public function scopeToday($query)
    {
        return $query->whereDate('checked_in_at', today());
    }

    /**
     * Scope for self check-ins (QR code).
     */
    public function scopeSelfCheckIn($query)
    {
        return $query->whereNull('checked_in_by');
    }

    /**
     * Scope for admin-assisted check-ins.
     */
    public function scopeAdminAssisted($query)
    {
        return $query->whereNotNull('checked_in_by');
    }

    // =====================================================
    // ACCESSORS
    // =====================================================

    /**
     * Get formatted check-in time.
     */
    protected function formattedCheckedInAt(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->checked_in_at?->format('M j, Y H:i')
        );
    }

    /**
     * Get check-in method display name.
     */
    protected function checkInMethodDisplayName(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match($this->check_in_method) {
                    'qr' => 'QR Code',
                    'manual' => 'Manual',
                    'id' => 'ID Verification',
                    default => 'Unknown'
                };
            }
        );
    }

    /**
     * Get time since check-in in human readable format.
     */
    protected function timeSinceCheckIn(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->checked_in_at?->diffForHumans()
        );
    }

    /**
     * Get check-in method icon.
     */
    protected function checkInMethodIcon(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match($this->check_in_method) {
                    'qr' => 'qr-code',
                    'manual' => 'user-check',
                    'id' => 'id-card',
                    default => 'check'
                };
            }
        );
    }

    /**
     * Get check-in method color.
     */
    protected function checkInMethodColor(): Attribute
    {
        return Attribute::make(
            get: function () {
                return match($this->check_in_method) {
                    'qr' => 'green',
                    'manual' => 'blue',
                    'id' => 'purple',
                    default => 'gray'
                };
            }
        );
    }

    // =====================================================
    // STATIC METHODS
    // =====================================================

    /**
     * Record a check-in for a registration.
     */
    public static function recordCheckIn(
        Registration $registration, 
        string $method = 'qr', 
        ?User $checkedInBy = null
    ): ?CheckIn {
        // Validate that the registration can be checked in
        if (!$registration->canCheckIn()) {
            Log::warning('Check-in attempt failed - registration cannot be checked in', [
                'registration_id' => $registration->id,
                'method' => $method,
                'reason' => 'Cannot check in',
                'registration_status' => $registration->status,
                'event_status' => $registration->event?->status,
                'can_check_in_result' => $registration->canCheckIn(),
            ]);
            return null;
        }

        // Check if already checked in
        if ($registration->isCheckedIn()) {
            Log::warning('Check-in attempt failed - already checked in', [
                'registration_id' => $registration->id,
                'method' => $method,
            ]);
            return null;
        }

        // Create the check-in record
        $checkIn = new CheckIn([
            'registration_id' => $registration->id,
            'checked_in_at' => now(),
            'check_in_method' => $method,
            'checked_in_by' => $checkedInBy?->id,
        ]);

        try {
            if ($checkIn->save()) {
                Log::info('Check-in recorded successfully', [
                    'check_in_id' => $checkIn->id,
                    'registration_id' => $registration->id,
                    'method' => $method,
                    'checked_in_by' => $checkedInBy?->id,
                ]);
                
                return $checkIn;
            } else {
                Log::error('Failed to save check-in record', [
                    'registration_id' => $registration->id,
                    'method' => $method,
                    'check_in_data' => $checkIn->toArray(),
                ]);
                return null;
            }
        } catch (\Exception $e) {
            Log::error('Exception while saving check-in record', [
                'registration_id' => $registration->id,
                'method' => $method,
                'error' => $e->getMessage(),
                'check_in_data' => $checkIn->toArray(),
            ]);
            return null;
        }
    }

    /**
     * Record QR code check-in.
     */
    public static function recordQRCheckIn(Registration $registration): ?CheckIn
    {
        return static::recordCheckIn($registration, 'qr');
    }

    /**
     * Record manual check-in by admin.
     */
    public static function recordManualCheckIn(Registration $registration, User $admin): ?CheckIn
    {
        return static::recordCheckIn($registration, 'manual', $admin);
    }

    /**
     * Record ID-based check-in.
     */
    public static function recordIdCheckIn(Registration $registration, User $admin): ?CheckIn
    {
        return static::recordCheckIn($registration, 'id', $admin);
    }

    /**
     * Bulk record check-ins from registration codes.
     */
    public static function bulkCheckIn(array $registrationCodes, User $admin): array
    {
        $results = [
            'successful' => [],
            'failed' => [],
            'already_checked_in' => [],
        ];

        foreach ($registrationCodes as $code) {
            $registration = Registration::where('registration_code', $code)->first();

            if (!$registration) {
                $results['failed'][] = [
                    'code' => $code,
                    'reason' => 'Registration not found',
                ];
                continue;
            }

            if ($registration->isCheckedIn()) {
                $results['already_checked_in'][] = [
                    'code' => $code,
                    'user' => $registration->user->name,
                ];
                continue;
            }

            $checkIn = static::recordManualCheckIn($registration, $admin);

            if ($checkIn) {
                $results['successful'][] = [
                    'code' => $code,
                    'user' => $registration->user->name,
                    'check_in_id' => $checkIn->id,
                ];
            } else {
                $results['failed'][] = [
                    'code' => $code,
                    'reason' => 'Check-in failed',
                ];
            }
        }

        Log::info('Bulk check-in completed', [
            'admin_id' => $admin->id,
            'total_codes' => count($registrationCodes),
            'successful' => count($results['successful']),
            'failed' => count($results['failed']),
            'already_checked_in' => count($results['already_checked_in']),
        ]);

        return $results;
    }

    // =====================================================
    // INSTANCE METHODS
    // =====================================================

    /**
     * Check if this is a self check-in (QR code without admin).
     */
    public function isSelfCheckIn(): bool
    {
        return $this->check_in_method === 'qr' && !$this->checked_in_by;
    }

    /**
     * Check if this is an admin-assisted check-in.
     */
    public function isAdminAssisted(): bool
    {
        return !is_null($this->checked_in_by);
    }

    /**
     * Check if check-in is recent.
     */
    public function isRecent(int $hours = 2): bool
    {
        return $this->checked_in_at && $this->checked_in_at->gte(now()->subHours($hours));
    }

    /**
     * Get check-in summary for display.
     */
    public function getSummary(): array
    {
        return [
            'id' => $this->id,
            'checked_in_at' => $this->formatted_checked_in_at,
            'time_since' => $this->time_since_check_in,
            'method' => $this->check_in_method,
            'method_display' => $this->check_in_method_display_name,
            'method_icon' => $this->check_in_method_icon,
            'method_color' => $this->check_in_method_color,
            'is_self_check_in' => $this->isSelfCheckIn(),
            'is_admin_assisted' => $this->isAdminAssisted(),
            'checked_in_by' => $this->checkedInBy?->name,
            'registration' => [
                'id' => $this->registration->id,
                'code' => $this->registration->registration_code,
                'user' => $this->registration->user->name,
                'user_email' => $this->registration->user->email,
            ],
            'event' => [
                'id' => $this->registration->event->id,
                'title' => $this->registration->event->title,
                'date' => $this->registration->event->formatted_start_date,
            ],
        ];
    }

    /**
     * Get statistics for check-ins.
     */
    public static function getStatistics(?\Carbon $startDate = null, ?\Carbon $endDate = null): array
    {
        $query = static::query();

        if ($startDate && $endDate) {
            $query->whereBetween('checked_in_at', [$startDate, $endDate]);
        } elseif ($startDate) {
            $query->where('checked_in_at', '>=', $startDate);
        } elseif ($endDate) {
            $query->where('checked_in_at', '<=', $endDate);
        }

        $total = $query->count();
        $qrCheckIns = $query->clone()->where('check_in_method', 'qr')->count();
        $manualCheckIns = $query->clone()->where('check_in_method', 'manual')->count();
        $idCheckIns = $query->clone()->where('check_in_method', 'id')->count();
        $selfCheckIns = $query->clone()->whereNull('checked_in_by')->count();
        $adminAssisted = $query->clone()->whereNotNull('checked_in_by')->count();

        return [
            'total' => $total,
            'by_method' => [
                'qr' => $qrCheckIns,
                'manual' => $manualCheckIns,
                'id' => $idCheckIns,
            ],
            'by_assistance' => [
                'self' => $selfCheckIns,
                'admin_assisted' => $adminAssisted,
            ],
            'percentages' => [
                'qr' => $total > 0 ? round(($qrCheckIns / $total) * 100, 1) : 0,
                'manual' => $total > 0 ? round(($manualCheckIns / $total) * 100, 1) : 0,
                'id' => $total > 0 ? round(($idCheckIns / $total) * 100, 1) : 0,
                'self' => $total > 0 ? round(($selfCheckIns / $total) * 100, 1) : 0,
                'admin_assisted' => $total > 0 ? round(($adminAssisted / $total) * 100, 1) : 0,
            ],
        ];
    }

    /**
     * Get hourly check-in stats for a given date.
     */
    public static function getHourlyStats(\Carbon $date): array
    {
        $stats = [];
        
        for ($hour = 0; $hour < 24; $hour++) {
            $startHour = $date->copy()->hour($hour)->minute(0)->second(0);
            $endHour = $startHour->copy()->addHour();
            
            $count = static::whereBetween('checked_in_at', [$startHour, $endHour])->count();
            
            $stats[] = [
                'hour' => $hour,
                'time' => $startHour->format('g A'),
                'count' => $count,
            ];
        }

        return $stats;
    }

    /**
     * Validate check-in data from QR code.
     */
    public static function validateQRData(string $qrData): ?array
    {
        try {
            $data = json_decode($qrData, true);
            
            if (!$data || !isset($data['type']) || $data['type'] !== 'registration') {
                return null;
            }

            if (!isset($data['registration_code'])) {
                return null;
            }

            return $data;
        } catch (\Exception $e) {
            Log::warning('Invalid QR code data', [
                'qr_data' => $qrData,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
