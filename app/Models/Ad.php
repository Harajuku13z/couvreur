<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ad extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'keyword', 'city_id', 'slug', 'status',
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
}





