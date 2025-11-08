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
use App\Models\Setting;

class FactureReminder extends Mailable
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
        $fromAddress = Setting::get('mail_from_address', env('MAIL_FROM_ADDRESS', 'hello@example.com'));
        $fromName = Setting::get('mail_from_name', env('MAIL_FROM_NAME', 'Example'));
        
        return new Envelope(
            from: new \Illuminate\Mail\Mailables\Address($fromAddress, $fromName),
            subject: 'Rappel - Facture ' . $this->facture->numero . ' en attente de paiement',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.facture_reminder',
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

