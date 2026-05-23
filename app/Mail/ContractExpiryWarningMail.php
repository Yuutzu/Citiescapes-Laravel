<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractExpiryWarningMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $tenantName,
        public string $roomNumber,
        public int $daysRemaining,      // 30 or 7
        public string $endsOn,          // formatted date
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->daysRemaining === 7
            ? 'Citiescapes — Urgent: Lease Expires in 7 Days'
            : 'Citiescapes — Lease Expires in 30 Days';
        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contract-expiry-warning');
    }
}
