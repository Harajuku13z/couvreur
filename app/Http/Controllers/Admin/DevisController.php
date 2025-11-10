<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Devis;
use App\Models\Facture;
use App\Models\LigneDevis;
use App\Services\GroqQuotationService;
use App\Services\PdfService;
use App\Mail\DevisSent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;

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
        try {
            $query = Devis::with(['client', 'facture'])->orderBy('created_at', 'desc');

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
        } catch (\Exception $e) {
            \Log::error('Erreur DevisController::index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('admin.devis.index', ['devis' => collect([])->paginate(20)])
                ->with('error', 'Erreur lors du chargement des devis. Vérifiez que les migrations ont été exécutées : ' . $e->getMessage());
        }
    }

    /**
     * Formulaire de création
     */
    public function create(Request $request)
    {
        try {
            $clients = Client::orderBy('nom')->get();
            $selectedClientId = $request->get('client_id');
            $selectedClient = null;
            
            if ($selectedClientId) {
                $selectedClient = Client::find($selectedClientId);
            }
            
            return view('admin.devis.create', compact('clients', 'selectedClient', 'selectedClientId'));
        } catch (\Exception $e) {
            \Log::error('Erreur DevisController::create', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return view('admin.devis.create', ['clients' => collect([]), 'selectedClient' => null, 'selectedClientId' => null])
                ->with('error', 'Erreur lors du chargement. Vérifiez que les migrations ont été exécutées : ' . $e->getMessage());
        }
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
                'acompte_pourcentage' => $request->acompte_pourcentage,
                'acompte_montant' => $request->acompte_montant,
                'reste_a_payer' => $request->reste_a_payer,
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

            // Si le statut est "Validé" ou "Accepté", créer automatiquement la facture
            if (in_array($devis->statut, ['Validé', 'Accepté']) && !$devis->facture) {
                try {
                    $facture = Facture::create([
                        'devis_id' => $devis->id,
                        'client_id' => $devis->client_id,
                        'date_emission' => now(),
                        'date_echeance' => now()->addDays(30),
                        'prix_total_ht' => $devis->total_ht,
                        'taux_tva' => $devis->taux_tva,
                        'prix_total_ttc' => $devis->total_ttc,
                        'statut' => 'En Attente',
                    ]);
                    
                    Log::info('Facture créée automatiquement pour devis validé', [
                        'devis_id' => $devis->id,
                        'facture_id' => $facture->id
                    ]);
                } catch (\Exception $e) {
                    Log::error('Erreur création automatique facture', [
                        'devis_id' => $devis->id,
                        'error' => $e->getMessage()
                    ]);
                    // On continue même si la facture n'a pas pu être créée
                }
            }

            \DB::commit();

            $successMessage = 'Devis créé avec succès.';
            if (in_array($devis->statut, ['Validé', 'Accepté']) && $devis->fresh()->facture) {
                $successMessage .= ' Facture créée automatiquement.';
            }

            return redirect()->route('admin.devis.show', $devis->id)
                ->with('success', $successMessage);
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
        try {
            $devis = Devis::with(['client', 'lignesDevis'])->findOrFail($id);
            return view('admin.devis.show', compact('devis'));
        } catch (\Exception $e) {
            \Log::error('Erreur DevisController::show', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.devis.index')
                ->with('error', 'Erreur lors du chargement. Vérifiez que les migrations ont été exécutées : ' . $e->getMessage());
        }
    }

    /**
     * Générer et voir le PDF du devis
     */
    /**
     * Accès public au PDF avec token
     */
    public function publicPdf($id, $token)
    {
        try {
            $devis = Devis::with(['client', 'lignesDevis'])->findOrFail($id);
            
            // Vérifier le token
            if (empty($devis->public_token) || $devis->public_token !== $token) {
                abort(403, 'Token invalide ou accès non autorisé');
            }
            
            // Vérifications préliminaires
            if (!$devis->client) {
                throw new \Exception('Le client associé au devis n\'existe pas.');
            }
            
            if ($devis->lignesDevis->isEmpty()) {
                throw new \Exception('Le devis n\'a pas de lignes. Impossible de générer le PDF.');
            }
            
            // Utiliser la même logique que la méthode pdf() mais sans authentification
            return $this->generatePdfResponse($devis);
            
        } catch (\Exception $e) {
            Log::error('Erreur génération PDF public', [
                'error' => $e->getMessage(),
                'devis_id' => $id,
                'token' => substr($token, 0, 8) . '...'
            ]);
            
            return response()->view('errors.500', [
                'message' => 'Impossible de générer le PDF : ' . $e->getMessage()
            ], 500);
        }
    }

    public function pdf($id)
    {
        try {
            $devis = Devis::with(['client', 'lignesDevis'])->findOrFail($id);
            
            // Vérifications préliminaires
            if (!$devis->client) {
                throw new \Exception('Le client associé au devis n\'existe pas.');
            }
            
            if ($devis->lignesDevis->isEmpty()) {
                throw new \Exception('Le devis n\'a pas de lignes. Impossible de générer le PDF.');
            }
            
            return $this->generatePdfResponse($devis);
            
        } catch (\Exception $e) {
            Log::error('Erreur génération PDF', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'devis_id' => $id,
            ]);
            
            return response()->view('errors.500', [
                'message' => 'Impossible de générer le PDF : ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Générer la réponse PDF (méthode partagée)
     */
    private function generatePdfResponse($devis)
    {
        // Essayer d'abord de récupérer le PDF existant
        if ($devis->pdf_path && Storage::disk('local')->exists($devis->pdf_path)) {
            $pdfPath = Storage::disk('local')->path($devis->pdf_path);
            if (file_exists($pdfPath)) {
                return response()->file($pdfPath, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="Devis_' . $devis->numero . '.pdf"',
                ]);
            }
        }
        
        // Si le PDF n'existe pas, le générer directement sans le sauvegarder
        try {
            $devis->load(['client', 'lignesDevis']);
            $companySettings = $this->getCompanySettings();
            
            // Générer le HTML depuis la vue
            $html = view('pdfs.devis', [
                'devis' => $devis,
                'companySettings' => $companySettings,
            ])->render();

            // Vérifier que DomPDF est disponible
            if (!class_exists('Dompdf\Dompdf')) {
                throw new \Exception('Le package DomPDF n\'est pas installé. Exécutez sur le serveur: composer require dompdf/dompdf');
            }

            // Créer une instance DomPDF directement
            $options = new \Dompdf\Options();
            $options->set('isHtml5ParserEnabled', true);
            $options->set('isRemoteEnabled', false);
            $options->set('enableLocalFileAccess', true);
            $options->set('defaultFont', 'DejaVu Sans');
            
            $dompdf = new \Dompdf\Dompdf($options);
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            return response($dompdf->output(), 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="Devis_' . $devis->numero . '.pdf"');
            
        } catch (\Exception $pdfError) {
            Log::error('Erreur génération PDF directe', [
                'error' => $pdfError->getMessage(),
                'trace' => $pdfError->getTraceAsString(),
                'devis_id' => $devis->id,
            ]);
            
            // Essayer avec le service
            $pdfService = new PdfService();
            $pdfService->generateDevisPdf($devis);
            $devis->refresh();
            
            if ($devis->pdf_path && Storage::disk('local')->exists($devis->pdf_path)) {
                $pdfPath = Storage::disk('local')->path($devis->pdf_path);
                return response()->file($pdfPath, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="Devis_' . $devis->numero . '.pdf"',
                ]);
            }
            
            throw $pdfError;
        }
    }
    
    /**
     * Obtenir les paramètres de l'entreprise (méthode helper)
     */
    private function getCompanySettings(): array
    {
        $logoPath = \App\Models\Setting::get('company_logo');
        $logoBase64 = null;
        
        // Convertir le logo en base64 pour l'inclure dans le PDF
        if ($logoPath) {
            $fullPath = public_path($logoPath);
            if (file_exists($fullPath)) {
                $imageData = file_get_contents($fullPath);
                $imageInfo = getimagesize($fullPath);
                $mimeType = $imageInfo['mime'] ?? 'image/png';
                $logoBase64 = 'data:' . $mimeType . ';base64,' . base64_encode($imageData);
            }
        }
        
        return [
            'name' => \App\Models\Setting::get('company_name', 'Votre Entreprise'),
            'address' => \App\Models\Setting::get('company_address', ''),
            'postal_code' => \App\Models\Setting::get('company_postal_code', ''),
            'city' => \App\Models\Setting::get('company_city', ''),
            'phone' => \App\Models\Setting::get('company_phone', ''),
            'email' => \App\Models\Setting::get('company_email', ''),
            'siret' => \App\Models\Setting::get('company_siret', ''),
            'rcs' => \App\Models\Setting::get('company_rcs', ''),
            'capital' => \App\Models\Setting::get('company_capital', ''),
            'tva' => \App\Models\Setting::get('company_tva', ''),
            'director' => \App\Models\Setting::get('company_director', ''),
            'hosting_provider' => \App\Models\Setting::get('hosting_provider', ''),
            'rib' => \App\Models\Setting::get('company_rib', ''),
            'logo_base64' => $logoBase64,
            'primary_color' => \App\Models\Setting::get('primary_color', '#3b82f6'),
            'secondary_color' => \App\Models\Setting::get('secondary_color', '#10b981'),
        ];
    }

    /**
     * Télécharger le PDF du devis
     */
    public function downloadPdf($id)
    {
        try {
            $devis = Devis::with(['client', 'lignesDevis'])->findOrFail($id);
            $pdfService = new PdfService();
            
            // Générer le PDF s'il n'existe pas
            if (!$devis->pdf_path || !Storage::disk('local')->exists($devis->pdf_path)) {
                try {
                    $pdfService->generateDevisPdf($devis);
                    $devis->refresh();
                } catch (\Exception $genError) {
                    Log::error('Erreur génération PDF lors du téléchargement', [
                        'error' => $genError->getMessage(),
                        'trace' => $genError->getTraceAsString(),
                        'devis_id' => $id,
                    ]);
                    throw new \Exception('Impossible de générer le PDF : ' . $genError->getMessage());
                }
            }
            
            // Vérifier que le fichier existe
            if (!Storage::disk('local')->exists($devis->pdf_path)) {
                throw new \Exception('Le fichier PDF n\'a pas pu être généré ou n\'existe pas');
            }
            
            $filePath = Storage::disk('local')->path($devis->pdf_path);
            
            // Vérifier que le fichier existe physiquement
            if (!file_exists($filePath)) {
                throw new \Exception('Le fichier PDF n\'existe pas sur le serveur : ' . $filePath);
            }
            
            // Essayer d'abord avec Storage::download
            try {
                return Storage::disk('local')->download($devis->pdf_path, 'Devis_' . $devis->numero . '.pdf');
            } catch (\Exception $downloadError) {
                // Fallback : utiliser response()->download
                Log::warning('Storage::download a échoué, utilisation de response()->download', [
                    'error' => $downloadError->getMessage(),
                ]);
                
                return response()->download($filePath, 'Devis_' . $devis->numero . '.pdf', [
                    'Content-Type' => 'application/pdf',
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Erreur téléchargement PDF devis', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'devis_id' => $id,
            ]);
            
            return redirect()->route('admin.devis.show', $id)
                ->with('error', 'Erreur lors du téléchargement du PDF : ' . $e->getMessage());
        }
    }

    /**
     * Envoyer le devis par email
     */
    public function sendEmail($id)
    {
        try {
            $devis = Devis::with(['client', 'lignesDevis'])->findOrFail($id);
            
            if (!$devis->client->email) {
                return redirect()->route('admin.devis.show', $id)
                    ->with('error', 'Le client n\'a pas d\'adresse email.');
            }
            
            $pdfService = new PdfService();
            
            // Générer le PDF s'il n'existe pas
            if (!$devis->pdf_path || !Storage::disk('local')->exists($devis->pdf_path)) {
                $pdfService->generateDevisPdf($devis);
                $devis->refresh();
            }
            
            Mail::to($devis->client->email)->send(new DevisSent($devis));
            
            return redirect()->route('admin.devis.show', $id)
                ->with('success', 'Devis envoyé par email avec succès à ' . $devis->client->email);
        } catch (\Exception $e) {
            Log::error('Erreur envoi email devis', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'devis_id' => $id,
            ]);
            
            return redirect()->route('admin.devis.show', $id)
                ->with('error', 'Erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
        }
    }

    /**
     * Formulaire d'édition
     */
    public function edit($id)
    {
        try {
            $devis = Devis::with(['client', 'lignesDevis'])->findOrFail($id);
            $clients = Client::orderBy('nom')->get();
            return view('admin.devis.edit', compact('devis', 'clients'));
        } catch (\Exception $e) {
            \Log::error('Erreur DevisController::edit', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->route('admin.devis.index')
                ->with('error', 'Erreur lors du chargement. Vérifiez que les migrations ont été exécutées : ' . $e->getMessage());
        }
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

            // Sauvegarder l'ancien statut avant la mise à jour
            $oldStatut = $devis->statut;

            $devis->update([
                'client_id' => $request->client_id,
                'statut' => $request->statut,
                'date_validite' => $request->date_validite,
                'description_globale' => $request->description_globale,
                'taux_tva' => $request->taux_tva ?? 20.00,
                'acompte_pourcentage' => $request->acompte_pourcentage,
                'acompte_montant' => $request->acompte_montant,
                'reste_a_payer' => $request->reste_a_payer,
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

            // Vérifier si le statut a changé pour "Validé" ou "Accepté"
            $newStatut = $devis->statut;
            
            // Si le statut passe à "Validé" ou "Accepté" et qu'il n'y a pas encore de facture, en créer une
            if (in_array($newStatut, ['Validé', 'Accepté']) && 
                !in_array($oldStatut, ['Validé', 'Accepté']) && 
                !$devis->facture) {
                try {
                    $facture = Facture::create([
                        'devis_id' => $devis->id,
                        'client_id' => $devis->client_id,
                        'date_emission' => now(),
                        'date_echeance' => now()->addDays(30),
                        'prix_total_ht' => $devis->total_ht,
                        'taux_tva' => $devis->taux_tva,
                        'prix_total_ttc' => $devis->total_ttc,
                        'statut' => 'En Attente',
                    ]);
                    
                    Log::info('Facture créée automatiquement pour devis validé', [
                        'devis_id' => $devis->id,
                        'facture_id' => $facture->id,
                        'old_statut' => $oldStatut,
                        'new_statut' => $newStatut
                    ]);
                } catch (\Exception $e) {
                    Log::error('Erreur création automatique facture', [
                        'devis_id' => $devis->id,
                        'error' => $e->getMessage()
                    ]);
                    // On continue même si la facture n'a pas pu être créée
                }
            }

            // Supprimer l'ancien PDF s'il existe pour forcer la régénération
            if ($devis->pdf_path && Storage::disk('local')->exists($devis->pdf_path)) {
                Storage::disk('local')->delete($devis->pdf_path);
            }
            $devis->pdf_path = null;
            $devis->save();

            // Régénérer le PDF avec les nouvelles données
            try {
                $pdfService = new \App\Services\PdfService();
                $pdfService->generateDevisPdf($devis);
            } catch (\Exception $pdfError) {
                Log::warning('Erreur régénération PDF après mise à jour devis', [
                    'devis_id' => $devis->id,
                    'error' => $pdfError->getMessage(),
                ]);
                // On continue même si le PDF n'a pas pu être généré
            }

            \DB::commit();

            $successMessage = 'Devis mis à jour avec succès. Le PDF a été régénéré.';
            if (in_array($newStatut, ['Validé', 'Accepté']) && 
                !in_array($oldStatut, ['Validé', 'Accepté']) && 
                $devis->fresh()->facture) {
                $successMessage .= ' Facture créée automatiquement.';
            }

            return redirect()->route('admin.devis.show', $devis->id)
                ->with('success', $successMessage);
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
    public function destroy(Request $request, $id)
    {
        $devis = Devis::findOrFail($id);
        
        // Déterminer si un mot de passe est requis
        $requiresPassword = in_array($devis->statut, ['Accepté', 'En Attente']) || $devis->facture;
        
        if ($requiresPassword) {
            $request->validate([
                'password' => 'required|string',
            ]);
            
            $correctPassword = 'elizo';
            
            if ($request->password !== $correctPassword) {
                return back()->with('error', 'Mot de passe incorrect');
            }
        }

        try {
            // Supprimer les lignes de devis
            $devis->lignesDevis()->delete();
            
            // Supprimer le PDF si existe
            if ($devis->pdf_path && Storage::disk('local')->exists($devis->pdf_path)) {
                Storage::disk('local')->delete($devis->pdf_path);
            }
            
            $devis->delete();
            
            \Log::info('Devis supprimé', [
                'devis_id' => $id,
                'statut' => $devis->statut,
                'admin' => session()->get('admin_username', 'unknown'),
            ]);
            
            return redirect()->route('admin.devis.index')
                ->with('success', 'Devis supprimé avec succès');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression du devis', [
                'devis_id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            return back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }
}

