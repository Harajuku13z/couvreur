<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Address;

class ContactConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $companyName;
    public $companyEmail;
    public $companyPhone;

    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->companyName = setting('company_name', 'Votre Entreprise');
        $this->companyEmail = setting('company_email', '');
        $this->companyPhone = setting('company_phone', '');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $fromAddress = setting('mail_from_address') ?: config('mail.from.address');
        $fromName = setting('mail_from_name') ?: config('mail.from.name');

        $envelope = new Envelope(
            from: new Address($fromAddress, $fromName),
            subject: '✅ Nous avons bien reçu votre message - ' . $this->companyName,
        );

        // Ajouter le header BIMI pour afficher le logo dans Gmail
        $this->withSymfonyMessage(function ($message) {
            $logoUrl = url('/logo/logo.svg');
            // Vérifier si le fichier SVG existe, sinon utiliser PNG
            if (!file_exists(public_path('logo/logo.svg'))) {
                $logoUrl = url('/logo/logo.png');
            }
            // BIMI header - Gmail affichera le logo si le DNS BIMI est configuré
            $message->getHeaders()->addTextHeader('X-BIMI-Logo', $logoUrl);
        });

        return $envelope;
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-confirmation',
        );
    }
}

