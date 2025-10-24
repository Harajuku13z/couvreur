@props(['prestations' => []])

@php
$defaultPrestations = [
    [
        'icon' => 'fas fa-hands',
        'title' => 'Enlèvement manuel des mousses et débris',
        'description' => 'Nettoyage minutieux par nos experts'
    ],
    [
        'icon' => 'fas fa-spray-can',
        'title' => 'Nettoyage haute pression contrôlé',
        'description' => 'Technique professionnelle et sécurisée'
    ],
    [
        'icon' => 'fas fa-flask',
        'title' => 'Application de traitement anti-mousse professionnel',
        'description' => 'Produits de qualité professionnelle'
    ],
    [
        'icon' => 'fas fa-shield-alt',
        'title' => 'Traitement hydrofuge pour imperméabilisation',
        'description' => 'Protection durable contre l\'humidité'
    ],
    [
        'icon' => 'fas fa-tools',
        'title' => 'Inspection et réparation de tuiles endommagées',
        'description' => 'Réparation et remplacement si nécessaire'
    ],
    [
        'icon' => 'fas fa-water',
        'title' => 'Débouchage des gouttières',
        'description' => 'Écoulement optimal des eaux pluviales'
    ],
    [
        'icon' => 'fas fa-sun',
        'title' => 'Protection durable contre les UV',
        'description' => 'Résistance aux rayons ultraviolets'
    ],
    [
        'icon' => 'fas fa-lightbulb',
        'title' => 'Conseils d\'entretien personnalisé',
        'description' => 'Recommandations adaptées à votre toiture'
    ]
];

$prestationsToShow = !empty($prestations) ? $prestations : $defaultPrestations;
@endphp

<div class="prestations-container">
    <h3 class="text-2xl font-bold text-gray-900 mb-6 text-center">
        <i class="fas fa-list-check text-blue-600 mr-3"></i>
        Nos Prestations
    </h3>
    
    <div class="prestations-grid">
        @foreach($prestationsToShow as $prestation)
        <div class="prestation-card">
            <div class="prestation-icon">
                <i class="{{ $prestation['icon'] ?? 'fas fa-check' }}"></i>
            </div>
            <div class="prestation-content">
                <h4 class="prestation-title">{{ $prestation['title'] }}</h4>
                @if(isset($prestation['description']))
                <p class="prestation-description">{{ $prestation['description'] }}</p>
                @endif
            </div>
        </div>
        @endforeach
    </div>
</div>

<style>
.prestations-container {
    margin: 2rem 0;
}

.prestations-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
    margin-top: 2rem;
}

.prestation-card {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    padding: 1.5rem;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border-radius: 12px;
    border-left: 4px solid #3b82f6;
    transition: all 0.3s ease;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}

.prestation-card:hover {
    background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(59, 130, 246, 0.15);
}

.prestation-icon {
    flex-shrink: 0;
    width: 3rem;
    height: 3rem;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.25rem;
    box-shadow: 0 4px 8px rgba(59, 130, 246, 0.3);
}

.prestation-content {
    flex: 1;
}

.prestation-title {
    font-size: 1.1rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 0.5rem;
    line-height: 1.4;
}

.prestation-description {
    font-size: 0.9rem;
    color: #6b7280;
    line-height: 1.5;
    margin: 0;
}

/* Responsive */
@media (max-width: 768px) {
    .prestations-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .prestation-card {
        padding: 1rem;
    }
    
    .prestation-icon {
        width: 2.5rem;
        height: 2.5rem;
        font-size: 1rem;
    }
    
    .prestation-title {
        font-size: 1rem;
    }
}
</style>
