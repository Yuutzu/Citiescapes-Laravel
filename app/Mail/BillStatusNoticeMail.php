<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BillStatusNoticeMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param string $stage one of: grace, delinquent, eviction
     */
    public function __construct(
        public string $tenantName,
        public string $stage,
        public string $billingPeriod,
        public int $daysOverdue,
        public string $totalAmount,
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->stage) {
            'eviction'   => 'Citiescapes — URGENT: Eviction Notice',
            'delinquent' => 'Citiescapes — Delinquent Account Notice',
            'grace'      => 'Citiescapes — Payment Reminder',
            default      => 'Citiescapes — Account Notice',
        };
        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.bill-status-notice');
    }
}
