<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContractTerminatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $tenantName,
        public string $roomNumber,
        public string $terminatedOn, // formatted date string
        public string $reason,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Citiescapes — Contract Termination Notice');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contract-terminated');
    }
}
