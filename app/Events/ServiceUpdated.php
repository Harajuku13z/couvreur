<?php

namespace App\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ServiceUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct()
    {
        // Pas besoin de données spécifiques pour les services
    }
}
