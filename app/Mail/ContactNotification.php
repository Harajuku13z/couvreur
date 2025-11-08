<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $data;
    public $companyName;

    /**
     * Create a new message instance.
     */
    public function __construct($data)
    {
        $this->data = $data;
        $this->companyName = setting('company_name', 'Votre Entreprise');
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('📧 Nouveau message de contact : ' . ($this->data['subject'] ?? 'Sans sujet'))
                    ->view('emails.contact-notification');
    }
}

