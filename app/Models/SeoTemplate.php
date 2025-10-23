<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'template_title', 'template_meta', 'content_blocks_json', 'config_json', 'active',
    ];

    protected $casts = [
        'content_blocks_json' => 'array',
        'config_json' => 'array',
        'active' => 'boolean',
    ];
}





