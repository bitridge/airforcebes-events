<?php

if (!function_exists('app_setting')) {
    /**
     * Get an application setting value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    function app_setting(string $key, $default = null)
    {
        try {
            return app('App\Services\SettingsService')->getSettingValue($key, $default);
        } catch (\Exception $e) {
            return $default;
        }
    }
}

if (!function_exists('app_name')) {
    /**
     * Get the application name from settings
     *
     * @return string
     */
    function app_name(): string
    {
        return app_setting('app.name', config('app.name', 'AirforceBES Events'));
    }
}

if (!function_exists('app_logo')) {
    /**
     * Get the application logo from settings
     *
     * @return string|null
     */
    function app_logo(): ?string
    {
        return app_setting('app.logo');
    }
}

if (!function_exists('app_description')) {
    /**
     * Get the application description from settings
     *
     * @return string
     */
    function app_description(): string
    {
        return app_setting('app.description', 'Professional event management system');
    }
}

if (!function_exists('primary_color')) {
    /**
     * Get the primary color from settings
     *
     * @return string
     */
    function primary_color(): string
    {
        return app_setting('appearance.primary_color', '#dc2626');
    }
}

if (!function_exists('secondary_color')) {
    /**
     * Get the secondary color from settings
     *
     * @return string
     */
    function secondary_color(): string
    {
        return app_setting('appearance.secondary_color', '#1e293b');
    }
}
