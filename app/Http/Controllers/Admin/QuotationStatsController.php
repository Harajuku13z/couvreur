<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Devis;
use App\Models\Facture;
use Illuminate\Http\Request;

class QuotationStatsController extends Controller
{
    /**
     * Tableau de bord avec statistiques
     */
    public function dashboard()
    {
        // Utilisation de cursors pour les calculs sur de grandes quantités de données
        
        // Chiffre d'Affaire Total (CA) - Factures payées uniquement
        $totalCA = 0;
        $paidInvoices = Facture::where('statut', 'Payée')->cursor();
        foreach ($paidInvoices as $invoice) {
            $totalCA += $invoice->prix_total_ttc;
        }

        // CA Potentiel - Devis acceptés non encore payés
        $caPotentiel = 0;
        $acceptedQuotations = Devis::where('statut', 'Accepté')
            ->whereDoesntHave('facture', function($q) {
                $q->where('statut', 'Payée');
            })
            ->cursor();
        foreach ($acceptedQuotations as $quotation) {
            $caPotentiel += $quotation->total_ttc;
        }

        // Taux de conversion
        $totalDevis = Devis::count();
        $devisAcceptes = Devis::where('statut', 'Accepté')->count();
        $tauxConversion = $totalDevis > 0 ? ($devisAcceptes / $totalDevis) * 100 : 0;

        // Factures en attente (impayées)
        $facturesEnAttente = Facture::where('statut', 'Impayée')
            ->with('client')
            ->orderBy('date_echeance', 'asc')
            ->limit(10)
            ->get();

        // Statistiques par statut
        $statsDevis = [
            'Brouillon' => Devis::where('statut', 'Brouillon')->count(),
            'En Attente' => Devis::where('statut', 'En Attente')->count(),
            'Accepté' => Devis::where('statut', 'Accepté')->count(),
            'Refusé' => Devis::where('statut', 'Refusé')->count(),
        ];

        $statsFactures = [
            'Impayée' => Facture::where('statut', 'Impayée')->count(),
            'Payée' => Facture::where('statut', 'Payée')->count(),
            'Annulée' => Facture::where('statut', 'Annulée')->count(),
        ];

        // Derniers devis
        $derniersDevis = Devis::with('client')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Dernières factures
        $dernieresFactures = Facture::with('client')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        return view('admin.quotations.dashboard', compact(
            'totalCA',
            'caPotentiel',
            'tauxConversion',
            'facturesEnAttente',
            'statsDevis',
            'statsFactures',
            'derniersDevis',
            'dernieresFactures'
        ));
    }
}

