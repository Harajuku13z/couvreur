<?php

namespace App\Services;

use App\Models\Devis;
use App\Models\Facture;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class PdfService
{
    /**
     * Générer le PDF d'un devis
     */
    public function generateDevisPdf(Devis $devis): string
    {
        $devis->load(['client', 'lignesDevis']);

        $pdf = Pdf::loadView('pdfs.devis', [
            'devis' => $devis,
            'companySettings' => $this->getCompanySettings(),
        ]);

        $filename = 'devis_' . $devis->numero . '_' . time() . '.pdf';
        $path = 'devis/' . $filename;

        // Sauvegarder le PDF
        Storage::disk('local')->put($path, $pdf->output());

        // Mettre à jour le devis avec le chemin du PDF
        $devis->update(['pdf_path' => $path]);

        return $path;
    }

    /**
     * Générer le PDF d'une facture
     */
    public function generateFacturePdf(Facture $facture): string
    {
        $facture->load(['client', 'devis']);

        $pdf = Pdf::loadView('pdfs.facture', [
            'facture' => $facture,
            'companySettings' => $this->getCompanySettings(),
        ]);

        $filename = 'facture_' . $facture->numero . '_' . time() . '.pdf';
        $path = 'factures/' . $filename;

        // Sauvegarder le PDF
        Storage::disk('local')->put($path, $pdf->output());

        // Mettre à jour la facture avec le chemin du PDF
        $facture->update(['pdf_path' => $path]);

        return $path;
    }

    /**
     * Obtenir le chemin complet du PDF
     */
    public function getPdfPath(string $relativePath): string
    {
        return Storage::disk('local')->path($relativePath);
    }

    /**
     * Récupérer les paramètres de l'entreprise
     */
    private function getCompanySettings(): array
    {
        return [
            'name' => \App\Models\Setting::get('company_name', 'Votre Entreprise'),
            'address' => \App\Models\Setting::get('company_address', ''),
            'postal_code' => \App\Models\Setting::get('company_postal_code', ''),
            'city' => \App\Models\Setting::get('company_city', ''),
            'phone' => \App\Models\Setting::get('company_phone', ''),
            'email' => \App\Models\Setting::get('company_email', ''),
            'siret' => \App\Models\Setting::get('company_siret', ''),
            'tva' => \App\Models\Setting::get('company_tva', ''),
        ];
    }
}

