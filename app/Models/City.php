<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'postal_code', 'department', 'region', 'active', 'is_favorite',
    ];

    public function ads(): HasMany
    {
        return $this->hasMany(Ad::class);
    }
}





