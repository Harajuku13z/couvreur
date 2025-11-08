<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Devis;
use App\Models\LigneDevis;
use App\Services\GroqQuotationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DevisController extends Controller
{
    protected $quotationService;

    public function __construct(GroqQuotationService $quotationService)
    {
        $this->quotationService = $quotationService;
    }

    /**
     * Liste des devis
     */
    public function index(Request $request)
    {
        $query = Devis::with('client')->orderBy('created_at', 'desc');

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

        $devis = $query->paginate(20);

        return view('admin.devis.index', compact('devis'));
    }

    /**
     * Formulaire de création
     */
    public function create()
    {
        $clients = Client::orderBy('nom')->get();
        return view('admin.devis.create', compact('clients'));
    }

    /**
     * Générer les lignes avec l'IA
     */
    public function generateLines(Request $request)
    {
        $request->validate([
            'description_globale' => 'required|string',
            'superficie_totale' => 'nullable|string',
            'prix_final_estime' => 'nullable|numeric|min:0',
        ]);

        try {
            $lines = $this->quotationService->generateQuotationLines(
                $request->description_globale,
                $request->superficie_totale,
                $request->prix_final_estime
            );

            return response()->json([
                'success' => true,
                'lines' => $lines,
            ]);
        } catch (\Exception $e) {
            Log::error('Erreur génération lignes devis', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sauvegarder le devis
     */
    public function store(Request $request)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'description_globale' => 'nullable|string',
            'superficie_totale' => 'nullable|string',
            'prix_final_estime' => 'nullable|numeric|min:0',
            'date_validite' => 'nullable|date',
            'taux_tva' => 'nullable|numeric|min:0|max:100',
            'conditions_particulieres' => 'nullable|string',
            'lignes' => 'required|array|min:1',
            'lignes.*.description' => 'required|string',
            'lignes.*.quantite' => 'required|numeric|min:0.01',
            'lignes.*.unite' => 'required|string',
            'lignes.*.prix_unitaire' => 'required|numeric|min:0',
        ]);

        try {
            \DB::beginTransaction();

            // Créer ou mettre à jour le devis
            $devis = Devis::create([
                'client_id' => $request->client_id,
                'statut' => $request->statut ?? 'Brouillon',
                'date_emission' => now(),
                'date_validite' => $request->date_validite,
                'description_globale' => $request->description_globale,
                'superficie_totale' => $request->superficie_totale,
                'prix_final_estime' => $request->prix_final_estime,
                'taux_tva' => $request->taux_tva ?? 20.00,
                'conditions_particulieres' => $request->conditions_particulieres,
            ]);

            // Créer les lignes
            foreach ($request->lignes as $index => $ligne) {
                LigneDevis::create([
                    'devis_id' => $devis->id,
                    'ordre' => $index + 1,
                    'description' => $ligne['description'],
                    'quantite' => $ligne['quantite'],
                    'unite' => $ligne['unite'],
                    'prix_unitaire' => $ligne['prix_unitaire'],
                ]);
            }

            // Recalculer les totaux
            $devis->recalculateTotals();
            $devis->save();

            \DB::commit();

            return redirect()->route('admin.devis.show', $devis->id)
                ->with('success', 'Devis créé avec succès');
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Erreur création devis', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->withInput()
                ->with('error', 'Erreur lors de la création du devis : ' . $e->getMessage());
        }
    }

    /**
     * Afficher un devis
     */
    public function show($id)
    {
        $devis = Devis::with(['client', 'lignesDevis'])->findOrFail($id);
        return view('admin.devis.show', compact('devis'));
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        $devis = Devis::with(['client', 'lignesDevis'])->findOrFail($id);
        $clients = Client::orderBy('nom')->get();
        return view('admin.devis.edit', compact('devis', 'clients'));
    }

    /**
     * Mettre à jour le devis
     */
    public function update(Request $request, $id)
    {
        $devis = Devis::findOrFail($id);

        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'statut' => 'required|in:Brouillon,En Attente,Accepté,Refusé',
            'description_globale' => 'nullable|string',
            'date_validite' => 'nullable|date',
            'taux_tva' => 'nullable|numeric|min:0|max:100',
            'conditions_particulieres' => 'nullable|string',
            'lignes' => 'required|array|min:1',
            'lignes.*.description' => 'required|string',
            'lignes.*.quantite' => 'required|numeric|min:0.01',
            'lignes.*.unite' => 'required|string',
            'lignes.*.prix_unitaire' => 'required|numeric|min:0',
        ]);

        try {
            \DB::beginTransaction();

            $devis->update([
                'client_id' => $request->client_id,
                'statut' => $request->statut,
                'date_validite' => $request->date_validite,
                'description_globale' => $request->description_globale,
                'taux_tva' => $request->taux_tva ?? 20.00,
                'conditions_particulieres' => $request->conditions_particulieres,
            ]);

            // Supprimer les anciennes lignes
            $devis->lignesDevis()->delete();

            // Créer les nouvelles lignes
            foreach ($request->lignes as $index => $ligne) {
                LigneDevis::create([
                    'devis_id' => $devis->id,
                    'ordre' => $index + 1,
                    'description' => $ligne['description'],
                    'quantite' => $ligne['quantite'],
                    'unite' => $ligne['unite'],
                    'prix_unitaire' => $ligne['prix_unitaire'],
                ]);
            }

            // Recalculer les totaux
            $devis->recalculateTotals();
            $devis->save();

            \DB::commit();

            return redirect()->route('admin.devis.show', $devis->id)
                ->with('success', 'Devis mis à jour avec succès');
        } catch (\Exception $e) {
            \DB::rollBack();
            Log::error('Erreur mise à jour devis', [
                'error' => $e->getMessage(),
            ]);

            return back()->withInput()
                ->with('error', 'Erreur lors de la mise à jour du devis');
        }
    }

    /**
     * Valider un devis (créer la facture)
     */
    public function validate($id)
    {
        $devis = Devis::findOrFail($id);

        try {
            $facture = $devis->validate();

            return redirect()->route('admin.factures.show', $facture->id)
                ->with('success', 'Devis validé et facture créée avec succès');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Supprimer un devis
     */
    public function destroy($id)
    {
        $devis = Devis::findOrFail($id);

        if ($devis->statut === 'Accepté') {
            return back()->with('error', 'Impossible de supprimer un devis accepté');
        }

        $devis->delete();

        return redirect()->route('admin.devis.index')
            ->with('success', 'Devis supprimé avec succès');
    }
}

