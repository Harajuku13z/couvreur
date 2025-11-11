<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UrlIndexationStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'url',
        'indexed',
        'coverage_state',
        'indexing_state',
        'page_fetch_state',
        'verdict',
        'last_crawl_time',
        'last_submission_time',
        'last_verification_time',
        'details',
        'errors',
        'warnings',
        'mobile_usable',
        'submission_count',
    ];

    protected $casts = [
        'indexed' => 'boolean',
        'mobile_usable' => 'boolean',
        'last_crawl_time' => 'datetime',
        'last_submission_time' => 'datetime',
        'last_verification_time' => 'datetime',
        'details' => 'array',
        'errors' => 'array',
        'warnings' => 'array',
        'submission_count' => 'integer',
    ];

    /**
     * Mettre à jour ou créer le statut d'une URL
     */
    public static function updateOrCreateStatus(string $url, array $data): self
    {
        return self::updateOrCreate(
            ['url' => $url],
            array_merge($data, [
                'last_verification_time' => now(),
            ])
        );
    }

    /**
     * Enregistrer une soumission (via Indexing API)
     */
    public static function recordSubmission(string $url): self
    {
        $status = self::firstOrNew(['url' => $url]);
        $status->last_submission_time = now();
        $status->submission_count = ($status->submission_count ?? 0) + 1;
        $status->save();

        return $status;
    }

    /**
     * Scope pour les URLs indexées
     */
    public function scopeIndexed($query)
    {
        return $query->where('indexed', true);
    }

    /**
     * Scope pour les URLs non indexées
     */
    public function scopeNotIndexed($query)
    {
        return $query->where('indexed', false);
    }

    /**
     * Scope pour les URLs à vérifier (vérifiées il y a plus de X heures)
     */
    public function scopeNeedsVerification($query, $hours = 24)
    {
        return $query->where(function($q) use ($hours) {
            $q->whereNull('last_verification_time')
              ->orWhere('last_verification_time', '<', now()->subHours($hours));
        });
    }
}
