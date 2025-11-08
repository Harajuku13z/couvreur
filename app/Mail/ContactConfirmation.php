<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

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
     * Build the message.
     */
    public function build()
    {
        return $this->subject('✅ Nous avons bien reçu votre message - ' . $this->companyName)
                    ->view('emails.contact-confirmation');
    }
}

