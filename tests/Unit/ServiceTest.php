<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\SettingsService;
use App\Models\Setting;
use App\Enums\SettingGroup;
use App\Enums\SettingType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class ServiceTest extends TestCase
{
    use RefreshDatabase;

    protected SettingsService $settingsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->settingsService = new SettingsService();
        Storage::fake('public');
    }

    /** @test */
    public function settings_service_can_get_all_settings_grouped()
    {
        // Create test settings
        Setting::create([
            'key' => 'test.general',
            'value' => 'general_value',
            'type' => SettingType::TEXT,
            'group' => SettingGroup::GENERAL,
            'label' => 'Test General Setting',
        ]);

        Setting::create([
            'key' => 'test.smtp',
            'value' => 'smtp_value',
            'type' => SettingType::TEXT,
            'group' => SettingGroup::SMTP,
            'label' => 'Test SMTP Setting',
        ]);

        $groupedSettings = $this->settingsService->getAllSettingsGrouped();

        $this->assertArrayHasKey('general', $groupedSettings);
        $this->assertArrayHasKey('smtp', $groupedSettings);
        $this->assertCount(1, $groupedSettings['general']);
        $this->assertCount(1, $groupedSettings['smtp']);
    }

    /** @test */
    public function settings_service_can_get_settings_for_specific_group()
    {
        Setting::create([
            'key' => 'test.general',
            'value' => 'general_value',
            'type' => SettingType::TEXT,
            'group' => SettingGroup::GENERAL,
            'label' => 'Test General Setting',
        ]);

        $generalSettings = $this->settingsService->getSettingsForGroup(SettingGroup::GENERAL);

        $this->assertCount(1, $generalSettings);
        $this->assertEquals('test.general', $generalSettings->first()->key);
    }

    /** @test */
    public function settings_service_can_update_single_setting()
    {
        $setting = Setting::create([
            'key' => 'test.setting',
            'value' => 'old_value',
            'type' => SettingType::TEXT,
            'group' => SettingGroup::GENERAL,
            'label' => 'Test Setting',
        ]);

        $result = $this->settingsService->updateSetting('test.setting', 'new_value');

        $this->assertTrue($result);
        $this->assertDatabaseHas('settings', [
            'key' => 'test.setting',
            'value' => 'new_value',
        ]);
    }

    /** @test */
    public function settings_service_can_update_multiple_settings()
    {
        Setting::create([
            'key' => 'test.setting1',
            'value' => 'old_value1',
            'type' => SettingType::TEXT,
            'group' => SettingGroup::GENERAL,
            'label' => 'Test Setting 1',
        ]);

        Setting::create([
            'key' => 'test.setting2',
            'value' => 'old_value2',
            'type' => SettingType::TEXT,
            'group' => SettingGroup::GENERAL,
            'label' => 'Test Setting 2',
        ]);

        $settings = [
            'test.setting1' => 'new_value1',
            'test.setting2' => 'new_value2',
        ];

        $results = $this->settingsService->updateMultipleSettings($settings);

        $this->assertArrayHasKey('test.setting1', $results);
        $this->assertArrayHasKey('test.setting2', $results);
        $this->assertTrue($results['test.setting1']);
        $this->assertTrue($results['test.setting2']);

        $this->assertDatabaseHas('settings', [
            'key' => 'test.setting1',
            'value' => 'new_value1',
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'test.setting2',
            'value' => 'new_value2',
        ]);
    }

    /** @test */
    public function settings_service_validates_setting_values()
    {
        $setting = Setting::create([
            'key' => 'test.email',
            'value' => 'old@email.com',
            'type' => SettingType::EMAIL,
            'group' => SettingGroup::GENERAL,
            'label' => 'Test Email Setting',
            'validation_rules' => 'email',
        ]);

        // Valid email
        $result = $this->settingsService->updateSetting('test.email', 'new@email.com');
        $this->assertTrue($result);

        // Invalid email should fail
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->settingsService->updateSetting('test.email', 'invalid-email');
    }

    /** @test */
    public function settings_service_handles_file_uploads()
    {
        $setting = Setting::create([
            'key' => 'test.file',
            'value' => null,
            'type' => SettingType::FILE,
            'group' => SettingGroup::GENERAL,
            'label' => 'Test File Setting',
        ]);

        $file = UploadedFile::fake()->image('test.png', 100, 100);

        $result = $this->settingsService->updateSetting('test.file', $file);

        $this->assertTrue($result);
        
        $updatedSetting = Setting::where('key', 'test.file')->first();
        $this->assertNotNull($updatedSetting->value);
        Storage::disk('public')->assertExists($updatedSetting->value);
    }

    /** @test */
    public function settings_service_clears_cache_after_updates()
    {
        $setting = Setting::create([
            'key' => 'test.cache',
            'value' => 'cache_value',
            'type' => SettingType::TEXT,
            'group' => SettingGroup::GENERAL,
            'label' => 'Test Cache Setting',
        ]);

        // Cache the setting
        Cache::put("settings.key.test.cache", $setting, 3600);
        $this->assertTrue(Cache::has("settings.key.test.cache"));

        // Update the setting
        $this->settingsService->updateSetting('test.cache', 'new_cache_value');

        // Cache should be cleared
        $this->assertFalse(Cache::has("settings.key.test.cache"));
    }

    /** @test */
    public function settings_service_can_test_smtp_connection()
    {
        $smtpSettings = [
            'mail.smtp_host' => 'smtp.gmail.com',
            'mail.smtp_port' => '587',
            'mail.smtp_username' => 'test@example.com',
            'mail.smtp_password' => 'password123',
            'mail.smtp_encryption' => 'tls',
        ];

        // Mock the mail facade to avoid actual SMTP connection
        $this->mock(\Illuminate\Support\Facades\Mail::class);

        $result = $this->settingsService->testSmtpConnection($smtpSettings, 'test@example.com');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('success', $result);
    }

    /** @test */
    public function settings_service_can_backup_settings()
    {
        Setting::create([
            'key' => 'test.backup',
            'value' => 'backup_value',
            'type' => SettingType::TEXT,
            'group' => SettingGroup::GENERAL,
            'label' => 'Test Backup Setting',
        ]);

        $filename = $this->settingsService->backupSettings();

        $this->assertIsString($filename);
        $this->assertStringContainsString('settings_backup_', $filename);
        
        // Check if backup file exists
        $this->assertTrue(Storage::disk('local')->exists('backups/' . $filename));
    }

    /** @test */
    public function settings_service_can_restore_settings_from_backup()
    {
        // Create original setting
        Setting::create([
            'key' => 'test.restore',
            'value' => 'original_value',
            'type' => SettingType::TEXT,
            'group' => SettingGroup::GENERAL,
            'label' => 'Test Restore Setting',
        ]);

        // Create backup
        $filename = $this->settingsService->backupSettings();

        // Modify the setting
        Setting::where('key', 'test.restore')->update(['value' => 'modified_value']);

        // Restore from backup
        $results = $this->settingsService->restoreSettings($filename);

        $this->assertArrayHasKey('results', $results);
        $this->assertArrayHasKey('errors', $results);

        // Check if setting was restored
        $restoredSetting = Setting::where('key', 'test.restore')->first();
        $this->assertEquals('original_value', $restoredSetting->value);
    }

    /** @test */
    public function settings_service_handles_encrypted_settings()
    {
        $setting = Setting::create([
            'key' => 'test.encrypted',
            'value' => 'secret_value',
            'type' => SettingType::PASSWORD,
            'group' => SettingGroup::SECURITY,
            'label' => 'Test Encrypted Setting',
            'is_encrypted' => true,
        ]);

        // Value should be encrypted in database
        $this->assertNotEquals('secret_value', $setting->value);
        
        // Display value should be decrypted
        $this->assertEquals('secret_value', $setting->display_value);
    }

    /** @test */
    public function settings_service_can_get_setting_value()
    {
        Setting::create([
            'key' => 'test.value',
            'value' => 'test_value',
            'type' => SettingType::TEXT,
            'group' => SettingGroup::GENERAL,
            'label' => 'Test Value Setting',
        ]);

        $value = $this->settingsService->getSettingValue('test.value');
        $this->assertEquals('test_value', $value);

        // Test with default value
        $value = $this->settingsService->getSettingValue('test.nonexistent', 'default_value');
        $this->assertEquals('default_value', $value);
    }

    /** @test */
    public function settings_service_can_clear_all_cache()
    {
        // Create some cached settings
        Cache::put('settings.group.general', ['test' => 'value'], 3600);
        Cache::put('settings.all', ['test' => 'value'], 3600);

        $this->assertTrue(Cache::has('settings.group.general'));
        $this->assertTrue(Cache::has('settings.all'));

        $this->settingsService->clearAllCache();

        $this->assertFalse(Cache::has('settings.group.general'));
        $this->assertFalse(Cache::has('settings.all'));
    }

    /** @test */
    public function settings_service_can_get_cache_stats()
    {
        // Create some cached settings
        Cache::put('settings.group.general', ['test' => 'value'], 3600);
        Cache::put('settings.group.smtp', ['test' => 'value'], 3600);

        $stats = $this->settingsService->getCacheStats();

        $this->assertArrayHasKey('general', $stats);
        $this->assertArrayHasKey('smtp', $stats);
        $this->assertArrayHasKey('all', $stats);
    }

    /** @test */
    public function settings_service_handles_boolean_settings()
    {
        $setting = Setting::create([
            'key' => 'test.boolean',
            'value' => '1',
            'type' => SettingType::BOOLEAN,
            'group' => SettingGroup::GENERAL,
            'label' => 'Test Boolean Setting',
        ]);

        $this->assertTrue($setting->display_value);

        // Update to false
        $this->settingsService->updateSetting('test.boolean', '0');
        
        $updatedSetting = Setting::where('key', 'test.boolean')->first();
        $this->assertFalse($updatedSetting->display_value);
    }

    /** @test */
    public function settings_service_handles_select_settings()
    {
        $setting = Setting::create([
            'key' => 'test.select',
            'value' => 'option1',
            'type' => SettingType::SELECT,
            'group' => SettingGroup::GENERAL,
            'label' => 'Test Select Setting',
            'options' => ['option1' => 'Option 1', 'option2' => 'Option 2'],
        ]);

        $this->assertIsArray($setting->options);
        $this->assertArrayHasKey('option1', $setting->options);
        $this->assertArrayHasKey('option2', $setting->options);
    }

    /** @test */
    public function settings_service_handles_json_settings()
    {
        $jsonData = ['key1' => 'value1', 'key2' => 'value2'];
        
        $setting = Setting::create([
            'key' => 'test.json',
            'value' => json_encode($jsonData),
            'type' => SettingType::JSON,
            'group' => SettingGroup::GENERAL,
            'label' => 'Test JSON Setting',
        ]);

        $this->assertIsArray($setting->display_value);
        $this->assertEquals($jsonData, $setting->display_value);
    }
}
