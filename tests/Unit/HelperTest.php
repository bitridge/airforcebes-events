<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Setting;
use App\Enums\SettingGroup;
use App\Enums\SettingType;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HelperTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create test settings
        Setting::create([
            'key' => 'app.name',
            'value' => 'Test App Name',
            'type' => SettingType::TEXT,
            'group' => SettingGroup::GENERAL,
            'label' => 'Application Name',
            'is_public' => true,
        ]);

        Setting::create([
            'key' => 'app.description',
            'value' => 'Test App Description',
            'type' => SettingType::TEXT,
            'group' => SettingGroup::GENERAL,
            'label' => 'Application Description',
            'is_public' => true,
        ]);

        Setting::create([
            'key' => 'app.logo',
            'value' => 'test-logo.png',
            'type' => SettingType::FILE,
            'group' => SettingGroup::GENERAL,
            'label' => 'Application Logo',
            'is_public' => true,
        ]);

        Setting::create([
            'key' => 'appearance.primary_color',
            'value' => '#ff0000',
            'type' => SettingType::COLOR,
            'group' => SettingGroup::APPEARANCE,
            'label' => 'Primary Color',
            'is_public' => true,
        ]);

        Setting::create([
            'key' => 'appearance.secondary_color',
            'value' => '#00ff00',
            'type' => SettingType::COLOR,
            'group' => SettingGroup::APPEARANCE,
            'label' => 'Secondary Color',
            'is_public' => true,
        ]);
    }

    /** @test */
    public function app_setting_helper_returns_correct_value()
    {
        $value = app_setting('app.name');
        $this->assertEquals('Test App Name', $value);
    }

    /** @test */
    public function app_setting_helper_returns_default_when_setting_not_found()
    {
        $value = app_setting('app.nonexistent', 'Default Value');
        $this->assertEquals('Default Value', $value);
    }

    /** @test */
    public function app_setting_helper_returns_null_when_no_default()
    {
        $value = app_setting('app.nonexistent');
        $this->assertNull($value);
    }

    /** @test */
    public function app_name_helper_returns_correct_value()
    {
        $name = app_name();
        $this->assertEquals('Test App Name', $name);
    }

    /** @test */
    public function app_name_helper_returns_config_fallback()
    {
        // Delete the setting to test fallback
        Setting::where('key', 'app.name')->delete();
        
        $name = app_name();
        $this->assertEquals(config('app.name'), $name);
    }

    /** @test */
    public function app_logo_helper_returns_correct_value()
    {
        $logo = app_logo();
        $this->assertEquals('test-logo.png', $logo);
    }

    /** @test */
    public function app_logo_helper_returns_null_when_not_set()
    {
        // Delete the setting to test null return
        Setting::where('key', 'app.logo')->delete();
        
        $logo = app_logo();
        $this->assertNull($logo);
    }

    /** @test */
    public function app_description_helper_returns_correct_value()
    {
        $description = app_description();
        $this->assertEquals('Test App Description', $description);
    }

    /** @test */
    public function app_description_helper_returns_default_fallback()
    {
        // Delete the setting to test fallback
        Setting::where('key', 'app.description')->delete();
        
        $description = app_description();
        $this->assertEquals('Professional event management system', $description);
    }

    /** @test */
    public function primary_color_helper_returns_correct_value()
    {
        $color = primary_color();
        $this->assertEquals('#ff0000', $color);
    }

    /** @test */
    public function primary_color_helper_returns_default_fallback()
    {
        // Delete the setting to test fallback
        Setting::where('key', 'appearance.primary_color')->delete();
        
        $color = primary_color();
        $this->assertEquals('#dc2626', $color);
    }

    /** @test */
    public function secondary_color_helper_returns_correct_value()
    {
        $color = secondary_color();
        $this->assertEquals('#00ff00', $color);
    }

    /** @test */
    public function secondary_color_helper_returns_default_fallback()
    {
        // Delete the setting to test fallback
        Setting::where('key', 'appearance.secondary_color')->delete();
        
        $color = secondary_color();
        $this->assertEquals('#1e293b', $color);
    }

    /** @test */
    public function helper_functions_are_case_sensitive()
    {
        // Test that keys are case-sensitive
        $value = app_setting('APP.NAME');
        $this->assertNull($value);
        
        $value = app_setting('app.name');
        $this->assertEquals('Test App Name', $value);
    }

    /** @test */
    public function helper_functions_handle_encrypted_settings()
    {
        // Create an encrypted setting
        $setting = new Setting([
            'key' => 'app.secret',
            'type' => SettingType::PASSWORD,
            'group' => SettingGroup::SECURITY,
            'label' => 'Secret Setting',
            'is_encrypted' => true,
            'is_public' => true,
        ]);
        $setting->value = 'secret_value'; // This will trigger encryption
        $setting->save();

        $value = app_setting('app.secret');
        $this->assertEquals('secret_value', $value);
    }

    /** @test */
    public function helper_functions_handle_boolean_settings()
    {
        // Create a boolean setting
        Setting::create([
            'key' => 'app.enabled',
            'value' => '1',
            'type' => SettingType::BOOLEAN,
            'group' => SettingGroup::GENERAL,
            'label' => 'App Enabled',
            'is_public' => true,
        ]);

        $value = app_setting('app.enabled');
        $this->assertTrue($value);
    }

    /** @test */
    public function helper_functions_handle_json_settings()
    {
        // Create a JSON setting
        $jsonData = ['key1' => 'value1', 'key2' => 'value2'];
        Setting::create([
            'key' => 'app.config',
            'value' => json_encode($jsonData),
            'type' => SettingType::JSON,
            'group' => SettingGroup::GENERAL,
            'label' => 'App Config',
            'is_public' => true,
        ]);

        $value = app_setting('app.config');
        $this->assertIsArray($value);
        $this->assertEquals($jsonData, $value);
    }

    /** @test */
    public function helper_functions_handle_select_settings()
    {
        // Create a select setting
        Setting::create([
            'key' => 'app.theme',
            'value' => 'dark',
            'type' => SettingType::SELECT,
            'group' => SettingGroup::APPEARANCE,
            'label' => 'App Theme',
            'options' => ['light' => 'Light', 'dark' => 'Dark'],
            'is_public' => true,
        ]);

        $value = app_setting('app.theme');
        $this->assertEquals('dark', $value);
    }

    /** @test */
    public function helper_functions_handle_integer_settings()
    {
        // Create an integer setting
        Setting::create([
            'key' => 'app.max_users',
            'value' => '100',
            'type' => SettingType::INTEGER,
            'group' => SettingGroup::SYSTEM,
            'label' => 'Max Users',
            'is_public' => true,
        ]);

        $value = app_setting('app.max_users');
        $this->assertEquals(100, $value);
        $this->assertIsInt($value);
    }

    /** @test */
    public function helper_functions_handle_float_settings()
    {
        // Create a float setting
        Setting::create([
            'key' => 'app.version',
            'value' => '1.5',
            'type' => SettingType::FLOAT,
            'group' => SettingGroup::SYSTEM,
            'label' => 'App Version',
            'is_public' => true,
        ]);

        $value = app_setting('app.version');
        $this->assertEquals(1.5, $value);
        $this->assertIsFloat($value);
    }

    /** @test */
    public function helper_functions_handle_url_settings()
    {
        // Create a URL setting
        Setting::create([
            'key' => 'app.website',
            'value' => 'https://example.com',
            'type' => SettingType::URL,
            'group' => SettingGroup::GENERAL,
            'label' => 'Website URL',
            'is_public' => true,
        ]);

        $value = app_setting('app.website');
        $this->assertEquals('https://example.com', $value);
    }

    /** @test */
    public function helper_functions_handle_color_settings()
    {
        // Create a color setting
        Setting::create([
            'key' => 'app.accent_color',
            'value' => '#ffff00',
            'type' => SettingType::COLOR,
            'group' => SettingGroup::APPEARANCE,
            'label' => 'Accent Color',
            'is_public' => true,
        ]);

        $value = app_setting('app.accent_color');
        $this->assertEquals('#ffff00', $value);
    }

    /** @test */
    public function helper_functions_handle_file_settings()
    {
        // Create a file setting
        Setting::create([
            'key' => 'app.favicon',
            'value' => 'favicon.ico',
            'type' => SettingType::FILE,
            'group' => SettingGroup::GENERAL,
            'label' => 'Favicon',
            'is_public' => true,
        ]);

        $value = app_setting('app.favicon');
        $this->assertEquals('favicon.ico', $value);
    }

    /** @test */
    public function helper_functions_handle_public_only_settings()
    {
        // Create a non-public setting
        Setting::create([
            'key' => 'app.internal',
            'value' => 'internal_value',
            'type' => SettingType::TEXT,
            'group' => SettingGroup::SYSTEM,
            'label' => 'Internal Setting',
            'is_public' => false,
        ]);

        // Non-public settings should not be accessible via helpers
        $value = app_setting('app.internal');
        $this->assertNull($value);
    }

    /** @test */
    public function helper_functions_handle_missing_settings_gracefully()
    {
        // Test with completely non-existent keys
        $value = app_setting('completely.nonexistent.key');
        $this->assertNull($value);

        $value = app_setting('completely.nonexistent.key', 'fallback');
        $this->assertEquals('fallback', $value);
    }
}
