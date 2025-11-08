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
            // Utiliser asset() pour générer l'URL correcte
            $logoPath = 'logo/logo.svg';
            
            // Vérifier si le fichier SVG existe
            if (!file_exists(public_path($logoPath))) {
                // Fallback sur PNG si SVG n'existe pas
                $logoPath = 'logo/logo.png';
                if (!file_exists(public_path($logoPath))) {
                    // Pas de logo disponible
                    return;
                }
            }
            
            // Générer l'URL avec un paramètre de version basé sur la date de modification
            // pour forcer le rechargement et éviter le cache
            $filemtime = file_exists(public_path($logoPath)) ? filemtime(public_path($logoPath)) : time();
            $logoUrl = asset($logoPath) . '?v=' . $filemtime;
            
            // S'assurer que l'URL est en HTTPS (requis pour BIMI)
            $logoUrl = str_replace('http://', 'https://', $logoUrl);
            
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

