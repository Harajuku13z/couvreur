<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LigneDevis extends Model
{
    use HasFactory;

    protected $table = 'ligne_devis';

    protected $fillable = [
        'devis_id',
        'ordre',
        'description',
        'quantite',
        'unite',
        'prix_unitaire',
        'total_ligne',
    ];

    protected $casts = [
        'quantite' => 'decimal:2',
        'prix_unitaire' => 'decimal:2',
        'total_ligne' => 'decimal:2',
    ];

    /**
     * Boot method pour calculer automatiquement le total
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($ligne) {
            $ligne->total_ligne = $ligne->quantite * $ligne->prix_unitaire;
        });
    }

    /**
     * Relation avec le devis
     */
    public function devis(): BelongsTo
    {
        return $this->belongsTo(Devis::class, 'devis_id');
    }
}

