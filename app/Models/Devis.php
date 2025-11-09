<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Devis extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'numero',
        'statut',
        'date_emission',
        'date_validite',
        'description_globale',
        'superficie_totale',
        'prix_final_estime',
        'total_ht',
        'taux_tva',
        'total_ttc',
        'acompte_pourcentage',
        'acompte_montant',
        'reste_a_payer',
        'conditions_particulieres',
        'pdf_path',
        'public_token',
    ];

    protected $casts = [
        'date_emission' => 'date',
        'date_validite' => 'date',
        'prix_final_estime' => 'decimal:2',
        'total_ht' => 'decimal:2',
        'taux_tva' => 'decimal:2',
        'total_ttc' => 'decimal:2',
        'acompte_pourcentage' => 'decimal:2',
        'acompte_montant' => 'decimal:2',
        'reste_a_payer' => 'decimal:2',
    ];

    /**
     * Générer un numéro de devis unique
     */
    public static function generateNumero(): string
    {
        $year = date('Y');
        $prefix = 'DEV-' . $year . '-';
        
        $lastDevis = self::where('numero', 'like', $prefix . '%')
            ->orderBy('numero', 'desc')
            ->first();
        
        if ($lastDevis) {
            $lastNumber = (int) Str::after($lastDevis->numero, $prefix);
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

        static::creating(function ($devis) {
            if (empty($devis->numero)) {
                $devis->numero = self::generateNumero();
            }
            if (empty($devis->date_emission)) {
                $devis->date_emission = now();
            }
            if (empty($devis->public_token)) {
                $devis->public_token = Str::random(32);
            }
        });

        static::saving(function ($devis) {
            // Recalculer les totaux à chaque sauvegarde
            $devis->recalculateTotals();
        });
    }

    /**
     * Recalculer les totaux HT et TTC
     */
    public function recalculateTotals(): void
    {
        $totalHT = $this->lignesDevis()->sum('total_ligne');
        $this->total_ht = $totalHT;
        $this->total_ttc = $totalHT * (1 + ($this->taux_tva / 100));
        
        // Recalculer l'acompte et le reste à payer
        if ($this->acompte_pourcentage && $this->acompte_pourcentage > 0) {
            $this->acompte_montant = $this->total_ttc * ($this->acompte_pourcentage / 100);
            $this->reste_a_payer = $this->total_ttc - $this->acompte_montant;
        } else {
            $this->acompte_montant = 0;
            $this->reste_a_payer = $this->total_ttc;
        }
    }

    /**
     * Relation avec le client
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Relation avec les lignes de devis
     */
    public function lignesDevis(): HasMany
    {
        return $this->hasMany(LigneDevis::class, 'devis_id')->orderBy('ordre');
    }

    /**
     * Relation avec la facture (si le devis a été accepté)
     */
    public function facture(): HasOne
    {
        return $this->hasOne(Facture::class);
    }

    /**
     * Vérifier si le devis peut être validé
     */
    public function canBeValidated(): bool
    {
        return $this->statut === 'En Attente' && $this->lignesDevis()->count() > 0;
    }

    /**
     * Valider le devis et créer la facture
     */
    public function validate(): Facture
    {
        if (!$this->canBeValidated()) {
            throw new \Exception('Le devis ne peut pas être validé');
        }

        $this->update(['statut' => 'Accepté']);

        // Créer la facture
        $facture = Facture::create([
            'devis_id' => $this->id,
            'client_id' => $this->client_id,
            'date_emission' => now(),
            'date_echeance' => now()->addDays(30),
            'prix_total_ht' => $this->total_ht,
            'taux_tva' => $this->taux_tva,
            'prix_total_ttc' => $this->total_ttc,
        ]);

        return $facture;
    }

    /**
     * Obtenir ou générer le token public
     */
    public function getPublicToken(): string
    {
        if (empty($this->public_token)) {
            $this->public_token = Str::random(32);
            $this->save();
        }
        return $this->public_token;
    }

    /**
     * Obtenir l'URL publique du PDF
     */
    public function getPublicPdfUrl(): string
    {
        return route('devis.public.pdf', [
            'id' => $this->id,
            'token' => $this->getPublicToken()
        ]);
    }
}

