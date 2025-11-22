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
        'featured_image',
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
     * Obtenir le contenu HTML avec personnalisation IA avancée pour la ville
     * Utilise l'IA pour créer du contenu 100% UNIQUE au lieu de simples remplacements
     */
    public function getContentForCity($city, $useAi = null)
    {
        // Vérifier si la personnalisation IA est activée
        if ($useAi === null) {
            $useAiPersonalization = \App\Models\Setting::get('ad_template_ai_personalization', false);
            $useAiPersonalization = filter_var($useAiPersonalization, FILTER_VALIDATE_BOOLEAN);
        } else {
            $useAiPersonalization = (bool)$useAi;
        }
        
        if ($useAiPersonalization) {
            try {
                $personalizer = app(\App\Services\CityContentPersonalizer::class);
                $serviceData = [
                    'name' => $this->service_name,
                    'slug' => $this->service_slug,
                    'description' => $this->short_description
                ];
                
                // Générer du contenu UNIQUE avec l'IA
                $personalizedContent = $personalizer->generatePersonalizedContent(
                    $this->content_html, 
                    $serviceData, 
                    $city
                );
                
                return $personalizedContent;
                
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Échec personnalisation IA, fallback sur méthode basique', [
                    'service' => $this->service_name,
                    'city' => $city->name,
                    'error' => $e->getMessage()
                ]);
                // Continuer avec la méthode basique en cas d'erreur
            }
        }
        
        // Fallback : méthode basique (remplacement de variables)
        $content = $this->content_html;
        
        // Remplacer les variables dynamiques
        $replacements = [
            '[VILLE]' => $city->name,
            '[RÉGION]' => $city->region ?? '',
            '[DÉPARTEMENT]' => $city->department ?? '',
            '[CODE_POSTAL]' => $city->postal_code ?? '',
            '[FORM_URL]' => url('/form/propertyType'),
            '[URL]' => url('/annonces/' . \Illuminate\Support\Str::slug($this->service_name . '-' . $city->name)),
            '[TITRE]' => $this->service_name . ' à ' . $city->name,
            '[PHONE]' => \App\Models\Setting::get('company_phone', ''),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $content);
    }

    /**
     * Obtenir les métadonnées avec personnalisation IA avancée pour la ville
     */
    public function getMetaForCity($city, $useAi = null)
    {
        // Vérifier si la personnalisation IA est activée
        if ($useAi === null) {
            $useAiPersonalization = \App\Models\Setting::get('ad_template_ai_personalization', false);
            $useAiPersonalization = filter_var($useAiPersonalization, FILTER_VALIDATE_BOOLEAN);
        } else {
            $useAiPersonalization = (bool)$useAi;
        }
        
        if ($useAiPersonalization) {
            try {
                $personalizer = app(\App\Services\CityContentPersonalizer::class);
                
                // Générer des métadonnées UNIQUES avec l'IA
                $personalizedMeta = $personalizer->generatePersonalizedMeta(
                    $this->service_name, 
                    $city, 
                    [
                        'meta_title' => $this->meta_title,
                        'meta_description' => $this->meta_description,
                        'meta_keywords' => $this->meta_keywords
                    ]
                );
                
                // Compléter avec les autres champs (OG, Twitter)
                $personalizedMeta['og_title'] = $personalizedMeta['meta_title'];
                $personalizedMeta['og_description'] = $personalizedMeta['meta_description'];
                $personalizedMeta['twitter_title'] = $personalizedMeta['meta_title'];
                $personalizedMeta['twitter_description'] = $personalizedMeta['meta_description'];
                
                return $personalizedMeta;
                
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::warning('Échec personnalisation meta IA, fallback sur méthode basique', [
                    'service' => $this->service_name,
                    'city' => $city->name,
                    'error' => $e->getMessage()
                ]);
                // Continuer avec la méthode basique
            }
        }
        
        // Fallback : remplacement basique de variables
        $replacements = [
            '[VILLE]' => $city->name,
            '[RÉGION]' => $city->region ?? '',
            '[DÉPARTEMENT]' => $city->department ?? '',
            '[CODE_POSTAL]' => $city->postal_code ?? '',
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