<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Waitlist extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'status',
        'joined_at',
        'notified_at',
        'notes',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'notified_at' => 'datetime',
    ];

    /**
     * Get the event for this waitlist entry.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user for this waitlist entry.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include waiting entries.
     */
    public function scopeWaiting($query)
    {
        return $query->where('status', 'waiting');
    }

    /**
     * Scope a query to only include notified entries.
     */
    public function scopeNotified($query)
    {
        return $query->where('status', 'notified');
    }

    /**
     * Scope a query to only include registered entries.
     */
    public function scopeRegistered($query)
    {
        return $query->where('status', 'registered');
    }

    /**
     * Scope a query to only include cancelled entries.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    /**
     * Scope a query to order by join date (oldest first).
     */
    public function scopeByJoinDate($query)
    {
        return $query->orderBy('joined_at');
    }

    /**
     * Check if the waitlist entry is waiting.
     */
    public function isWaiting(): bool
    {
        return $this->status === 'waiting';
    }

    /**
     * Check if the waitlist entry has been notified.
     */
    public function isNotified(): bool
    {
        return $this->status === 'notified';
    }

    /**
     * Check if the waitlist entry has been registered.
     */
    public function isRegistered(): bool
    {
        return $this->status === 'registered';
    }

    /**
     * Check if the waitlist entry has been cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Mark the waitlist entry as notified.
     */
    public function markAsNotified(): bool
    {
        return $this->update([
            'status' => 'notified',
            'notified_at' => now(),
        ]);
    }

    /**
     * Mark the waitlist entry as registered.
     */
    public function markAsRegistered(): bool
    {
        return $this->update(['status' => 'registered']);
    }

    /**
     * Mark the waitlist entry as cancelled.
     */
    public function markAsCancelled(): bool
    {
        return $this->update(['status' => 'cancelled']);
    }

    /**
     * Get the wait time in days.
     */
    public function getWaitTimeInDaysAttribute(): int
    {
        return $this->joined_at->diffInDays(now());
    }

    /**
     * Get the wait time in hours.
     */
    public function getWaitTimeInHoursAttribute(): int
    {
        return $this->joined_at->diffInHours(now());
    }

    /**
     * Get the position in the waitlist.
     */
    public function getPositionAttribute(): int
    {
        return static::where('event_id', $this->event_id)
            ->where('status', 'waiting')
            ->where('joined_at', '<=', $this->joined_at)
            ->count();
    }
}
