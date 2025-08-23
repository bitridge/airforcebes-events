<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'title',
        'description',
        'file_path',
        'file_name',
        'mime_type',
        'file_size',
        'alt_text',
        'is_featured',
        'sort_order',
        'uploaded_by',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'file_size' => 'integer',
    ];

    /**
     * Get the event for this photo.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user who uploaded this photo.
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Scope a query to only include featured photos.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope a query to order by sort order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at');
    }

    /**
     * Scope a query to order by creation date (newest first).
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Check if the photo is featured.
     */
    public function isFeatured(): bool
    {
        return $this->is_featured;
    }

    /**
     * Get the photo's display title.
     */
    public function getDisplayTitleAttribute(): string
    {
        return $this->title ?? $this->file_name;
    }

    /**
     * Get the photo's display description.
     */
    public function getDisplayDescriptionAttribute(): string
    {
        return $this->description ?? $this->alt_text ?? '';
    }

    /**
     * Get the photo's alt text with fallback.
     */
    public function getDisplayAltTextAttribute(): string
    {
        return $this->alt_text ?? $this->title ?? $this->file_name;
    }

    /**
     * Get the photo's file size in human-readable format.
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get the photo's URL.
     */
    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->file_path);
    }

    /**
     * Get the photo's thumbnail URL.
     */
    public function getThumbnailUrlAttribute(): string
    {
        $path = str_replace('.', '_thumb.', $this->file_path);
        return asset('storage/' . $path);
    }

    /**
     * Check if the photo is an image.
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if the photo is a video.
     */
    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    /**
     * Get the photo's dimensions (for images).
     */
    public function getDimensionsAttribute(): ?array
    {
        if (!$this->isImage()) {
            return null;
        }

        $path = storage_path('app/public/' . $this->file_path);
        if (!file_exists($path)) {
            return null;
        }

        $imageInfo = getimagesize($path);
        if ($imageInfo === false) {
            return null;
        }

        return [
            'width' => $imageInfo[0],
            'height' => $imageInfo[1],
        ];
    }

    /**
     * Mark the photo as featured.
     */
    public function markAsFeatured(): bool
    {
        return $this->update(['is_featured' => true]);
    }

    /**
     * Mark the photo as not featured.
     */
    public function markAsNotFeatured(): bool
    {
        return $this->update(['is_featured' => false]);
    }

    /**
     * Update the photo's sort order.
     */
    public function updateSortOrder(int $order): bool
    {
        return $this->update(['sort_order' => $order]);
    }

    /**
     * Get the next photo in the event.
     */
    public function getNextPhotoAttribute()
    {
        return static::where('event_id', $this->event_id)
            ->where('sort_order', '>', $this->sort_order)
            ->ordered()
            ->first();
    }

    /**
     * Get the previous photo in the event.
     */
    public function getPreviousPhotoAttribute()
    {
        return static::where('event_id', $this->event_id)
            ->where('sort_order', '<', $this->sort_order)
            ->ordered()
            ->orderBy('sort_order', 'desc')
            ->first();
    }
}
