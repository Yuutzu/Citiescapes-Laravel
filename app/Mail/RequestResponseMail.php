<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RequestResponseMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $tenantName,
        public string $type,
        public string $subject,
        public string $status,
        public string $adminResponse,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Citiescapes — Update on your ' . ucfirst($this->type) . ': ' . $this->subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.request-response');
    }
}
