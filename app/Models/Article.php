<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
    ];

    protected $casts = [
        'content_json' => 'array',
        'og_data' => 'array',
        'tags' => 'array',
        'published_at' => 'datetime',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
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
}
