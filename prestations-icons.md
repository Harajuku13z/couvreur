# Icônes pour les prestations de nettoyage de toiture

## Nos Prestations - Nettoyage de toiture

### 1. Enlèvement manuel des mousses et débris
- **Icône** : `fas fa-hands` ou `fas fa-broom`
- **Description** : Représente le travail manuel et le nettoyage

### 2. Nettoyage haute pression contrôlé
- **Icône** : `fas fa-spray-can` ou `fas fa-tint`
- **Description** : Représente l'eau sous pression

### 3. Application de traitement anti-mousse professionnel
- **Icône** : `fas fa-flask` ou `fas fa-vial`
- **Description** : Représente les produits chimiques professionnels

### 4. Traitement hydrofuge pour imperméabilisation
- **Icône** : `fas fa-shield-alt` ou `fas fa-umbrella`
- **Description** : Représente la protection contre l'eau

### 5. Inspection et réparation de tuiles endommagées
- **Icône** : `fas fa-tools` ou `fas fa-hammer`
- **Description** : Représente la réparation et l'entretien

### 6. Débouchage des gouttières
- **Icône** : `fas fa-water` ou `fas fa-stream`
- **Description** : Représente l'écoulement de l'eau

### 7. Protection durable contre les UV
- **Icône** : `fas fa-sun` ou `fas fa-shield-virus`
- **Description** : Représente la protection solaire

### 8. Conseils d'entretien personnalisé
- **Icône** : `fas fa-lightbulb` ou `fas fa-user-tie`
- **Description** : Représente les conseils et l'expertise

## Code HTML avec icônes

```html
<div class="prestations-list">
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
```

## CSS pour le style

```css
.prestations-list {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
    margin: 2rem 0;
}

.prestation-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: #f8fafc;
    border-radius: 8px;
    border-left: 4px solid #3b82f6;
    transition: all 0.3s ease;
}

.prestation-item:hover {
    background: #e0f2fe;
    transform: translateX(5px);
}

.prestation-item i {
    font-size: 1.5rem;
    min-width: 2rem;
}

.prestation-item span {
    font-weight: 500;
    color: #374151;
}
```
