<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Registration extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'user_id',
        'registration_code',
        'qr_code_data',
        'registration_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'registration_date' => 'datetime',
        ];
    }

    /**
     * Get the event this registration belongs to.
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get the user this registration belongs to.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the check-in record for this registration.
     */
    public function checkIn()
    {
        return $this->hasOne(CheckIn::class);
    }

    /**
     * Check if the user is checked in.
     */
    public function isCheckedIn(): bool
    {
        return $this->checkIn()->exists();
    }

    /**
     * Scope for confirmed registrations.
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope for pending registrations.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope for cancelled registrations.
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }
}
