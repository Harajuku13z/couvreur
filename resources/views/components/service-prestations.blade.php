@props(['service' => null, 'prestations' => null])

@php
// Si des prestations sont fournies directement, les utiliser
if ($prestations) {
    $prestationsToShow = $prestations;
} elseif ($service && isset($service['prestations'])) {
    // Si le service a des prestations, les utiliser
    $prestationsToShow = $service['prestations'];
} else {
    // Prestations par défaut pour nettoyage de toiture
    $prestationsToShow = [
        ['icon' => 'fas fa-hands', 'title' => 'Enlèvement manuel des mousses et débris'],
        ['icon' => 'fas fa-spray-can', 'title' => 'Nettoyage haute pression contrôlé'],
        ['icon' => 'fas fa-flask', 'title' => 'Application de traitement anti-mousse professionnel'],
        ['icon' => 'fas fa-shield-alt', 'title' => 'Traitement hydrofuge pour imperméabilisation'],
        ['icon' => 'fas fa-tools', 'title' => 'Inspection et réparation de tuiles endommagées'],
        ['icon' => 'fas fa-water', 'title' => 'Débouchage des gouttières'],
        ['icon' => 'fas fa-sun', 'title' => 'Protection durable contre les UV'],
        ['icon' => 'fas fa-lightbulb', 'title' => 'Conseils d\'entretien personnalisé']
    ];
}
@endphp

<div class="service-prestations">
    <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
        <i class="fas fa-list-check text-blue-600 mr-3"></i>
        Nos Prestations
    </h3>
    
    <div class="prestations-grid">
        @foreach($prestationsToShow as $prestation)
        <div class="prestation-item">
            <div class="prestation-icon">
                <i class="{{ $prestation['icon'] ?? 'fas fa-check' }}"></i>
            </div>
            <div class="prestation-content">
                <span class="prestation-title">{{ $prestation['title'] }}</span>
                @if(isset($prestation['description']))
                <p class="prestation-description">{{ $prestation['description'] }}</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

<style>
.service-prestations {
    margin: 2rem 0;
}

.prestations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
}

.prestation-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border-radius: 8px;
    border-left: 4px solid #3b82f6;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.prestation-item:hover {
    background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.15);
}

.prestation-icon {
    flex-shrink: 0;
    width: 2.5rem;
    height: 2.5rem;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1rem;
    box-shadow: 0 2px 6px rgba(59, 130, 246, 0.3);
}

.prestation-content {
    flex: 1;
}

.prestation-title {
    font-weight: 600;
    color: #1f2937;
    line-height: 1.4;
    display: block;
}

.prestation-description {
    font-size: 0.875rem;
    color: #6b7280;
    margin-top: 0.25rem;
    line-height: 1.4;
}

/* Responsive */
@media (max-width: 768px) {
    .prestations-grid {
        grid-template-columns: 1fr;
        gap: 0.75rem;
    }
    
    .prestation-item {
        padding: 0.75rem;
        gap: 0.75rem;
    }
    
    .prestation-icon {
        width: 2rem;
        height: 2rem;
        font-size: 0.875rem;
    }
    
    .prestation-title {
        font-size: 0.9rem;
    }
}
</style>
