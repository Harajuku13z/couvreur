<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ad extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'keyword', 'city_id', 'template_id', 'slug', 'status',
        'meta_title', 'meta_description', 'content_html', 'content_json', 'published_at',
    ];

    protected $casts = [
        'content_json' => 'array',
        'published_at' => 'datetime',
    ];

    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(AdTemplate::class);
    }

    /**
     * Get the publication date, fallback to created_at if published_at is null
     */
    public function getPublicationDateAttribute()
    {
        return $this->published_at ?? $this->created_at;
    }

    /**
     * Get formatted publication date
     */
    public function getFormattedPublicationDateAttribute()
    {
        return $this->getPublicationDateAttribute()->format('d/m/Y');
    }
}





