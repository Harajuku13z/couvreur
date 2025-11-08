<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facture;
use App\Services\PdfService;
use App\Mail\FactureSent;
use App\Mail\FactureReminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

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
     * Voir le PDF de la facture
     */
    public function pdf($id)
    {
        try {
            $facture = Facture::with(['client', 'devis'])->findOrFail($id);
            
            // Vérifications préliminaires
            if (!$facture->client) {
                throw new \Exception('Le client associé à la facture n\'existe pas.');
            }
            
            // Essayer d'abord de récupérer le PDF existant
            if ($facture->pdf_path && Storage::disk('local')->exists($facture->pdf_path)) {
                $pdfPath = Storage::disk('local')->path($facture->pdf_path);
                if (file_exists($pdfPath)) {
                    return response()->file($pdfPath, [
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="Facture_' . $facture->numero . '.pdf"',
                    ]);
                }
            }
            
            // Si le PDF n'existe pas, le générer
            $pdfService = new PdfService();
            $pdfService->generateFacturePdf($facture);
            $facture->refresh();
            
            if ($facture->pdf_path && Storage::disk('local')->exists($facture->pdf_path)) {
                $pdfPath = Storage::disk('local')->path($facture->pdf_path);
                return response()->file($pdfPath, [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="Facture_' . $facture->numero . '.pdf"',
                ]);
            }
            
            throw new \Exception('Impossible de générer le PDF');
            
        } catch (\Exception $e) {
            Log::error('Erreur génération PDF facture', [
                'error' => $e->getMessage(),
                'facture_id' => $id,
            ]);
            
            return redirect()->route('admin.factures.show', $id)
                ->with('error', 'Erreur lors de la génération du PDF : ' . $e->getMessage());
        }
    }
    
    /**
     * Télécharger le PDF de la facture
     */
    public function downloadPdf($id)
    {
        try {
            $facture = Facture::findOrFail($id);
            
            // Générer le PDF s'il n'existe pas
            if (!$facture->pdf_path || !Storage::disk('local')->exists($facture->pdf_path)) {
                $pdfService = new PdfService();
                $pdfService->generateFacturePdf($facture);
                $facture->refresh();
            }
            
            if (!Storage::disk('local')->exists($facture->pdf_path)) {
                throw new \Exception('Le fichier PDF n\'existe pas');
            }
            
            $filePath = Storage::disk('local')->path($facture->pdf_path);
            
            if (file_exists($filePath)) {
                return response()->download($filePath, 'Facture_' . $facture->numero . '.pdf');
            }
            
            return Storage::disk('local')->download($facture->pdf_path, 'Facture_' . $facture->numero . '.pdf');
            
        } catch (\Exception $e) {
            Log::error('Erreur téléchargement PDF facture', [
                'error' => $e->getMessage(),
                'facture_id' => $id,
            ]);
            
            return redirect()->route('admin.factures.show', $id)
                ->with('error', 'Erreur lors du téléchargement du PDF : ' . $e->getMessage());
        }
    }
    
    /**
     * Envoyer la facture par email
     */
    public function sendEmail($id)
    {
        try {
            $facture = Facture::with(['client'])->findOrFail($id);
            
            if (!$facture->client->email) {
                return back()->with('error', 'Le client n\'a pas d\'adresse email');
            }
            
            // Générer le PDF s'il n'existe pas
            if (!$facture->pdf_path || !Storage::disk('local')->exists($facture->pdf_path)) {
                $pdfService = new PdfService();
                $pdfService->generateFacturePdf($facture);
                $facture->refresh();
            }
            
            Mail::to($facture->client->email)->send(new FactureSent($facture));
            
            return back()->with('success', 'Facture envoyée par email avec succès');
            
        } catch (\Exception $e) {
            Log::error('Erreur envoi email facture', [
                'error' => $e->getMessage(),
                'facture_id' => $id,
            ]);
            
            return back()->with('error', 'Erreur lors de l\'envoi de l\'email : ' . $e->getMessage());
        }
    }
    
    /**
     * Envoyer une relance pour facture impayée
     */
    public function sendReminder($id)
    {
        try {
            $facture = Facture::with(['client'])->findOrFail($id);
            
            if (!$facture->client->email) {
                return back()->with('error', 'Le client n\'a pas d\'adresse email');
            }
            
            if ($facture->statut === 'Payée') {
                return back()->with('error', 'Impossible d\'envoyer une relance pour une facture payée');
            }
            
            // Générer le PDF s'il n'existe pas
            if (!$facture->pdf_path || !Storage::disk('local')->exists($facture->pdf_path)) {
                $pdfService = new PdfService();
                $pdfService->generateFacturePdf($facture);
                $facture->refresh();
            }
            
            // Envoyer la relance
            Mail::to($facture->client->email)->send(new FactureReminder($facture));
            
            // Enregistrer la relance
            $facture->sendReminder();
            
            return back()->with('success', 'Relance envoyée avec succès');
            
        } catch (\Exception $e) {
            Log::error('Erreur envoi relance facture', [
                'error' => $e->getMessage(),
                'facture_id' => $id,
            ]);
            
            return back()->with('error', 'Erreur lors de l\'envoi de la relance : ' . $e->getMessage());
        }
    }
    
    /**
     * Enregistrer un paiement partiel
     */
    public function recordPayment(Request $request, $id)
    {
        $request->validate([
            'montant' => 'required|numeric|min:0.01',
        ]);
        
        try {
            $facture = Facture::findOrFail($id);
            
            if ($facture->statut === 'Payée') {
                return back()->with('error', 'Cette facture est déjà payée');
            }
            
            $facture->recordPayment($request->montant);
            
            return back()->with('success', 'Paiement enregistré avec succès');
            
        } catch (\Exception $e) {
            Log::error('Erreur enregistrement paiement', [
                'error' => $e->getMessage(),
                'facture_id' => $id,
            ]);
            
            return back()->with('error', 'Erreur lors de l\'enregistrement du paiement : ' . $e->getMessage());
        }
    }

    /**
     * Supprimer une facture
     */
    public function destroy(Request $request, $id)
    {
        $facture = Facture::findOrFail($id);
        
        // Déterminer si un mot de passe est requis
        $requiresPassword = in_array($facture->statut, ['Payée', 'Partiellement payée']);
        
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
            // Supprimer le PDF si existe
            if ($facture->pdf_path && Storage::disk('local')->exists($facture->pdf_path)) {
                Storage::disk('local')->delete($facture->pdf_path);
            }
            
            $facture->delete();
            
            \Log::info('Facture supprimée', [
                'facture_id' => $id,
                'statut' => $facture->statut,
                'admin' => session()->get('admin_username', 'unknown'),
            ]);
            
            return redirect()->route('admin.factures.index')
                ->with('success', 'Facture supprimée avec succès');
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression de la facture', [
                'facture_id' => $id,
                'error' => $e->getMessage(),
            ]);
            
            return back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }
}

