<?php

namespace App\Services;

use App\Enums\SettingGroup;
use App\Enums\SettingType;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class SettingsService
{
    /**
     * Get all settings grouped by category
     */
    public function getAllSettingsGrouped(): array
    {
        $settings = [];
        
        foreach (SettingGroup::cases() as $group) {
            $settings[$group->value] = Setting::getGroup($group);
        }
        
        return $settings;
    }

    /**
     * Get settings for a specific group
     */
    public function getSettingsForGroup(SettingGroup $group): \Illuminate\Database\Eloquent\Collection
    {
        return Setting::getGroup($group);
    }

    /**
     * Update a single setting
     */
    public function updateSetting(string $key, $value): bool
    {
        $setting = Setting::where('key', $key)->first();
        
        if (!$setting) {
            throw new \InvalidArgumentException("Setting '{$key}' not found");
        }

        // Validate the value
        $this->validateSettingValue($setting, $value);

        // Handle file uploads
        if ($setting->type === SettingType::FILE && $value instanceof \Illuminate\Http\UploadedFile) {
            $value = $this->handleFileUpload($setting, $value);
        }

        // Update the setting
        $setting->value = $value;
        $result = $setting->save();

        if ($result) {
            $this->clearSettingCache($key, $setting->group);
            $this->logSettingChange($setting, $value);
        }

        return $result;
    }

    /**
     * Update multiple settings at once
     */
    public function updateMultipleSettings(array $settings): array
    {
        $results = [];
        $errors = [];

        foreach ($settings as $key => $value) {
            try {
                $results[$key] = $this->updateSetting($key, $value);
            } catch (\Exception $e) {
                $errors[$key] = $e->getMessage();
                $results[$key] = false;
            }
        }

        if (!empty($errors)) {
            throw new ValidationException(Validator::make([], []), null, $errors);
        }

        return $results;
    }

    /**
     * Create a new setting
     */
    public function createSetting(array $data): Setting
    {
        // Validate the data
        $validator = Validator::make($data, [
            'key' => 'required|string|unique:settings,key|max:255',
            'type' => 'required|string|in:' . implode(',', array_column(SettingType::cases(), 'value')),
            'group' => 'required|string|in:' . implode(',', array_column(SettingGroup::cases(), 'value')),
            'label' => 'required|string|max:255',
            'description' => 'nullable|string',
            'options' => 'nullable|array',
            'is_encrypted' => 'boolean',
            'is_required' => 'boolean',
            'validation_rules' => 'nullable|string',
            'default_value' => 'nullable|string',
            'sort_order' => 'integer',
            'is_public' => 'boolean',
        ]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $setting = Setting::create($data);
        
        // Clear cache
        $this->clearSettingCache($setting->key, $setting->group);
        
        return $setting;
    }

    /**
     * Delete a setting
     */
    public function deleteSetting(string $key): bool
    {
        $setting = Setting::where('key', $key)->first();
        
        if (!$setting) {
            return false;
        }

        // Handle file deletion if it's a file setting
        if ($setting->type === SettingType::FILE && $setting->value) {
            $this->deleteSettingFile($setting);
        }

        $result = $setting->delete();

        if ($result) {
            $this->clearSettingCache($key, $setting->group);
            $this->logSettingChange($setting, null, 'deleted');
        }

        return $result;
    }

    /**
     * Reset a setting to its default value
     */
    public function resetSetting(string $key): bool
    {
        $setting = Setting::where('key', $key)->first();
        
        if (!$setting || !$setting->default_value) {
            return false;
        }

        $setting->value = $setting->default_value;
        $result = $setting->save();

        if ($result) {
            $this->clearSettingCache($key, $setting->group);
            $this->logSettingChange($setting, $setting->default_value, 'reset');
        }

        return $result;
    }

    /**
     * Reset all settings to defaults
     */
    public function resetAllSettings(): array
    {
        $results = [];
        $settings = Setting::whereNotNull('default_value')->get();

        foreach ($settings as $setting) {
            $results[$setting->key] = $this->resetSetting($setting->key);
        }

        return $results;
    }

    /**
     * Export settings to JSON
     */
    public function exportSettings(): string
    {
        $settings = Setting::all()->map(function ($setting) {
            return [
                'key' => $setting->key,
                'value' => $setting->display_value,
                'type' => $setting->type->value,
                'group' => $setting->group->value,
                'label' => $setting->label,
                'description' => $setting->description,
                'options' => $setting->options,
                'is_encrypted' => $setting->is_encrypted,
                'is_required' => $setting->is_required,
                'validation_rules' => $setting->validation_rules,
                'default_value' => $setting->default_value,
                'sort_order' => $setting->sort_order,
                'is_public' => $setting->is_public,
            ];
        });

        return json_encode($settings, JSON_PRETTY_PRINT);
    }

    /**
     * Import settings from JSON
     */
    public function importSettings(string $jsonData): array
    {
        $data = json_decode($jsonData, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON data');
        }

        $results = [];
        $errors = [];

        foreach ($data as $settingData) {
            try {
                if (isset($settingData['key'])) {
                    $existing = Setting::where('key', $settingData['key'])->first();
                    
                    if ($existing) {
                        $existing->update($settingData);
                        $results[$settingData['key']] = 'updated';
                    } else {
                        Setting::create($settingData);
                        $results[$settingData['key']] = 'created';
                    }
                }
            } catch (\Exception $e) {
                $errors[$settingData['key'] ?? 'unknown'] = $e->getMessage();
            }
        }

        // Clear all caches after import
        Setting::clearCache();

        if (!empty($errors)) {
            Log::warning('Settings import completed with errors', $errors);
        }

        return [
            'results' => $results,
            'errors' => $errors,
        ];
    }

    /**
     * Test SMTP connection
     */
    public function testSmtpConnection(array $smtpSettings): array
    {
        try {
            // Temporarily update mail config
            $this->updateMailConfig($smtpSettings);

            // Send test email
            $testEmail = $smtpSettings['test_email'] ?? config('mail.from.address');
            
            Mail::raw('This is a test email from AirforceBES Events to verify SMTP configuration.', function ($message) use ($testEmail) {
                $message->to($testEmail)
                        ->subject('SMTP Test - AirforceBES Events');
            });

            return [
                'success' => true,
                'message' => 'SMTP connection successful. Test email sent to ' . $testEmail,
            ];

        } catch (\Exception $e) {
            Log::error('SMTP test failed', [
                'error' => $e->getMessage(),
                'smtp_settings' => array_except($smtpSettings, ['password']),
            ]);

            return [
                'success' => false,
                'message' => 'SMTP connection failed: ' . $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get SMTP provider templates
     */
    public function getSmtpProviderTemplates(): array
    {
        return [
            'gmail' => [
                'name' => 'Gmail / Google Workspace',
                'host' => 'smtp.gmail.com',
                'port' => 587,
                'encryption' => 'tls',
                'driver' => 'smtp',
                'logo' => 'gmail-logo.png',
            ],
            'outlook' => [
                'name' => 'Outlook / Office 365',
                'host' => 'smtp.office365.com',
                'port' => 587,
                'encryption' => 'tls',
                'driver' => 'smtp',
                'logo' => 'outlook-logo.png',
            ],
            'sendgrid' => [
                'name' => 'SendGrid',
                'host' => 'smtp.sendgrid.net',
                'port' => 587,
                'encryption' => 'tls',
                'driver' => 'smtp',
                'logo' => 'sendgrid-logo.png',
            ],
            'mailgun' => [
                'name' => 'Mailgun',
                'host' => 'smtp.mailgun.org',
                'port' => 587,
                'encryption' => 'tls',
                'driver' => 'smtp',
                'logo' => 'mailgun-logo.png',
            ],
            'ses' => [
                'name' => 'Amazon SES',
                'host' => 'email-smtp.us-east-1.amazonaws.com',
                'port' => 587,
                'encryption' => 'tls',
                'driver' => 'ses',
                'logo' => 'ses-logo.png',
            ],
            'postmark' => [
                'name' => 'Postmark',
                'host' => 'smtp.postmarkapp.com',
                'port' => 587,
                'encryption' => 'tls',
                'driver' => 'smtp',
                'logo' => 'postmark-logo.png',
            ],
            'custom' => [
                'name' => 'Custom SMTP',
                'host' => '',
                'port' => 587,
                'encryption' => 'tls',
                'driver' => 'smtp',
                'logo' => 'custom-logo.png',
            ],
        ];
    }

    /**
     * Validate setting value
     */
    protected function validateSettingValue(Setting $setting, $value): void
    {
        $rules = $setting->getValidationRules();
        
        if ($setting->is_required) {
            $rules[] = 'required';
        }

        $validator = Validator::make(['value' => $value], ['value' => $rules]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * Handle file upload for file settings
     */
    protected function handleFileUpload(Setting $setting, \Illuminate\Http\UploadedFile $file): string
    {
        // Delete old file if exists
        if ($setting->value) {
            $this->deleteSettingFile($setting);
        }

        // Store new file
        $path = $file->store('settings', 'public');
        
        return $path;
    }

    /**
     * Delete file for file setting
     */
    protected function deleteSettingFile(Setting $setting): void
    {
        if ($setting->value && Storage::disk('public')->exists($setting->value)) {
            Storage::disk('public')->delete($setting->value);
        }
    }

    /**
     * Update mail configuration temporarily
     */
    protected function updateMailConfig(array $smtpSettings): void
    {
        config([
            'mail.default' => $smtpSettings['driver'] ?? 'smtp',
            'mail.mailers.smtp.host' => $smtpSettings['host'] ?? '',
            'mail.mailers.smtp.port' => $smtpSettings['port'] ?? 587,
            'mail.mailers.smtp.username' => $smtpSettings['username'] ?? '',
            'mail.mailers.smtp.password' => $smtpSettings['password'] ?? '',
            'mail.mailers.smtp.encryption' => $smtpSettings['encryption'] ?? 'tls',
            'mail.from.address' => $smtpSettings['from_email'] ?? config('mail.from.address'),
            'mail.from.name' => $smtpSettings['from_name'] ?? config('mail.from.name'),
        ]);
    }

    /**
     * Clear setting cache
     */
    protected function clearSettingCache(string $key, SettingGroup $group): void
    {
        Cache::forget("setting.{$key}");
        Cache::forget("settings.group.{$group->value}");
        Cache::forget('settings.all');
    }

    /**
     * Log setting changes
     */
    protected function logSettingChange(Setting $setting, $value, string $action = 'updated'): void
    {
        Log::info("Setting {$action}", [
            'key' => $setting->key,
            'group' => $setting->group->value,
            'type' => $setting->type->value,
            'action' => $action,
            'user_id' => auth()->id(),
            'value_length' => is_string($value) ? strlen($value) : null,
            'is_encrypted' => $setting->is_encrypted,
        ]);
    }

    /**
     * Get settings cache statistics
     */
    public function getCacheStats(): array
    {
        $stats = [];
        
        foreach (SettingGroup::cases() as $group) {
            $cacheKey = "settings.group.{$group->value}";
            $stats[$group->value] = Cache::has($cacheKey);
        }
        
        $stats['all'] = Cache::has('settings.all');
        
        return $stats;
    }

    /**
     * Clear all settings cache
     */
    public function clearAllCache(): void
    {
        Setting::clearCache();
    }

    /**
     * Backup current settings
     */
    public function backupSettings(): string
    {
        $backup = [
            'timestamp' => now()->toISOString(),
            'user_id' => auth()->id(),
            'settings' => Setting::all()->toArray(),
        ];

        $filename = 'settings_backup_' . now()->format('Y_m_d_H_i_s') . '.json';
        Storage::disk('local')->put('backups/' . $filename, json_encode($backup, JSON_PRETTY_PRINT));

        return $filename;
    }

    /**
     * Restore settings from backup
     */
    public function restoreSettings(string $filename): array
    {
        $backupPath = 'backups/' . $filename;
        
        if (!Storage::disk('local')->exists($backupPath)) {
            throw new \InvalidArgumentException('Backup file not found');
        }

        $backupData = json_decode(Storage::disk('local')->get($backupPath), true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid backup file format');
        }

        // Create current backup before restore
        $currentBackup = $this->backupSettings();

        $results = [];
        $errors = [];

        foreach ($backupData['settings'] as $settingData) {
            try {
                $setting = Setting::where('key', $settingData['key'])->first();
                
                if ($setting) {
                    $setting->update($settingData);
                    $results[$settingData['key']] = 'restored';
                }
            } catch (\Exception $e) {
                $errors[$settingData['key']] = $e->getMessage();
            }
        }

        // Clear cache after restore
        Setting::clearCache();

        return [
            'results' => $results,
            'errors' => $errors,
            'current_backup' => $currentBackup,
        ];
    }
}
