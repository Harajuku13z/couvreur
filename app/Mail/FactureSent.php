<?php

namespace App\Mail;

use App\Models\Facture;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class FactureSent extends Mailable
{
    use Queueable, SerializesModels;

    public $facture;

    /**
     * Create a new message instance.
     */
    public function __construct(Facture $facture)
    {
        $this->facture = $facture;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Votre facture ' . $this->facture->numero,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.facture_sent',
            with: [
                'facture' => $this->facture,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     */
    public function attachments(): array
    {
        $attachments = [];

        if ($this->facture->pdf_path && Storage::disk('local')->exists($this->facture->pdf_path)) {
            $attachments[] = Attachment::fromStorageDisk('local', $this->facture->pdf_path)
                ->as('Facture_' . $this->facture->numero . '.pdf');
        }

        return $attachments;
    }
}

