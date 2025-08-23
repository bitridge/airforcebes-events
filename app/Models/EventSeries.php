<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EventSeries extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'slug',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($series) {
            if (empty($series->slug)) {
                $series->slug = Str::slug($series->name);
            }
        });

        static::updating(function ($series) {
            if ($series->isDirty('name') && empty($series->slug)) {
                $series->slug = Str::slug($series->name);
            }
        });
    }

    /**
     * Get the events in this series.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'series_id')->orderBy('series_order');
    }

    /**
     * Get the published events in this series.
     */
    public function publishedEvents(): HasMany
    {
        return $this->events()->published();
    }

    /**
     * Get the upcoming events in this series.
     */
    public function upcomingEvents(): HasMany
    {
        return $this->events()->upcoming();
    }

    /**
     * Get the past events in this series.
     */
    public function pastEvents(): HasMany
    {
        return $this->events()->past();
    }

    /**
     * Scope a query to only include active series.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Get the series display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Check if the series has events.
     */
    public function hasEvents(): bool
    {
        return $this->events()->exists();
    }

    /**
     * Get the count of events in this series.
     */
    public function getEventsCountAttribute(): int
    {
        return $this->events()->count();
    }

    /**
     * Get the count of published events in this series.
     */
    public function getPublishedEventsCountAttribute(): int
    {
        return $this->publishedEvents()->count();
    }

    /**
     * Get the count of upcoming events in this series.
     */
    public function getUpcomingEventsCountAttribute(): int
    {
        return $this->upcomingEvents()->count();
    }

    /**
     * Get the next event in the series.
     */
    public function getNextEventAttribute()
    {
        return $this->upcomingEvents()->orderBy('start_date')->first();
    }

    /**
     * Get the last event in the series.
     */
    public function getLastEventAttribute()
    {
        return $this->events()->orderBy('start_date', 'desc')->first();
    }
}
