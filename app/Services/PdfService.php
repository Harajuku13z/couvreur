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
        try {
            $devis->load(['client', 'lignesDevis']);

            // Vérifier que le devis a des lignes
            if ($devis->lignesDevis->isEmpty()) {
                throw new \Exception('Le devis n\'a pas de lignes. Impossible de générer le PDF.');
            }

            // Vérifier que le client existe
            if (!$devis->client) {
                throw new \Exception('Le client associé au devis n\'existe pas.');
            }

            $companySettings = $this->getCompanySettings();

            Log::info('Génération PDF devis', [
                'devis_id' => $devis->id,
                'devis_numero' => $devis->numero,
                'client_id' => $devis->client_id,
                'lignes_count' => $devis->lignesDevis->count(),
            ]);

            $pdf = Pdf::loadView('pdfs.devis', [
                'devis' => $devis,
                'companySettings' => $companySettings,
            ]);

            // Options PDF
            $pdf->setPaper('a4', 'portrait');
            $pdf->setOption('enable-local-file-access', true);

            $filename = 'devis_' . $devis->numero . '_' . time() . '.pdf';
            $path = 'devis/' . $filename;

            // S'assurer que le dossier existe
            $directory = dirname(Storage::disk('local')->path($path));
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            // Sauvegarder le PDF
            $pdfContent = $pdf->output();
            Storage::disk('local')->put($path, $pdfContent);

            // Vérifier que le fichier a bien été créé
            if (!Storage::disk('local')->exists($path)) {
                throw new \Exception('Le fichier PDF n\'a pas pu être sauvegardé');
            }

            // Mettre à jour le devis avec le chemin du PDF
            $devis->update(['pdf_path' => $path]);

            Log::info('PDF devis généré avec succès', [
                'devis_id' => $devis->id,
                'path' => $path,
                'size' => Storage::disk('local')->size($path),
            ]);

            return $path;
        } catch (\Exception $e) {
            Log::error('Erreur génération PDF devis', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'devis_id' => $devis->id ?? null,
            ]);
            throw $e;
        }
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

