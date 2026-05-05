<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TenantRequestMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $tenantName,
        public string $type,
        public string $subject,
        public string $body,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Citiescapes — New ' . ucfirst($this->type) . ': ' . $this->subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.tenant-request');
    }
}
