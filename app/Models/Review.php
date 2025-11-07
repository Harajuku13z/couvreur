<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasFactory;

    protected $fillable = [
        'author_name',
        'author_photo',
        'author_link',
        'author_location',
        'rating',
        'review_text',
        'video_url',
        'google_review_id',
        'author_photo_url',
        'review_date',
        'is_verified',
        'is_active',
        'display_order',
        'source',
    ];

    protected $casts = [
        'review_date' => 'datetime',
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'rating' => 'integer',
        'display_order' => 'integer',
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc')->orderBy('created_at', 'desc');
    }

    public function scopeByRating($query, int $rating)
    {
        return $query->where('rating', $rating);
    }

    // Accessors
    public function getStarsHtmlAttribute(): string
    {
        $html = '';
        for ($i = 1; $i <= 5; $i++) {
            $html .= $i <= (int) $this->rating
                ? '<i class="fas fa-star text-yellow-400"></i>'
                : '<i class="far fa-star text-yellow-400"></i>';
        }
        return $html;
    }

    public function getAuthorInitialsAttribute(): string
    {
        $names = preg_split('/\s+/', trim((string) $this->author_name)) ?: [];
        $initials = '';
        foreach ($names as $name) {
            if ($name !== '') {
                $initials .= strtoupper(substr($name, 0, 1));
            }
        }
        return substr($initials, 0, 2);
    }
}


