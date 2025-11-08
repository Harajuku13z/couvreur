<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'email',
        'telephone',
        'adresse',
        'code_postal',
        'ville',
        'pays',
        'notes',
    ];

    /**
     * Relation avec les devis
     */
    public function devis(): HasMany
    {
        return $this->hasMany(Devis::class);
    }

    /**
     * Relation avec les factures
     */
    public function factures(): HasMany
    {
        return $this->hasMany(Facture::class);
    }

    /**
     * Nom complet du client
     */
    public function getNomCompletAttribute(): string
    {
        return trim(($this->prenom ?? '') . ' ' . $this->nom);
    }

    /**
     * Adresse complète
     */
    public function getAdresseCompleteAttribute(): string
    {
        $parts = array_filter([
            $this->adresse,
            $this->code_postal,
            $this->ville,
            $this->pays,
        ]);

        return implode(', ', $parts);
    }
}

