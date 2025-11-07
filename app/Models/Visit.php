<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'ip_address',
        'user_agent',
        'url',
        'path',
        'method',
        'referrer_url',
        'city',
        'country',
        'country_code',
        'device_type',
        'browser',
        'os',
        'is_bot',
        'duration',
        'visited_at',
    ];

    protected $casts = [
        'visited_at' => 'datetime',
        'is_bot' => 'boolean',
        'duration' => 'integer',
    ];

    /**
     * Scope pour les visites d'aujourd'hui
     */
    public function scopeToday($query)
    {
        return $query->whereDate('visited_at', today());
    }

    /**
     * Scope pour les visites de cette semaine
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('visited_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * Scope pour les visites de ce mois
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('visited_at', now()->month)
                     ->whereYear('visited_at', now()->year);
    }

    /**
     * Scope pour exclure les bots
     */
    public function scopeExcludeBots($query)
    {
        return $query->where('is_bot', false);
    }

    /**
     * Scope pour une période donnée
     */
    public function scopeForPeriod($query, $days = 30)
    {
        return $query->where('visited_at', '>=', now()->subDays($days));
    }
}

