@props(['title' => 'Nos Prestations'])

<div class="prestations-simple">
    <h3 class="text-xl font-bold text-gray-900 mb-6 flex items-center">
        <i class="fas fa-list-check text-blue-600 mr-3"></i>
        {{ $title }}
    </h3>
    
    <div class="space-y-3">
        <div class="prestation-item">
            <i class="fas fa-hands text-blue-600"></i>
            <span>Enlèvement manuel des mousses et débris</span>
        </div>
        
        <div class="prestation-item">
            <i class="fas fa-spray-can text-blue-600"></i>
            <span>Nettoyage haute pression contrôlé</span>
        </div>
        
        <div class="prestation-item">
            <i class="fas fa-flask text-blue-600"></i>
            <span>Application de traitement anti-mousse professionnel</span>
        </div>
        
        <div class="prestation-item">
            <i class="fas fa-shield-alt text-blue-600"></i>
            <span>Traitement hydrofuge pour imperméabilisation</span>
        </div>
        
        <div class="prestation-item">
            <i class="fas fa-tools text-blue-600"></i>
            <span>Inspection et réparation de tuiles endommagées</span>
        </div>
        
        <div class="prestation-item">
            <i class="fas fa-water text-blue-600"></i>
            <span>Débouchage des gouttières</span>
        </div>
        
        <div class="prestation-item">
            <i class="fas fa-sun text-blue-600"></i>
            <span>Protection durable contre les UV</span>
        </div>
        
        <div class="prestation-item">
            <i class="fas fa-lightbulb text-blue-600"></i>
            <span>Conseils d'entretien personnalisé</span>
        </div>
    </div>
</div>

<style>
.prestations-simple {
    margin: 1.5rem 0;
}

.prestation-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 0.75rem 1rem;
    background: #f8fafc;
    border-radius: 8px;
    border-left: 3px solid #3b82f6;
    transition: all 0.3s ease;
    margin-bottom: 0.5rem;
}

.prestation-item:hover {
    background: #e0f2fe;
    transform: translateX(5px);
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.15);
}

.prestation-item i {
    font-size: 1.25rem;
    min-width: 1.5rem;
    text-align: center;
}

.prestation-item span {
    font-weight: 500;
    color: #374151;
    line-height: 1.4;
}

/* Responsive */
@media (max-width: 768px) {
    .prestation-item {
        padding: 0.5rem 0.75rem;
        gap: 0.75rem;
    }
    
    .prestation-item i {
        font-size: 1rem;
        min-width: 1.25rem;
    }
    
    .prestation-item span {
        font-size: 0.9rem;
    }
}
</style>
