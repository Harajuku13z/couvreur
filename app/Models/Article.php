<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content_html',
        'content_json',
        'featured_image',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_data',
        'status',
        'focus_keyword',
        'estimated_reading_time',
        'difficulty',
        'tags',
        'published_at',
        'city_id',
        'is_published',
        'author_id',
    ];

    protected $casts = [
        'content_json' => 'array',
        'og_data' => 'array',
        'tags' => 'array',
        'published_at' => 'datetime',
        'is_published' => 'boolean',
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

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * Données SEO dynamiques pour les articles (si SEO package est disponible)
     */
    public function getDynamicSEOData()
    {
        if (!class_exists(\RalphJSmit\Laravel\SEO\Support\SEOData::class)) {
            return null;
        }
        
        $seoData = \RalphJSmit\Laravel\SEO\Support\SEOData::make()
            ->title($this->meta_title ?: $this->title)
            ->description($this->meta_description ?: $this->excerpt)
            ->image($this->featured_image ? asset($this->featured_image) : null)
            ->url(route('blog.show', $this))
            ->canonical(route('blog.show', $this))
            ->type('article');

        if ($this->published_at) {
            $seoData->publishedTime($this->published_at);
        }
        if ($this->updated_at) {
            $seoData->modifiedTime($this->updated_at);
        }
        if ($this->author) {
            $seoData->author($this->author->name);
        }

        return $seoData;
    }

    public function scopePublished($query)
    {
        return $query->where(function($q) {
            $q->where('is_published', true)
              ->orWhere('status', 'published');
        })->where(function($q) {
            $q->whereNull('published_at')
              ->orWhere('published_at', '<=', now());
        });
    }

    public function scopeLatest($query)
    {
        return $query->orderBy('published_at', 'desc')->orderBy('created_at', 'desc');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft')->orWhere('is_published', false);
    }

    public function getExcerptAttribute($value)
    {
        if ($value) {
            return $value;
        }
        
        // Générer un excerpt automatiquement si pas défini
        $content = strip_tags($this->content_html);
        return Str::limit($content, 160);
    }

    /**
     * Relation avec la ville
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Relation avec l'auteur
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Relation avec les images
     */
    public function images()
    {
        return $this->hasMany(ArticleImage::class);
    }
}
