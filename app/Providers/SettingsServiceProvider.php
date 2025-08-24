<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Blade;

class SettingsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share settings with all views
        View::composer('*', function ($view) {
            $view->with('appSettings', $this->getAppSettings());
        });

        // Add Blade directive for settings
        Blade::directive('setting', function ($expression) {
            return "<?php echo app('App\\Services\\SettingsService')->getSettingValue($expression); ?>";
        });

        // Add Blade directive for app name
        Blade::directive('appName', function () {
            return "<?php echo app('App\\Services\\SettingsService')->getSettingValue('app.name', config('app.name')); ?>";
        });

        // Add Blade directive for app logo
        Blade::directive('appLogo', function () {
            return "<?php echo app('App\\Services\\SettingsService')->getSettingValue('app.logo', null); ?>";
        });
    }

    /**
     * Get application settings for views
     */
    private function getAppSettings(): array
    {
        return Cache::remember('app_settings_for_views', 3600, function () {
            try {
                $settings = Setting::where('is_public', true)
                    ->orWhereIn('key', [
                        'app.name',
                        'app.description',
                        'app.logo',
                        'app.favicon',
                        'appearance.primary_color',
                        'appearance.secondary_color',
                        'appearance.theme',
                        'appearance.footer_text'
                    ])
                    ->get()
                    ->keyBy('key');

                return [
                    'name' => $settings->get('app.name')?->display_value ?? config('app.name'),
                    'description' => $settings->get('app.description')?->display_value ?? 'Professional event management system',
                    'logo' => $settings->get('app.logo')?->display_value,
                    'favicon' => $settings->get('app.favicon')?->display_value,
                    'primary_color' => $settings->get('appearance.primary_color')?->display_value ?? '#dc2626',
                    'secondary_color' => $settings->get('appearance.secondary_color')?->display_value ?? '#1e293b',
                    'theme' => $settings->get('appearance.theme')?->display_value ?? 'default',
                    'footer_text' => $settings->get('appearance.footer_text')?->display_value ?? '&copy; ' . date('Y') . ' Air Force Life Cycle Management Center. All rights reserved.',
                    'custom_css' => $settings->get('appearance.custom_css')?->display_value,
                ];
            } catch (\Exception $e) {
                // Fallback to config values if settings table doesn't exist yet
                return [
                    'name' => config('app.name'),
                    'description' => 'Professional event management system',
                    'logo' => null,
                    'favicon' => null,
                    'primary_color' => '#dc2626',
                    'secondary_color' => '#1e293b',
                    'theme' => 'default',
                    'footer_text' => '&copy; ' . date('Y') . ' Air Force Life Cycle Management Center. All rights reserved.',
                    'custom_css' => null,
                ];
            }
        });
    }
}
