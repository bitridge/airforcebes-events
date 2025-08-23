<?php

namespace App\Mail;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class RegistrationConfirmation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Registration $registration;

    /**
     * Create a new message instance.
     */
    public function __construct(Registration $registration)
    {
        $this->registration = $registration;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Event Registration Confirmation - ' . $this->registration->event->title,
            from: config('mail.from.address', 'noreply@airforcebes.mil'),
            replyTo: 'events@airforcebes.mil',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.registration-confirmation',
            with: [
                'registration' => $this->registration,
                'event' => $this->registration->event,
                'user' => $this->registration->user,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        $attachments = [];

        // Attach QR code if it exists
        $qrCodePath = "qr_codes/registration_{$this->registration->id}.svg";
        
        if (Storage::disk('public')->exists($qrCodePath)) {
            $attachments[] = Attachment::fromStorageDisk('public', $qrCodePath)
                ->as("qr_code_{$this->registration->registration_code}.svg")
                ->withMime('image/svg+xml');
        }

        return $attachments;
    }
}
