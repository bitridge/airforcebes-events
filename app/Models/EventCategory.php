<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class EventCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'icon',
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

        static::creating(function ($category) {
            if (empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });

        static::updating(function ($category) {
            if ($category->isDirty('name') && empty($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    /**
     * Get the events for this category.
     */
    public function events(): HasMany
    {
        return $this->hasMany(Event::class, 'category_id');
    }

    /**
     * Scope a query to only include active categories.
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
     * Get the category's display name with color.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Get the category's color with fallback.
     */
    public function getDisplayColorAttribute(): string
    {
        return $this->color ?? '#3B82F6';
    }

    /**
     * Get the category's icon with fallback.
     */
    public function getDisplayIconAttribute(): string
    {
        return $this->icon ?? 'calendar';
    }

    /**
     * Check if the category has events.
     */
    public function hasEvents(): bool
    {
        return $this->events()->exists();
    }

    /**
     * Get the count of events in this category.
     */
    public function getEventsCountAttribute(): int
    {
        return $this->events()->count();
    }

    /**
     * Get the count of published events in this category.
     */
    public function getPublishedEventsCountAttribute(): int
    {
        return $this->events()->published()->count();
    }
}
