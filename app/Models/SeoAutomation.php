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
     * Setter pour metadata avec nettoyage UTF-8
     */
    public function setMetadataAttribute($value)
    {
        if (is_array($value)) {
            $cleaned = $this->cleanForJson($value);
            $this->attributes['metadata'] = json_encode($cleaned, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        } else {
            $this->attributes['metadata'] = $value;
        }
    }
    
    /**
     * Nettoie les données pour éviter les erreurs UTF-8 malformées
     */
    protected function cleanForJson($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'cleanForJson'], $data);
        } elseif (is_string($data)) {
            // Supprimer les caractères UTF-8 invalides
            $cleaned = mb_convert_encoding($data, 'UTF-8', 'UTF-8');
            // Supprimer les caractères de contrôle non valides
            $cleaned = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $cleaned);
            // Vérifier que c'est bien de l'UTF-8 valide
            if (!mb_check_encoding($cleaned, 'UTF-8')) {
                // Si toujours invalide, utiliser iconv avec ignore
                $cleaned = @iconv('UTF-8', 'UTF-8//IGNORE', $data);
                if ($cleaned === false) {
                    // Dernier recours : supprimer tous les caractères non-ASCII
                    $cleaned = preg_replace('/[^\x20-\x7E]/', '', $data);
                }
            }
            return $cleaned;
        } elseif (is_object($data)) {
            return $this->cleanForJson((array)$data);
        }
        return $data;
    }

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
