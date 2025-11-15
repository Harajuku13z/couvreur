<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use RalphJSmit\Laravel\SEO\Support\HasSEO;
use RalphJSmit\Laravel\SEO\Support\SEOData;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class City extends Model
{
    use HasFactory, HasSEO, HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'postal_code',
        'department',
        'region',
        'active',
        'is_favorite',
        'description',
        'latitude',
        'longitude',
        'phone',
        'email',
        'meta_title',
        'meta_description',
        'is_active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'is_favorite' => 'boolean',
        'is_active' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    /**
     * Configuration du slug automatique
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    /**
     * Utiliser le slug pour le routing
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Données SEO dynamiques pour le SEO local
     */
    public function getDynamicSEOData(): SEOData
    {
        $title = $this->meta_title ?: "Couvreur à {$this->name} ({$this->postal_code}) | Intervention Rapide";
        $description = $this->meta_description ?: "Expert couvreur à {$this->name} ({$this->postal_code}). Rénovation, réparation, démoussage de toiture. Devis gratuit, intervention rapide.";

        return SEOData::make()
            ->title($title)
            ->description($description)
            ->url(route('locale.show', $this))
            ->canonical(route('locale.show', $this));
    }

    /**
     * Scope pour les villes actives
     */
    public function scopeActive($query)
    {
        return $query->where(function($q) {
            $q->where('is_active', true)
              ->orWhere('active', true);
        });
    }

    public function ads(): HasMany
    {
        return $this->hasMany(Ad::class);
    }
}





