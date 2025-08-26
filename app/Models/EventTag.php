<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class EventTag extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tag) {
            if (empty($tag->slug)) {
                $tag->slug = Str::slug($tag->full_name);
            }
        });

        static::updating(function ($tag) {
            if ($tag->isDirty('name') && empty($tag->slug)) {
                $tag->slug = Str::slug($tag->full_name);
            }
        });
    }

    /**
     * Get the events that have this tag.
     */
    public function events(): BelongsToMany
    {
        return $this->belongsToMany(Event::class, 'event_tag', 'tag_id', 'event_id');
    }

    /**
     * Scope a query to only include active tags.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by name.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    /**
     * Get the tag's display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->full_name;
    }

    /**
     * Get the tag's color with fallback.
     */
    public function getDisplayColorAttribute(): string
    {
        return $this->color ?? '#6B7280';
    }

    /**
     * Check if the tag has events.
     */
    public function hasEvents(): bool
    {
        return $this->events()->exists();
    }

    /**
     * Get the count of events with this tag.
     */
    public function getEventsCountAttribute(): int
    {
        return $this->events()->count();
    }

    /**
     * Get the count of published events with this tag.
     */
    public function getPublishedEventsCountAttribute(): int
    {
        return $this->events()->published()->count();
    }
}
