<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckIn extends Model
{
    use HasFactory;

    protected $fillable = [
        'registration_id',
        'checked_in_at',
        'checked_in_by',
        'check_in_method',
    ];

    protected function casts(): array
    {
        return [
            'checked_in_at' => 'datetime',
        ];
    }

    /**
     * Get the registration this check-in belongs to.
     */
    public function registration()
    {
        return $this->belongsTo(Registration::class);
    }

    /**
     * Get the user who performed the check-in (for manual check-ins).
     */
    public function checkedInBy()
    {
        return $this->belongsTo(User::class, 'checked_in_by');
    }

    /**
     * Get the user who was checked in (through registration).
     */
    public function user()
    {
        return $this->hasOneThrough(User::class, Registration::class, 'id', 'id', 'registration_id', 'user_id');
    }

    /**
     * Get the event for this check-in (through registration).
     */
    public function event()
    {
        return $this->hasOneThrough(Event::class, Registration::class, 'id', 'id', 'registration_id', 'event_id');
    }

    /**
     * Scope for QR code check-ins.
     */
    public function scopeQrCode($query)
    {
        return $query->where('check_in_method', 'qr');
    }

    /**
     * Scope for manual check-ins.
     */
    public function scopeManual($query)
    {
        return $query->where('check_in_method', 'manual');
    }

    /**
     * Scope for ID-based check-ins.
     */
    public function scopeById($query)
    {
        return $query->where('check_in_method', 'id');
    }
}
