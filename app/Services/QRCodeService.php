<?php

namespace App\Services;

use App\Models\Registration;
use App\Models\Event;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Cache;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Carbon\Carbon;

class QRCodeService
{
    /**
     * QR code configuration constants
     */
    const QR_SIZE = 300;
    const QR_FORMAT = 'svg';
    const QR_MARGIN = 0;
    const SECURITY_SALT = 'af-events-qr-security-2024';
    const QR_CACHE_TTL = 3600; // 1 hour cache

    /**
     * Generate QR code for a registration
     */
    public function generateQRCode(Registration $registration): array
    {
        // Generate security hash
        $securityHash = $this->generateSecurityHash($registration);
        
        // Create QR code data
        $qrData = $this->createQRCodeData($registration, $securityHash);
        
        // Generate QR code image
        $qrCodeImage = $this->createQRCodeImage($qrData);
        
        // Store QR code file
        $filename = $this->storeQRCodeFile($registration, $qrCodeImage);
        
        // Update registration with QR data
        $registration->update([
            'qr_code_data' => json_encode($qrData),
            'qr_security_hash' => $securityHash,
        ]);

        return [
            'qr_data' => $qrData,
            'security_hash' => $securityHash,
            'filename' => $filename,
            'file_path' => Storage::disk('public')->path($filename),
            'file_url' => Storage::disk('public')->url($filename),
        ];
    }

    /**
     * Validate QR code data
     */
    public function validateQRCode(string $qrCodeData): array
    {
        try {
            // Decode QR code data
            $data = json_decode($qrCodeData, true);
            
            if (!$data || !$this->isValidQRCodeStructure($data)) {
                return $this->validationError('Invalid QR code format');
            }

            // Find registration
            $registration = Registration::where('id', $data['registration_id'])
                ->where('registration_code', $data['registration_code'])
                ->with(['user', 'event', 'checkIn'])
                ->first();

            if (!$registration) {
                return $this->validationError('Registration not found');
            }

            // Validate security hash
            if (!$this->verifySecurityHash($registration, $data['security_hash'])) {
                return $this->validationError('Invalid security hash - QR code may be tampered');
            }

            // Check expiration
            if ($this->isQRCodeExpired($data)) {
                return $this->validationError('QR code has expired');
            }

            // Check registration status
            if ($registration->status !== 'confirmed') {
                return $this->validationError('Registration is not confirmed');
            }

            // Check if already checked in
            if ($registration->isCheckedIn()) {
                return $this->validationError('Already checked in', [
                    'checked_in_at' => $registration->checkIn->checked_in_at,
                    'checked_in_by' => $registration->checkIn->checkedInBy->full_name ?? 'Unknown',
                ]);
            }

            // Check event eligibility
            if (!$this->isEventEligibleForCheckIn($registration->event)) {
                return $this->validationError('Event is not currently accepting check-ins');
            }

            return [
                'valid' => true,
                'registration' => $registration,
                'data' => $data,
                'message' => 'QR code is valid',
            ];

        } catch (\Exception $e) {
            \Log::error('QR code validation failed', [
                'qr_data' => $qrCodeData,
                'error' => $e->getMessage(),
            ]);

            return $this->validationError('Failed to validate QR code');
        }
    }

    /**
     * Get QR code for display
     */
    public function getQRCodeForDisplay(Registration $registration, int $size = null): string
    {
        $size = $size ?? self::QR_SIZE;
        $cacheKey = "qr_display_{$registration->id}_{$size}";

        return Cache::remember($cacheKey, self::QR_CACHE_TTL, function () use ($registration, $size) {
            if (!$registration->qr_code_data) {
                $this->generateQRCode($registration);
                $registration->refresh();
            }

            return QrCode::size($size)
                ->format(self::QR_FORMAT)
                ->margin(self::QR_MARGIN)
                ->generate($registration->qr_code_data);
        });
    }

    /**
     * Get QR code download URL
     */
    public function getQRCodeDownloadUrl(Registration $registration): string
    {
        return route('registrations.qr-code', $registration);
    }

    /**
     * Get QR code for printing
     */
    public function getQRCodeForPrint(Registration $registration): array
    {
        $qrCodeSvg = $this->getQRCodeForDisplay($registration, 400);
        
        return [
            'qr_code' => $qrCodeSvg,
            'registration' => $registration,
            'user' => $registration->user,
            'event' => $registration->event,
            'generated_at' => now(),
        ];
    }

    /**
     * Regenerate QR code for registration
     */
    public function regenerateQRCode(Registration $registration): array
    {
        // Delete old QR code file
        $this->deleteQRCodeFile($registration);
        
        // Clear cache
        $this->clearQRCodeCache($registration);
        
        // Generate new QR code
        return $this->generateQRCode($registration);
    }

    /**
     * Delete QR code file
     */
    public function deleteQRCodeFile(Registration $registration): bool
    {
        $filename = "qr_codes/registration_{$registration->id}.svg";
        
        if (Storage::disk('public')->exists($filename)) {
            return Storage::disk('public')->delete($filename);
        }
        
        return true;
    }

    /**
     * Clear QR code cache
     */
    public function clearQRCodeCache(Registration $registration): void
    {
        $sizes = [200, 300, 400, 500];
        
        foreach ($sizes as $size) {
            Cache::forget("qr_display_{$registration->id}_{$size}");
        }
    }

    /**
     * Create QR code data structure
     */
    private function createQRCodeData(Registration $registration, string $securityHash): array
    {
        return [
            'type' => 'airforce_event_registration',
            'version' => '2.0',
            'registration_id' => $registration->id,
            'registration_code' => $registration->registration_code,
            'event_id' => $registration->event_id,
            'user_id' => $registration->user_id,
            'event_title' => $registration->event->title,
            'user_name' => $registration->user->full_name,
            'registration_date' => $registration->registration_date->toISOString(),
            'generated_at' => now()->toISOString(),
            'expires_at' => $registration->event->end_date->addDays(1)->toISOString(),
            'security_hash' => $securityHash,
            'check_in_url' => route('admin.check-in.index'),
            'verification_url' => route('qr.verify', ['hash' => $securityHash]),
        ];
    }

    /**
     * Generate security hash for QR code
     */
    private function generateSecurityHash(Registration $registration): string
    {
        $data = [
            $registration->id,
            $registration->registration_code,
            $registration->event_id,
            $registration->user_id,
            $registration->registration_date->timestamp,
            self::SECURITY_SALT,
            config('app.key'),
        ];

        return hash('sha256', implode('|', $data));
    }

    /**
     * Verify security hash
     */
    private function verifySecurityHash(Registration $registration, string $providedHash): bool
    {
        $expectedHash = $this->generateSecurityHash($registration);
        return hash_equals($expectedHash, $providedHash);
    }

    /**
     * Create QR code image
     */
    private function createQRCodeImage(array $qrData): string
    {
        return QrCode::size(self::QR_SIZE)
            ->format(self::QR_FORMAT)
            ->margin(self::QR_MARGIN)
            ->errorCorrection('M')
            ->generate(json_encode($qrData));
    }

    /**
     * Store QR code file
     */
    private function storeQRCodeFile(Registration $registration, string $qrCodeImage): string
    {
        $filename = "qr_codes/registration_{$registration->id}.svg";
        Storage::disk('public')->put($filename, $qrCodeImage);
        return $filename;
    }

    /**
     * Check if QR code structure is valid
     */
    private function isValidQRCodeStructure(array $data): bool
    {
        $requiredFields = [
            'type',
            'version',
            'registration_id',
            'registration_code',
            'event_id',
            'user_id',
            'security_hash',
            'generated_at',
            'expires_at',
        ];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                return false;
            }
        }

        // Check type
        if ($data['type'] !== 'airforce_event_registration') {
            return false;
        }

        // Check version compatibility
        $supportedVersions = ['1.0', '2.0'];
        if (!in_array($data['version'], $supportedVersions)) {
            return false;
        }

        return true;
    }

    /**
     * Check if QR code is expired
     */
    private function isQRCodeExpired(array $data): bool
    {
        if (!isset($data['expires_at'])) {
            return false; // Legacy QR codes without expiration
        }

        $expiresAt = Carbon::parse($data['expires_at']);
        return $expiresAt->isPast();
    }

    /**
     * Check if event is eligible for check-in
     */
    private function isEventEligibleForCheckIn(Event $event): bool
    {
        // Event must be published
        if (!$event->isPublished()) {
            return false;
        }

        // Event must not be cancelled
        if ($event->status === 'cancelled') {
            return false;
        }

        // Check-in should be available from 2 hours before start until 1 day after end
        $checkInStart = $event->start_date->subHours(2);
        $checkInEnd = $event->end_date->addDay();

        return now()->between($checkInStart, $checkInEnd);
    }

    /**
     * Create validation error response
     */
    private function validationError(string $message, array $additionalData = []): array
    {
        return array_merge([
            'valid' => false,
            'message' => $message,
        ], $additionalData);
    }

    /**
     * Get QR code statistics
     */
    public function getQRCodeStats(): array
    {
        return [
            'total_generated' => Registration::whereNotNull('qr_code_data')->count(),
            'generated_today' => Registration::whereNotNull('qr_code_data')
                ->whereDate('updated_at', today())
                ->count(),
            'total_scanned' => \DB::table('check_ins')
                ->where('check_in_method', 'qr')
                ->count(),
            'scanned_today' => \DB::table('check_ins')
                ->where('check_in_method', 'qr')
                ->whereDate('checked_in_at', today())
                ->count(),
            'success_rate' => $this->calculateQRSuccessRate(),
        ];
    }

    /**
     * Calculate QR code success rate
     */
    private function calculateQRSuccessRate(): float
    {
        $totalGenerated = Registration::whereNotNull('qr_code_data')->count();
        $totalScanned = \DB::table('check_ins')->where('check_in_method', 'qr')->count();

        if ($totalGenerated === 0) {
            return 0;
        }

        return round(($totalScanned / $totalGenerated) * 100, 2);
    }
}
