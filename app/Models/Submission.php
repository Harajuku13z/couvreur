<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Submission extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_identifier',
        'property_type',
        'surface',
        'work_types',
        'roof_work_types',
        'facade_work_types',
        'isolation_work_types',
        'ownership_status',
        'gender',
        'first_name',
        'last_name',
        'postal_code',
        'phone',
        'email',
        'status',
        'current_step',
        'form_data',
        'completed_at',
        'abandoned_at'
    ];

    protected $casts = [
        'work_types' => 'array',
        'roof_work_types' => 'array',
        'facade_work_types' => 'array',
        'isolation_work_types' => 'array',
        'form_data' => 'array',
        'completed_at' => 'datetime',
        'abandoned_at' => 'datetime',
    ];

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'COMPLETED');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'IN_PROGRESS');
    }

    public function scopeAbandoned($query)
    {
        return $query->where('status', 'ABANDONED');
    }

    // Utilities
    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'COMPLETED',
            'completed_at' => now(),
        ]);
    }

    public function markAsAbandoned(): void
    {
        $this->update([
            'status' => 'ABANDONED',
            'abandoned_at' => now(),
        ]);
    }

    public function getProgressPercentage(): float
    {
        $steps = [
            'propertyType' => 1,
            'surface' => 2,
            'workType' => 3,
            'roofWorkType' => 4,
            'facadeWorkType' => 5,
            'isolationWorkType' => 6,
            'ownershipStatus' => 7,
            'personalInfo' => 8,
            'postalCode' => 9,
            'phone' => 10,
            'email' => 11,
        ];

        $currentStep = $this->current_step;
        $totalSteps = count($steps);
        
        if (!$currentStep || !isset($steps[$currentStep])) {
            return 0.0;
        }

        return round(($steps[$currentStep] / $totalSteps) * 100, 2);
    }
}








