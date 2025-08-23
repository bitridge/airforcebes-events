<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventFeedback extends Model
{
    use HasFactory;

    protected $table = 'event_feedback';

    protected $fillable = [
        'event_id',
        'user_id',
        'rating',
        'comment',
        'feedback_data',
        'is_anonymous',
        'is_approved',
    ];

    protected $casts = [
        'rating' => 'integer',
        'feedback_data' => 'array',
        'is_anonymous' => 'boolean',
        'is_approved' => 'boolean',
    ];

    /**
     * Get the event for this feedback.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user who provided this feedback.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include approved feedback.
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope a query to only include anonymous feedback.
     */
    public function scopeAnonymous($query)
    {
        return $query->where('is_anonymous', true);
    }

    /**
     * Scope a query to only include named feedback.
     */
    public function scopeNamed($query)
    {
        return $query->where('is_anonymous', false);
    }

    /**
     * Scope a query to filter by rating.
     */
    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * Scope a query to filter by minimum rating.
     */
    public function scopeMinRating($query, $rating)
    {
        return $query->where('rating', '>=', $rating);
    }

    /**
     * Scope a query to order by rating (highest first).
     */
    public function scopeByRatingDesc($query)
    {
        return $query->orderBy('rating', 'desc');
    }

    /**
     * Scope a query to order by creation date (newest first).
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Check if the feedback is approved.
     */
    public function isApproved(): bool
    {
        return $this->is_approved;
    }

    /**
     * Check if the feedback is anonymous.
     */
    public function isAnonymous(): bool
    {
        return $this->is_anonymous;
    }

    /**
     * Get the display name for the feedback author.
     */
    public function getAuthorNameAttribute(): string
    {
        if ($this->is_anonymous) {
            return 'Anonymous';
        }

        return $this->user->name ?? 'Unknown User';
    }

    /**
     * Get the star rating display.
     */
    public function getStarRatingAttribute(): string
    {
        $stars = '';
        for ($i = 1; $i <= 5; $i++) {
            if ($i <= $this->rating) {
                $stars .= '★';
            } else {
                $stars .= '☆';
            }
        }
        return $stars;
    }

    /**
     * Get the rating description.
     */
    public function getRatingDescriptionAttribute(): string
    {
        return match($this->rating) {
            1 => 'Poor',
            2 => 'Fair',
            3 => 'Good',
            4 => 'Very Good',
            5 => 'Excellent',
            default => 'Not Rated'
        };
    }

    /**
     * Approve the feedback.
     */
    public function approve(): bool
    {
        return $this->update(['is_approved' => true]);
    }

    /**
     * Reject the feedback.
     */
    public function reject(): bool
    {
        return $this->update(['is_approved' => false]);
    }

    /**
     * Get the average rating for an event.
     */
    public static function getAverageRatingForEvent($eventId): float
    {
        return static::where('event_id', $eventId)
            ->approved()
            ->avg('rating') ?? 0.0;
    }

    /**
     * Get the rating distribution for an event.
     */
    public static function getRatingDistributionForEvent($eventId): array
    {
        $distribution = [];
        for ($i = 1; $i <= 5; $i++) {
            $distribution[$i] = static::where('event_id', $eventId)
                ->approved()
                ->byRating($i)
                ->count();
        }
        return $distribution;
    }
}
