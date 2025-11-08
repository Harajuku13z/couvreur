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
        'notes',
        'pdf_path',
    ];

    protected $casts = [
        'date_emission' => 'date',
        'date_echeance' => 'date',
        'date_paiement' => 'date',
        'prix_total_ht' => 'decimal:2',
        'taux_tva' => 'decimal:2',
        'prix_total_ttc' => 'decimal:2',
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
        ]);
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

