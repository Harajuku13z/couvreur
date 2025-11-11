<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArticleImage extends Model
{
    protected $fillable = [
        'article_id',
        'image_path',
        'alt_text',
        'keywords',
        'title',
        'description',
        'width',
        'height',
        'file_size',
        'mime_type',
    ];

    /**
     * Relation avec l'article
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * Obtenir l'URL complète de l'image
     */
    public function getUrlAttribute(): string
    {
        return url($this->image_path);
    }

    /**
     * Obtenir les mots-clés sous forme de tableau
     */
    public function getKeywordsArrayAttribute(): array
    {
        if (empty($this->keywords)) {
            return [];
        }
        return array_map('trim', explode(',', $this->keywords));
    }
}
