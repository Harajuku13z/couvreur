<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facture;
use Illuminate\Http\Request;

class FactureController extends Controller
{
    /**
     * Liste des factures
     */
    public function index(Request $request)
    {
        try {
            $query = Facture::with(['client', 'devis'])->orderBy('created_at', 'desc');

            // Filtres
            if ($request->filled('statut')) {
                $query->where('statut', $request->statut);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('numero', 'like', "%{$search}%")
                      ->orWhereHas('client', function($q) use ($search) {
                          $q->where('nom', 'like', "%{$search}%")
                            ->orWhere('prenom', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            }

            $factures = $query->paginate(20);

            return view('admin.factures.index', compact('factures'));
        } catch (\Exception $e) {
            \Log::error('Erreur FactureController::index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('admin.factures.index', ['factures' => collect([])->paginate(20)])
                ->with('error', 'Erreur lors du chargement des factures. Vérifiez que les migrations ont été exécutées : ' . $e->getMessage());
        }
    }

    /**
     * Afficher une facture
     */
    public function show($id)
    {
        try {
            $facture = Facture::with(['client', 'devis'])->findOrFail($id);
            return view('admin.factures.show', compact('facture'));
        } catch (\Exception $e) {
            \Log::error('Erreur FactureController::show', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.factures.index')
                ->with('error', 'Erreur lors du chargement. Vérifiez que les migrations ont été exécutées : ' . $e->getMessage());
        }
    }

    /**
     * Marquer comme payée
     */
    public function markAsPaid($id)
    {
        $facture = Facture::findOrFail($id);
        $facture->markAsPaid();

        return back()->with('success', 'Facture marquée comme payée');
    }

    /**
     * Supprimer une facture
     */
    public function destroy($id)
    {
        $facture = Facture::findOrFail($id);

        if ($facture->statut === 'Payée') {
            return back()->with('error', 'Impossible de supprimer une facture payée');
        }

        $facture->delete();

        return redirect()->route('admin.factures.index')
            ->with('success', 'Facture supprimée avec succès');
    }
}

