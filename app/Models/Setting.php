<?php

namespace App\Models;

use App\Enums\SettingGroup;
use App\Enums\SettingType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
        'options',
        'is_encrypted',
        'is_required',
        'validation_rules',
        'default_value',
        'sort_order',
        'is_public',
    ];

    protected $casts = [
        'type' => SettingType::class,
        'group' => SettingGroup::class,
        'options' => 'array',
        'is_encrypted' => 'boolean',
        'is_required' => 'boolean',
        'is_public' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $hidden = [
        'value',
    ];

    protected $appends = [
        'display_value',
    ];

    /**
     * Get the decrypted/processed value for display
     */
    public function getDisplayValueAttribute()
    {
        $value = $this->value;

        if ($this->is_encrypted && $value) {
            try {
                $value = Crypt::decryptString($value);
            } catch (\Exception $e) {
                $value = null;
            }
        }

        return $this->castValue($value);
    }

    /**
     * Set the value with encryption if needed
     */
    public function setValueAttribute($value)
    {
        if ($this->is_encrypted && $value) {
            $value = Crypt::encryptString($value);
        }

        $this->attributes['value'] = $value;
    }

    /**
     * Cast the value based on type
     */
    protected function castValue($value)
    {
        if ($value === null) {
            return null;
        }

        return match($this->type) {
            SettingType::BOOLEAN => (bool) $value,
            SettingType::INTEGER => (int) $value,
            SettingType::FLOAT => (float) $value,
            SettingType::JSON => is_string($value) ? json_decode($value, true) : $value,
            SettingType::BOOLEAN => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            default => $value,
        };
    }

    /**
     * Get a setting value by key
     */
    public static function get(string $key, $default = null)
    {
        $cacheKey = "setting.{$key}";
        
        return Cache::remember($cacheKey, 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            
            if (!$setting) {
                return $default;
            }

            return $setting->display_value;
        });
    }

    /**
     * Set a setting value by key
     */
    public static function set(string $key, $value): bool
    {
        $setting = static::where('key', $key)->first();
        
        if (!$setting) {
            return false;
        }

        $setting->value = $value;
        $result = $setting->save();

        if ($result) {
            Cache::forget("setting.{$key}");
            Cache::forget("settings.group.{$setting->group->value}");
        }

        return $result;
    }

    /**
     * Get all settings for a specific group
     */
    public static function getGroup(SettingGroup $group): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = "settings.group.{$group->value}";
        
        return Cache::remember($cacheKey, 3600, function () use ($group) {
            return static::where('group', $group)
                ->orderBy('sort_order')
                ->get();
        });
    }

    /**
     * Get all settings as an associative array
     */
    public static function getAll(): array
    {
        $cacheKey = 'settings.all';
        
        return Cache::remember($cacheKey, 3600, function () {
            return static::all()->pluck('display_value', 'key')->toArray();
        });
    }

    /**
     * Clear all settings cache
     */
    public static function clearCache(): void
    {
        Cache::forget('settings.all');
        
        foreach (SettingGroup::cases() as $group) {
            Cache::forget("settings.group.{$group->value}");
        }
        
        // Clear individual setting caches
        static::all()->each(function ($setting) {
            Cache::forget("setting.{$setting->key}");
        });
    }

    /**
     * Check if a setting exists
     */
    public static function has(string $key): bool
    {
        return static::where('key', $key)->exists();
    }

    /**
     * Get a setting with fallback to default
     */
    public static function getWithDefault(string $key, $default = null)
    {
        $value = static::get($key);
        return $value !== null ? $value : $default;
    }

    /**
     * Bulk update settings
     */
    public static function bulkUpdate(array $settings): array
    {
        $results = [];
        
        foreach ($settings as $key => $value) {
            $results[$key] = static::set($key, $value);
        }

        return $results;
    }

    /**
     * Get settings for a group as key-value pairs
     */
    public static function getGroupAsArray(SettingGroup $group): array
    {
        return static::getGroup($group)
            ->pluck('display_value', 'key')
            ->toArray();
    }

    /**
     * Scope for public settings
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     * Scope for required settings
     */
    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    /**
     * Get validation rules for this setting
     */
    public function getValidationRules(): array
    {
        if ($this->validation_rules) {
            return explode('|', $this->validation_rules);
        }

        return $this->type->getValidationRules();
    }

    /**
     * Check if setting has options (for select fields)
     */
    public function hasOptions(): bool
    {
        return $this->type === SettingType::SELECT && !empty($this->options);
    }

    /**
     * Get options for select fields
     */
    public function getOptions(): array
    {
        return $this->options ?? [];
    }

    /**
     * Check if setting is encrypted
     */
    public function isEncrypted(): bool
    {
        return $this->is_encrypted || $this->type->needsEncryption();
    }

    /**
     * Get the raw encrypted value (for admin purposes)
     */
    public function getRawValue()
    {
        return $this->attributes['value'] ?? null;
    }

    /**
     * Check if setting value is empty
     */
    public function isEmpty(): bool
    {
        $value = $this->display_value;
        
        if (is_bool($value)) {
            return false;
        }
        
        return empty($value);
    }

    /**
     * Get setting value for forms
     */
    public function getFormValue()
    {
        if ($this->type === SettingType::BOOLEAN) {
            return $this->display_value ? '1' : '0';
        }

        return $this->display_value;
    }
}
