<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'template_data',
        'is_public',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'template_data' => 'array',
        'is_public' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who created this template.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include public templates.
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope a query to only include private templates.
     */
    public function scopePrivate($query)
    {
        return $query->where('is_public', false);
    }

    /**
     * Scope a query to filter by creator.
     */
    public function scopeByCreator($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope a query to order by name.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }

    /**
     * Scope a query to order by creation date (newest first).
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * Check if the template is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if the template is public.
     */
    public function isPublic(): bool
    {
        return $this->is_public;
    }

    /**
     * Check if the template is private.
     */
    public function isPrivate(): bool
    {
        return !$this->is_public;
    }

    /**
     * Get the template's display name.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->name;
    }

    /**
     * Get the template's display description.
     */
    public function getDisplayDescriptionAttribute(): string
    {
        return $this->description ?? 'No description available';
    }

    /**
     * Get the template data as an array.
     */
    public function getTemplateDataArrayAttribute(): array
    {
        if (empty($this->template_data)) {
            return [];
        }

        if (is_array($this->template_data)) {
            return $this->template_data;
        }

        if (is_string($this->template_data)) {
            return json_decode($this->template_data, true) ?? [];
        }

        return [];
    }

    /**
     * Get a specific value from template data.
     */
    public function getTemplateDataValue(string $key, $default = null)
    {
        $data = $this->template_data_array;
        return $data[$key] ?? $default;
    }

    /**
     * Check if template data has a specific key.
     */
    public function hasTemplateDataKey(string $key): bool
    {
        $data = $this->template_data_array;
        return array_key_exists($key, $data);
    }

    /**
     * Get the template's event title.
     */
    public function getEventTitleAttribute(): string
    {
        return $this->getTemplateDataValue('title', 'Untitled Event');
    }

    /**
     * Get the template's event description.
     */
    public function getEventDescriptionAttribute(): string
    {
        return $this->getTemplateDataValue('description', '');
    }

    /**
     * Get the template's event venue.
     */
    public function getEventVenueAttribute(): string
    {
        return $this->getTemplateDataValue('venue', '');
    }

    /**
     * Get the template's event category ID.
     */
    public function getEventCategoryIdAttribute(): ?int
    {
        return $this->getTemplateDataValue('category_id');
    }

    /**
     * Get the template's event tags.
     */
    public function getEventTagsAttribute(): array
    {
        return $this->getTemplateDataValue('tags', []);
    }

    /**
     * Get the template's event settings.
     */
    public function getEventSettingsAttribute(): array
    {
        return $this->getTemplateDataValue('settings', []);
    }

    /**
     * Get the template's custom registration fields.
     */
    public function getCustomFieldsAttribute(): array
    {
        return $this->getTemplateDataValue('custom_fields', []);
    }

    /**
     * Check if the template has custom fields.
     */
    public function hasCustomFields(): bool
    {
        return !empty($this->custom_fields);
    }

    /**
     * Check if the template has waitlist settings.
     */
    public function hasWaitlistSettings(): bool
    {
        $settings = $this->event_settings;
        return isset($settings['has_waitlist']) && $settings['has_waitlist'];
    }

    /**
     * Check if the template has early bird settings.
     */
    public function hasEarlyBirdSettings(): bool
    {
        $settings = $this->event_settings;
        return isset($settings['early_bird_enabled']) && $settings['early_bird_enabled'];
    }

    /**
     * Check if the template has pricing settings.
     */
    public function hasPricingSettings(): bool
    {
        $settings = $this->event_settings;
        return isset($settings['has_pricing']) && $settings['has_pricing'];
    }

    /**
     * Activate the template.
     */
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the template.
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Make the template public.
     */
    public function makePublic(): bool
    {
        return $this->update(['is_public' => true]);
    }

    /**
     * Make the template private.
     */
    public function makePrivate(): bool
    {
        return $this->update(['is_public' => false]);
    }

    /**
     * Create an event from this template.
     */
    public function createEventFromTemplate(array $overrides = []): Event
    {
        $templateData = $this->template_data_array;
        $eventData = array_merge($templateData, $overrides);

        // Remove template-specific fields
        unset($eventData['template_id']);

        return Event::create($eventData);
    }

    /**
     * Duplicate the template.
     */
    public function duplicate(string $newName = null): EventTemplate
    {
        $newName = $newName ?? $this->name . ' (Copy)';

        return static::create([
            'name' => $newName,
            'description' => $this->description,
            'template_data' => $this->template_data,
            'is_public' => false, // Duplicates are always private
            'created_by' => auth()->id(),
            'is_active' => true,
        ]);
    }

    /**
     * Get the template's usage count.
     */
    public function getUsageCountAttribute(): int
    {
        // This would track how many times the template has been used
        // Implementation depends on your tracking requirements
        return 0;
    }

    /**
     * Check if the template can be used by the current user.
     */
    public function canBeUsedBy(User $user): bool
    {
        if ($this->is_public) {
            return true;
        }

        return $this->created_by === $user->id;
    }

    /**
     * Check if the template can be edited by the current user.
     */
    public function canBeEditedBy(User $user): bool
    {
        return $this->created_by === $user->id;
    }

    /**
     * Check if the template can be deleted by the current user.
     */
    public function canBeDeletedBy(User $user): bool
    {
        return $this->created_by === $user->id;
    }
}
