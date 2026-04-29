<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CobrancaEmail extends Mailable
{
    use Queueable, SerializesModels;

    public string $bodyContent;

    public string $subjectContent;

    public string $fromEmail;

    public string $fromName;

    public string $tenantName;

    public string $logoUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(string $subject, string $body, string $fromEmail, string $fromName, string $tenantName = 'Fiscaut', ?string $logoUrl = null)
    {
        $this->subjectContent = $subject;
        $this->bodyContent = $body;
        $this->fromEmail = $fromEmail;
        $this->fromName = $fromName;
        $this->tenantName = $tenantName;

        if ($logoUrl) {
            $this->logoUrl = $logoUrl;
        } else {
            $this->logoUrl = url('images/application/logo-no-background.png');
        }
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address($this->fromEmail, $this->fromName),
            subject: $this->subjectContent,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.cobranca',
            with: [
                'bodyContent' => $this->bodyContent,
                'logoUrl' => $this->logoUrl,
                'tenantName' => $this->tenantName,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
