<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $name,
        public string $otpCode,
        public int $expiryMinutes,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Citiescapes — Your One-Time Password');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.otp');
    }
}
