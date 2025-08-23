<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomRegistrationField extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'label',
        'field_name',
        'field_type',
        'options',
        'validation_rules',
        'is_required',
        'help_text',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'options' => 'array',
        'validation_rules' => 'array',
        'is_required' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get the event for this custom field.
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Scope a query to only include active fields.
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
        return $query->orderBy('sort_order')->orderBy('label');
    }

    /**
     * Scope a query to filter by field type.
     */
    public function scopeByType($query, $type)
    {
        return $query->where('field_type', $type);
    }

    /**
     * Scope a query to filter by required fields.
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Scope a query to filter by optional fields.
     */
    public function scopeOptional($query)
    {
        return $query->where('is_required', false);
    }

    /**
     * Check if the field is active.
     */
    public function isActive(): bool
    {
        return $this->is_active;
    }

    /**
     * Check if the field is required.
     */
    public function isRequired(): bool
    {
        return $this->is_required;
    }

    /**
     * Check if the field is optional.
     */
    public function isOptional(): bool
    {
        return !$this->is_required;
    }

    /**
     * Get the field's display label.
     */
    public function getDisplayLabelAttribute(): string
    {
        return $this->label;
    }

    /**
     * Get the field's HTML input type.
     */
    public function getHtmlInputTypeAttribute(): string
    {
        return match($this->field_type) {
            'text' => 'text',
            'textarea' => 'textarea',
            'select' => 'select',
            'checkbox' => 'checkbox',
            'radio' => 'radio',
            'date' => 'date',
            'number' => 'number',
            'email' => 'email',
            'phone' => 'tel',
            'url' => 'url',
            default => 'text'
        };
    }

    /**
     * Get the field's validation rules as a string.
     */
    public function getValidationRulesStringAttribute(): string
    {
        if (empty($this->validation_rules)) {
            return $this->is_required ? 'required' : 'nullable';
        }

        $rules = $this->validation_rules;
        if ($this->is_required) {
            array_unshift($rules, 'required');
        } else {
            array_unshift($rules, 'nullable');
        }

        return implode('|', $rules);
    }

    /**
     * Get the field's options as an array.
     */
    public function getOptionsArrayAttribute(): array
    {
        if (empty($this->options)) {
            return [];
        }

        if (is_array($this->options)) {
            return $this->options;
        }

        if (is_string($this->options)) {
            return json_decode($this->options, true) ?? [];
        }

        return [];
    }

    /**
     * Check if the field has options.
     */
    public function hasOptions(): bool
    {
        return in_array($this->field_type, ['select', 'checkbox', 'radio']) && !empty($this->options_array);
    }

    /**
     * Get the field's help text with fallback.
     */
    public function getDisplayHelpTextAttribute(): string
    {
        return $this->help_text ?? '';
    }

    /**
     * Check if the field is a text input.
     */
    public function isTextInput(): bool
    {
        return in_array($this->field_type, ['text', 'email', 'phone', 'url']);
    }

    /**
     * Check if the field is a textarea.
     */
    public function isTextarea(): bool
    {
        return $this->field_type === 'textarea';
    }

    /**
     * Check if the field is a select.
     */
    public function isSelect(): bool
    {
        return $this->field_type === 'select';
    }

    /**
     * Check if the field is a checkbox.
     */
    public function isCheckbox(): bool
    {
        return $this->field_type === 'checkbox';
    }

    /**
     * Check if the field is a radio button.
     */
    public function isRadio(): bool
    {
        return $this->field_type === 'radio';
    }

    /**
     * Check if the field is a date input.
     */
    public function isDateInput(): bool
    {
        return $this->field_type === 'date';
    }

    /**
     * Check if the field is a number input.
     */
    public function isNumberInput(): bool
    {
        return $this->field_type === 'number';
    }

    /**
     * Get the field's placeholder text.
     */
    public function getPlaceholderAttribute(): string
    {
        return match($this->field_type) {
            'text' => 'Enter ' . strtolower($this->label),
            'email' => 'Enter email address',
            'phone' => 'Enter phone number',
            'url' => 'Enter website URL',
            'date' => 'Select date',
            'number' => 'Enter number',
            default => 'Enter ' . strtolower($this->label)
        };
    }

    /**
     * Activate the field.
     */
    public function activate(): bool
    {
        return $this->update(['is_active' => true]);
    }

    /**
     * Deactivate the field.
     */
    public function deactivate(): bool
    {
        return $this->update(['is_active' => false]);
    }

    /**
     * Update the field's sort order.
     */
    public function updateSortOrder(int $order): bool
    {
        return $this->update(['sort_order' => $order]);
    }

    /**
     * Get the next field in the event.
     */
    public function getNextFieldAttribute()
    {
        return static::where('event_id', $this->event_id)
            ->where('sort_order', '>', $this->sort_order)
            ->ordered()
            ->first();
    }

    /**
     * Get the previous field in the event.
     */
    public function getPreviousFieldAttribute()
    {
        return static::where('event_id', $this->event_id)
            ->where('sort_order', '<', $this->sort_order)
            ->ordered()
            ->orderBy('sort_order', 'desc')
            ->first();
    }
}
