<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AbandonedSubmission extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'user_identifier',
        'abandoned_at_step',
        'step_number',
        'form_data',
        'time_spent_seconds',
        'abandon_reason',
    ];

    protected $casts = [
        'form_data' => 'array',
    ];

    // Scopes
    public function scopeByStep($query, string $step)
    {
        return $query->where('abandoned_at_step', $step);
    }

    public function scopeByStepNumber($query, int $stepNumber)
    {
        return $query->where('step_number', $stepNumber);
    }

    // Utilities
    public function getTimeSpentFormatted(): string
    {
        if (!$this->time_spent_seconds) {
            return 'N/A';
        }

        $minutes = (int) floor($this->time_spent_seconds / 60);
        $seconds = (int) ($this->time_spent_seconds % 60);

        if ($minutes > 0) {
            return "{$minutes}m {$seconds}s";
        }

        return "{$seconds}s";
    }

    public function getStepName(): string
    {
        $stepNames = [
            'propertyType' => 'Type de propriété',
            'surface' => 'Surface',
            'workType' => 'Type de travaux',
            'roofWorkType' => 'Travaux de toiture',
            'facadeWorkType' => 'Travaux de façade',
            'isolationWorkType' => 'Travaux d\'isolation',
            'ownershipStatus' => 'Statut de propriété',
            'personalInfo' => 'Informations personnelles',
            'postalCode' => 'Code postal',
            'phone' => 'Téléphone',
            'email' => 'Email',
        ];

        return $stepNames[$this->abandoned_at_step] ?? (string) $this->abandoned_at_step;
    }
}








