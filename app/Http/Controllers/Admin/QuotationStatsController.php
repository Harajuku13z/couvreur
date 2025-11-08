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
        try {
            // Utilisation de cursors pour les calculs sur de grandes quantités de données
            
            // Chiffre d'Affaire Total (CA) - Factures payées uniquement
            $totalCA = 0;
            try {
                $paidInvoices = Facture::where('statut', 'Payée')->cursor();
                foreach ($paidInvoices as $invoice) {
                    $totalCA += $invoice->prix_total_ttc;
                }
            } catch (\Exception $e) {
                \Log::warning('Erreur calcul CA total', ['error' => $e->getMessage()]);
            }

            // CA Potentiel - Devis acceptés non encore payés
            $caPotentiel = 0;
            try {
                $acceptedQuotations = Devis::where('statut', 'Accepté')
                    ->whereDoesntHave('facture', function($q) {
                        $q->where('statut', 'Payée');
                    })
                    ->cursor();
                foreach ($acceptedQuotations as $quotation) {
                    $caPotentiel += $quotation->total_ttc;
                }
            } catch (\Exception $e) {
                \Log::warning('Erreur calcul CA potentiel', ['error' => $e->getMessage()]);
            }

            // Taux de conversion
            $totalDevis = 0;
            $devisAcceptes = 0;
            $tauxConversion = 0;
            try {
                $totalDevis = Devis::count();
                $devisAcceptes = Devis::where('statut', 'Accepté')->count();
                $tauxConversion = $totalDevis > 0 ? ($devisAcceptes / $totalDevis) * 100 : 0;
            } catch (\Exception $e) {
                \Log::warning('Erreur calcul taux conversion', ['error' => $e->getMessage()]);
            }

            // Factures en attente (impayées)
            $facturesEnAttente = collect([]);
            try {
                $facturesEnAttente = Facture::where('statut', 'Impayée')
                    ->with('client')
                    ->orderBy('date_echeance', 'asc')
                    ->limit(10)
                    ->get();
            } catch (\Exception $e) {
                \Log::warning('Erreur chargement factures en attente', ['error' => $e->getMessage()]);
            }

            // Statistiques par statut
            $statsDevis = [
                'Brouillon' => 0,
                'En Attente' => 0,
                'Accepté' => 0,
                'Refusé' => 0,
            ];
            try {
                $statsDevis = [
                    'Brouillon' => Devis::where('statut', 'Brouillon')->count(),
                    'En Attente' => Devis::where('statut', 'En Attente')->count(),
                    'Accepté' => Devis::where('statut', 'Accepté')->count(),
                    'Refusé' => Devis::where('statut', 'Refusé')->count(),
                ];
            } catch (\Exception $e) {
                \Log::warning('Erreur stats devis', ['error' => $e->getMessage()]);
            }

            $statsFactures = [
                'Impayée' => 0,
                'Payée' => 0,
                'Annulée' => 0,
            ];
            try {
                $statsFactures = [
                    'Impayée' => Facture::where('statut', 'Impayée')->count(),
                    'Payée' => Facture::where('statut', 'Payée')->count(),
                    'Annulée' => Facture::where('statut', 'Annulée')->count(),
                ];
            } catch (\Exception $e) {
                \Log::warning('Erreur stats factures', ['error' => $e->getMessage()]);
            }

            // Derniers devis
            $derniersDevis = collect([]);
            try {
                $derniersDevis = Devis::with('client')
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
            } catch (\Exception $e) {
                \Log::warning('Erreur derniers devis', ['error' => $e->getMessage()]);
            }

            // Dernières factures
            $dernieresFactures = collect([]);
            try {
                $dernieresFactures = Facture::with('client')
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
            } catch (\Exception $e) {
                \Log::warning('Erreur dernières factures', ['error' => $e->getMessage()]);
            }

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
        } catch (\Exception $e) {
            \Log::error('Erreur QuotationStatsController::dashboard', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('admin.quotations.dashboard', [
                'totalCA' => 0,
                'caPotentiel' => 0,
                'tauxConversion' => 0,
                'facturesEnAttente' => collect([]),
                'statsDevis' => ['Brouillon' => 0, 'En Attente' => 0, 'Accepté' => 0, 'Refusé' => 0],
                'statsFactures' => ['Impayée' => 0, 'Payée' => 0, 'Annulée' => 0],
                'derniersDevis' => collect([]),
                'dernieresFactures' => collect([]),
            ])->with('error', 'Erreur lors du chargement du tableau de bord. Vérifiez que les migrations ont été exécutées : ' . $e->getMessage());
        }
    }
}

