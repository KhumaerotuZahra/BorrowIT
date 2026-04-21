<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BorrowNotification extends Mailable
{
    use Queueable, SerializesModels;

    public string $type;
    public string $userName;
    public string $title;
    public string $body;
    public array $details;

    public function __construct(string $type, string $userName, string $title, string $body, array $details = [])
    {
        $this->type = $type;
        $this->userName = $userName;
        $this->title = $title;
        $this->body = $body;
        $this->details = $details;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[BorrowIT] {$this->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.borrow-notification',
        );
    }
}
