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

        // Vérifier si le PDF existe
        if ($this->devis->pdf_path && Storage::disk('local')->exists($this->devis->pdf_path)) {
            $attachments[] = Attachment::fromStorageDisk('local', $this->devis->pdf_path)
                ->as('Devis_' . $this->devis->numero . '.pdf');
        } else {
            // Si le PDF n'existe pas, essayer de le générer
            try {
                $pdfService = app(\App\Services\PdfService::class);
                $pdfService->generateDevisPdf($this->devis);
                $this->devis->refresh();
                
                // Vérifier à nouveau après génération
                if ($this->devis->pdf_path && Storage::disk('local')->exists($this->devis->pdf_path)) {
                    $attachments[] = Attachment::fromStorageDisk('local', $this->devis->pdf_path)
                        ->as('Devis_' . $this->devis->numero . '.pdf');
                }
            } catch (\Exception $e) {
                // Log l'erreur mais continue sans pièce jointe
                \Log::error('Erreur génération PDF pour email', [
                    'devis_id' => $this->devis->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $attachments;
    }
}

