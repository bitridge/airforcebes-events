<?php

namespace App\Http\Controllers\Admin;

use App\Enums\SettingGroup;
use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;

class SettingsController extends Controller
{
    protected SettingsService $settingsService;

    public function __construct(SettingsService $settingsService)
    {
        $this->settingsService = $settingsService;
    }

    /**
     * Display the settings management interface
     */
    public function index(): View
    {
        try {
            $settings = $this->settingsService->getAllSettingsGrouped();
            $smtpProviders = $this->settingsService->getSmtpProviderTemplates();
            $cacheStats = $this->settingsService->getCacheStats();

            return view('admin.settings.index', compact('settings', 'smtpProviders', 'cacheStats'));

        } catch (\Exception $e) {
            Log::error('Error loading settings', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return view('admin.settings.index')->with('error', 'Failed to load settings: ' . $e->getMessage());
        }
    }

    /**
     * Update settings for a specific group
     */
    public function updateGroup(Request $request, string $group): JsonResponse
    {
        try {
            $request->validate([
                'settings' => 'required|array',
            ]);

            $settings = $request->input('settings');
            
            // Handle file uploads
            foreach ($settings as $key => $value) {
                if ($request->hasFile("settings.{$key}")) {
                    $file = $request->file("settings.{$key}");
                    
                    // Validate file
                    $maxSize = $key === 'app.logo' ? 2048 : ($key === 'app.favicon' ? 1024 : 5120); // KB
                    $request->validate([
                        "settings.{$key}" => "file|max:{$maxSize}|mimes:" . $this->getAllowedMimes($key),
                    ]);
                    
                    // Store file and update setting value
                    $path = $file->store('settings', 'public');
                    $settings[$key] = $path;
                }
            }
            
            $results = $this->settingsService->updateMultipleSettings($settings);

            Log::info('Settings updated for group', [
                'group' => $group,
                'user_id' => auth()->id(),
                'settings_count' => count($settings),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Settings updated successfully',
                'results' => $results,
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating settings group', [
                'group' => $group,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get allowed MIME types for a setting key
     */
    private function getAllowedMimes(string $key): string
    {
        return match ($key) {
            'app.logo' => 'jpg,jpeg,png,gif,svg',
            'app.favicon' => 'ico,png',
            default => 'jpg,jpeg,png,gif,svg,pdf,doc,docx,txt',
        };
    }

    /**
     * Update a single setting
     */
    public function updateSetting(Request $request, string $key): JsonResponse
    {
        try {
            $request->validate([
                'value' => 'required',
            ]);

            $value = $request->input('value');
            $result = $this->settingsService->updateSetting($key, $value);

            if ($result) {
                Log::info('Setting updated', [
                    'key' => $key,
                    'user_id' => auth()->id(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Setting updated successfully',
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'Failed to update setting',
            ], 500);

        } catch (\Exception $e) {
            Log::error('Error updating setting', [
                'key' => $key,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update setting: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test SMTP connection
     */
    public function testSmtp(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'smtp_settings' => 'required|array',
                'smtp_settings.host' => 'required|string',
                'smtp_settings.port' => 'required|integer',
                'smtp_settings.username' => 'nullable|string',
                'smtp_settings.password' => 'nullable|string',
                'smtp_settings.encryption' => 'required|string',
                'smtp_settings.driver' => 'required|string',
                'smtp_settings.from_email' => 'required|email',
                'smtp_settings.from_name' => 'required|string',
                'smtp_settings.test_email' => 'required|email',
            ]);

            $smtpSettings = $request->input('smtp_settings');
            $result = $this->settingsService->testSmtpConnection($smtpSettings);

            Log::info('SMTP test attempted', [
                'host' => $smtpSettings['host'],
                'user_id' => auth()->id(),
                'success' => $result['success'],
            ]);

            return response()->json($result);

        } catch (\Exception $e) {
            Log::error('Error testing SMTP', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to test SMTP: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export settings to JSON
     */
    public function export(): JsonResponse
    {
        try {
            $jsonData = $this->settingsService->exportSettings();
            $filename = 'settings_export_' . now()->format('Y_m_d_H_i_s') . '.json';

            Log::info('Settings exported', [
                'user_id' => auth()->id(),
                'filename' => $filename,
            ]);

            return response()->json([
                'success' => true,
                'data' => $jsonData,
                'filename' => $filename,
            ]);

        } catch (\Exception $e) {
            Log::error('Error exporting settings', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to export settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Import settings from JSON
     */
    public function import(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'settings_file' => 'required|file|mimes:json|max:1024',
            ]);

            $file = $request->file('settings_file');
            $jsonData = file_get_contents($file->getPathname());
            
            $result = $this->settingsService->importSettings($jsonData);

            Log::info('Settings imported', [
                'user_id' => auth()->id(),
                'filename' => $file->getClientOriginalName(),
                'results' => $result['results'],
                'errors' => $result['errors'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Settings imported successfully',
                'results' => $result,
            ]);

        } catch (\Exception $e) {
            Log::error('Error importing settings', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to import settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Backup current settings
     */
    public function backup(): JsonResponse
    {
        try {
            $filename = $this->settingsService->backupSettings();

            Log::info('Settings backup created', [
                'user_id' => auth()->id(),
                'filename' => $filename,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Settings backup created successfully',
                'filename' => $filename,
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating settings backup', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create backup: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Restore settings from backup
     */
    public function restore(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'backup_file' => 'required|string',
            ]);

            $filename = $request->input('backup_file');
            $result = $this->settingsService->restoreSettings($filename);

            Log::info('Settings restored from backup', [
                'user_id' => auth()->id(),
                'filename' => $filename,
                'results' => $result['results'],
                'errors' => $result['errors'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Settings restored successfully',
                'results' => $result,
            ]);

        } catch (\Exception $e) {
            Log::error('Error restoring settings', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to restore settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Reset settings to defaults
     */
    public function resetToDefaults(): JsonResponse
    {
        try {
            $results = $this->settingsService->resetAllSettings();

            Log::info('Settings reset to defaults', [
                'user_id' => auth()->id(),
                'results' => $results,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Settings reset to defaults successfully',
                'results' => $results,
            ]);

        } catch (\Exception $e) {
            Log::error('Error resetting settings', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reset settings: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear settings cache
     */
    public function clearCache(): JsonResponse
    {
        try {
            $this->settingsService->clearAllCache();

            Log::info('Settings cache cleared', [
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Settings cache cleared successfully',
            ]);

        } catch (\Exception $e) {
            Log::error('Error clearing settings cache', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear cache: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get cache statistics
     */
    public function getCacheStats(): JsonResponse
    {
        try {
            $stats = $this->settingsService->getCacheStats();

            return response()->json([
                'success' => true,
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting cache stats', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get cache stats: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download backup file
     */
    public function downloadBackup(string $filename): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        try {
            $backupPath = 'backups/' . $filename;
            
            if (!Storage::disk('local')->exists($backupPath)) {
                abort(404, 'Backup file not found');
            }

            Log::info('Settings backup downloaded', [
                'user_id' => auth()->id(),
                'filename' => $filename,
            ]);

            return response()->download(
                Storage::disk('local')->path($backupPath),
                $filename,
                ['Content-Type' => 'application/json']
            );

        } catch (\Exception $e) {
            Log::error('Error downloading backup', [
                'filename' => $filename,
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            abort(500, 'Failed to download backup file');
        }
    }

    /**
     * Get available backup files
     */
    public function getBackups(): JsonResponse
    {
        try {
            $backups = collect(Storage::disk('local')->files('backups'))
                ->filter(function ($file) {
                    return str_ends_with($file, '.json');
                })
                ->map(function ($file) {
                    $filename = basename($file);
                    $path = 'backups/' . $filename;
                    
                    return [
                        'filename' => $filename,
                        'size' => Storage::disk('local')->size($path),
                        'created_at' => Storage::disk('local')->lastModified($path),
                        'download_url' => route('admin.settings.download-backup', basename($file)),
                    ];
                })
                ->sortByDesc('created_at')
                ->values();

            return response()->json([
                'success' => true,
                'backups' => $backups,
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting backup files', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get backup files: ' . $e->getMessage(),
            ], 500);
        }
    }
}
