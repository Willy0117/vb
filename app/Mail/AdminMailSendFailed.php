<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminMailSendFailed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public array $details) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[要対応] corp宛メール送信失敗');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.admin_mail_send_failed');
    }

    public function attachments(): array
    {
        return [];
    }
}