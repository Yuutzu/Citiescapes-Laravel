<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AnnouncementMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $recipientName,
        public string $title,
        public string $body,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Citiescapes — ' . $this->title);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.announcement');
    }
}
