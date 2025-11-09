<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SeoAutomation extends Model
{
    protected $fillable = [
        'city_id',
        'keyword',
        'status',
        'article_id',
        'article_url',
        'metadata',
        'error_message',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Relation avec la ville
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Scope pour filtrer par statut
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope pour les automations récentes
     */
    public function scopeRecent($query, int $days = 14)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
