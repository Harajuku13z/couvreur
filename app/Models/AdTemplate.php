<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AdTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'service_name',
        'service_slug',
        'content_html',
        'short_description',
        'long_description',
        'icon',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_title',
        'og_description',
        'twitter_title',
        'twitter_description',
        'ai_prompt_used',
        'ai_response_data',
        'is_active',
        'usage_count',
    ];

    protected $casts = [
        'ai_prompt_used' => 'array',
        'ai_response_data' => 'array',
        'is_active' => 'boolean',
        'usage_count' => 'integer',
    ];

    /**
     * Relation avec les annonces utilisant ce template
     */
    public function ads(): HasMany
    {
        return $this->hasMany(Ad::class, 'template_id');
    }

    /**
     * Scope pour les templates actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour un service spécifique
     */
    public function scopeForService($query, $serviceSlug)
    {
        return $query->where('service_slug', $serviceSlug);
    }

    /**
     * Incrémenter le compteur d'utilisation
     */
    public function incrementUsage()
    {
        $this->increment('usage_count');
    }

    /**
     * Décrémenter le compteur d'utilisation
     */
    public function decrementUsage()
    {
        $this->decrement('usage_count');
    }

    /**
     * Obtenir le contenu HTML avec remplacement des variables de ville
     */
    public function getContentForCity($city)
    {
        $content = $this->content_html;
        
        // Remplacer les variables dynamiques
        $replacements = [
            '[VILLE]' => $city->name,
            '[RÉGION]' => $city->region ?? '',
            '[DÉPARTEMENT]' => $city->department ?? '',
            '[FORM_URL]' => url('/form/propertyType'),
            '[URL]' => url('/annonces/' . \Illuminate\Support\Str::slug($this->service_name . '-' . $city->name)),
            '[TITRE]' => $this->service_name . ' à ' . $city->name,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    /**
     * Obtenir les métadonnées avec remplacement des variables de ville
     */
    public function getMetaForCity($city)
    {
        $replacements = [
            '[VILLE]' => $city->name,
            '[RÉGION]' => $city->region ?? '',
            '[DÉPARTEMENT]' => $city->department ?? '',
        ];

        return [
            'meta_title' => str_replace(array_keys($replacements), array_values($replacements), $this->meta_title),
            'meta_description' => str_replace(array_keys($replacements), array_values($replacements), $this->meta_description),
            'meta_keywords' => str_replace(array_keys($replacements), array_values($replacements), $this->meta_keywords),
            'og_title' => str_replace(array_keys($replacements), array_values($replacements), $this->og_title),
            'og_description' => str_replace(array_keys($replacements), array_values($replacements), $this->og_description),
            'twitter_title' => str_replace(array_keys($replacements), array_values($replacements), $this->twitter_title),
            'twitter_description' => str_replace(array_keys($replacements), array_values($replacements), $this->twitter_description),
        ];
    }
}