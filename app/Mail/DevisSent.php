<?php

namespace App\Mail;

use App\Models\Devis;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class DevisSent extends Mailable
{
    use Queueable, SerializesModels;

    public $devis;

    /**
     * Create a new message instance.
     */
    public function __construct(Devis $devis)
    {
        $this->devis = $devis;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre devis ' . $this->devis->numero,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.devis_sent',
            with: [
                'devis' => $this->devis,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        $attachments = [];

        if ($this->devis->pdf_path && Storage::disk('local')->exists($this->devis->pdf_path)) {
            $attachments[] = Attachment::fromStorageDisk('local', $this->devis->pdf_path)
                ->as('Devis_' . $this->devis->numero . '.pdf');
        }

        return $attachments;
    }
}

