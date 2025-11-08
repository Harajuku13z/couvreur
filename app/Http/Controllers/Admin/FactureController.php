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
    }

    /**
     * Afficher une facture
     */
    public function show($id)
    {
        $facture = Facture::with(['client', 'devis'])->findOrFail($id);
        return view('admin.factures.show', compact('facture'));
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

