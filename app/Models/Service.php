<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'icon',
        'image_path',
        'display_order',
        'is_active',
        'meta_title',
        'meta_description',
        'og_image',
        'price_from',
        'order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price_from' => 'decimal:2',
        'order' => 'integer',
        'display_order' => 'integer',
    ];

    /**
     * Configuration du slug automatique (si HasSlug est disponible)
     */
    public function getSlugOptions()
    {
        if (class_exists(\Spatie\Sluggable\SlugOptions::class)) {
            return \Spatie\Sluggable\SlugOptions::create()
                ->generateSlugsFrom('title')
                ->saveSlugsTo('slug');
        }
        return null;
    }

    /**
     * Utiliser le slug pour le routing
     */
    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Données SEO dynamiques (si SEO package est disponible)
     */
    public function getDynamicSEOData()
    {
        if (!class_exists(\RalphJSmit\Laravel\SEO\Support\SEOData::class)) {
            return null;
        }
        
        return \RalphJSmit\Laravel\SEO\Support\SEOData::make()
            ->title($this->meta_title ?: $this->title . ' | Couvreur Expert')
            ->description($this->meta_description ?: \Str::limit(strip_tags($this->description ?? ''), 160))
            ->image($this->og_image ? asset($this->og_image) : ($this->image_path ? asset($this->image_path) : null))
            ->url(route('services.show', $this))
            ->canonical(route('services.show', $this));
    }

    /**
     * Scope pour les services actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour trier par ordre
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order', 'asc')->orderBy('display_order', 'asc');
    }
}
