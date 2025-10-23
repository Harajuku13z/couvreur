<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GenerationJob extends Model
{
    use HasFactory;

    protected $fillable = [
        'mode', 'payload_json', 'status', 'stats_json', 'finished_at',
    ];

    protected $casts = [
        'payload_json' => 'array',
        'stats_json' => 'array',
        'finished_at' => 'datetime',
    ];
}





