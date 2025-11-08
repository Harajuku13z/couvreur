<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_identifier',
        'property_type',
        'surface',
        'work_types',
        'roof_work_types',
        'facade_work_types',
        'isolation_work_types',
        'ownership_status',
        'gender',
        'first_name',
        'last_name',
        'postal_code',
        'phone',
        'email',
        'status',
        'current_step',
        'form_data',
        'completed_at',
        'abandoned_at',
        'ip_address',
        'city',
        'country',
        'country_code',
        'referrer_url',
        'user_agent',
        'recaptcha_score',
        'tracking_data'
    ];

    protected $casts = [
        'work_types' => 'array',
        'roof_work_types' => 'array',
        'facade_work_types' => 'array',
        'isolation_work_types' => 'array',
        'form_data' => 'array',
        'tracking_data' => 'array',
        'completed_at' => 'datetime',
        'abandoned_at' => 'datetime',
        'recaptcha_score' => 'decimal:2',
    ];

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'COMPLETED');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'IN_PROGRESS');
    }

    public function scopeAbandoned($query)
    {
        return $query->where('status', 'ABANDONED');
    }

    // Utilities
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'COMPLETED',
            'completed_at' => now(),
        ]);
    }

    public function markAsAbandoned(): void
    {
        $this->update([
            'status' => 'ABANDONED',
            'abandoned_at' => now(),
        ]);
    }

    public function getProgressPercentage(): float
    {
        $steps = [
            'propertyType' => 1,
            'surface' => 2,
            'workType' => 3,
            'roofWorkType' => 4,
            'facadeWorkType' => 5,
            'isolationWorkType' => 6,
            'ownershipStatus' => 7,
            'personalInfo' => 8,
            'postalCode' => 9,
            'phone' => 10,
            'email' => 11,
        ];

        $currentStep = $this->current_step;
        $totalSteps = count($steps);
        
        if (!$currentStep || !isset($steps[$currentStep])) {
            return 0.0;
        }

        return round(($steps[$currentStep] / $totalSteps) * 100, 2);
    }

    /**
     * Relation avec le client (via email)
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class, 'email', 'email');
    }

    /**
     * Obtenir le client associé (via email) ou null
     */
    public function getClientAttribute()
    {
        if (!$this->email) {
            return null;
        }
        // Utiliser un cache pour éviter les requêtes répétées
        static $clientCache = [];
        if (!isset($clientCache[$this->email])) {
            $clientCache[$this->email] = Client::where('email', $this->email)->first();
        }
        return $clientCache[$this->email];
    }

    /**
     * Obtenir le nombre de devis pour ce lead
     */
    public function getDevisCountAttribute(): int
    {
        if (!$this->email) {
            return 0;
        }
        $client = $this->client;
        if (!$client) {
            return 0;
        }
        return $client->devis()->count();
    }

    /**
     * Obtenir le nombre de factures payées pour ce lead
     */
    public function getFacturesPayeesCountAttribute(): int
    {
        if (!$this->email) {
            return 0;
        }
        $client = $this->client;
        if (!$client) {
            return 0;
        }
        return $client->factures()->where('statut', 'Payée')->count();
    }

    /**
     * Vérifier si un devis existe pour ce lead
     */
    public function hasDevis(): bool
    {
        return $this->devis_count > 0;
    }

    /**
     * Vérifier si une facture validée existe pour ce lead
     */
    public function hasFactureValidee(): bool
    {
        return $this->factures_payees_count > 0;
    }
}








