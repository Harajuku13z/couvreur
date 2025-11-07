<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhoneCall extends Model
{
    use HasFactory;

    protected $fillable = [
        'submission_id',
        'session_id',
        'phone_number',
        'source_page',
        'user_agent',
        'ip_address',
        'city',
        'country',
        'country_code',
        'referrer_url',
        'clicked_at',
    ];

    protected $casts = [
        'clicked_at' => 'datetime',
    ];

    /**
     * Get the submission associated with this phone call
     */
    public function submission()
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }

    /**
     * Scope for today's calls
     */
    public function scopeToday($query)
    {
        return $query->whereDate('clicked_at', today());
    }

    /**
     * Scope for this week's calls
     */
    public function scopeThisWeek($query)
    {
        return $query->whereBetween('clicked_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    /**
     * Scope for this month's calls
     */
    public function scopeThisMonth($query)
    {
        return $query->whereMonth('clicked_at', now()->month)
                     ->whereYear('clicked_at', now()->year);
    }
}


















