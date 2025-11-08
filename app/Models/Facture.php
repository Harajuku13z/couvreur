<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Facture extends Model
{
    use HasFactory;

    protected $fillable = [
        'devis_id',
        'client_id',
        'numero',
        'statut',
        'date_emission',
        'date_echeance',
        'date_paiement',
        'prix_total_ht',
        'taux_tva',
        'prix_total_ttc',
        'montant_paye',
        'nombre_relances',
        'derniere_relance',
        'notes',
        'pdf_path',
    ];

    protected $casts = [
        'date_emission' => 'date',
        'date_echeance' => 'date',
        'date_paiement' => 'date',
        'derniere_relance' => 'date',
        'prix_total_ht' => 'decimal:2',
        'taux_tva' => 'decimal:2',
        'prix_total_ttc' => 'decimal:2',
        'montant_paye' => 'decimal:2',
    ];

    /**
     * Générer un numéro de facture unique
     */
    public static function generateNumero(): string
    {
        $year = date('Y');
        $prefix = 'FAC-' . $year . '-';
        
        $lastFacture = self::where('numero', 'like', $prefix . '%')
            ->orderBy('numero', 'desc')
            ->first();
        
        if ($lastFacture) {
            $lastNumber = (int) Str::after($lastFacture->numero, $prefix);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }
        
        return $prefix . str_pad($newNumber, 5, '0', STR_PAD_LEFT);
    }

    /**
     * Boot method pour générer automatiquement le numéro
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($facture) {
            if (empty($facture->numero)) {
                $facture->numero = self::generateNumero();
            }
            if (empty($facture->date_emission)) {
                $facture->date_emission = now();
            }
        });
    }

    /**
     * Relation avec le devis
     */
    public function devis(): BelongsTo
    {
        return $this->belongsTo(Devis::class);
    }

    /**
     * Relation avec le client
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Marquer la facture comme payée
     */
    public function markAsPaid(): void
    {
        $this->update([
            'statut' => 'Payée',
            'date_paiement' => now(),
            'montant_paye' => $this->prix_total_ttc,
        ]);
    }
    
    /**
     * Enregistrer un paiement partiel
     */
    public function recordPayment(float $montant): void
    {
        $this->montant_paye = ($this->montant_paye ?? 0) + $montant;
        
        // Arrondir pour éviter les problèmes de comparaison de float
        $montantPaye = round($this->montant_paye, 2);
        $prixTotalTTC = round($this->prix_total_ttc, 2);
        
        if ($montantPaye >= $prixTotalTTC) {
            $this->statut = 'Payée';
            $this->date_paiement = now();
            $this->montant_paye = $prixTotalTTC; // S'assurer que le montant payé = total
        } else {
            $this->statut = 'Partiellement payée';
        }
        
        $this->save();
        
        // Régénérer le PDF après mise à jour
        try {
            if ($this->pdf_path && \Illuminate\Support\Facades\Storage::disk('local')->exists($this->pdf_path)) {
                \Illuminate\Support\Facades\Storage::disk('local')->delete($this->pdf_path);
            }
            $this->pdf_path = null;
            $this->save();
            
            $pdfService = new \App\Services\PdfService();
            $pdfService->generateFacturePdf($this);
        } catch (\Exception $pdfError) {
            \Illuminate\Support\Facades\Log::warning('Erreur régénération PDF après paiement', [
                'facture_id' => $this->id,
                'error' => $pdfError->getMessage(),
            ]);
            // On continue même si le PDF n'a pas pu être généré
        }
    }
    
    /**
     * Envoyer une relance
     */
    public function sendReminder(): void
    {
        $this->increment('nombre_relances');
        $this->derniere_relance = now();
        $this->save();
    }
    
    /**
     * Obtenir le montant restant à payer
     */
    public function getMontantRestantAttribute(): float
    {
        return max(0, $this->prix_total_ttc - ($this->montant_paye ?? 0));
    }

    /**
     * Vérifier si la facture est en retard
     */
    public function isOverdue(): bool
    {
        return $this->statut === 'Impayée' 
            && $this->date_echeance 
            && $this->date_echeance->isPast();
    }
}

