# 💰 Guide du Simulateur de Coûts

## 📍 Accès

### Pour les visiteurs :
- **URL publique** : `https://couvreur-chevigny-saint-sauveur.fr/simulateur`

### Pour l'administration :
- **Configuration** : `/admin/simulator`
- **Connexion requise** : Compte admin

---

## ⚙️ Configuration du Simulateur

### 1. Paramètres généraux

**Titre et description :**
- Modifiables dans la page de configuration
- Impact SEO : utilisés pour meta title et description
- Recommandé : 
  - Titre : "Simulateur de Coûts Travaux - Estimation Gratuite Instantanée"
  - Description : "Estimez le coût de vos travaux de couverture, isolation, façade en quelques clics. Résultat immédiat avec fourchette de prix réaliste."

### 2. Configuration des services

Pour chaque service, configurer :

**Champs obligatoires :**
- **ID** (slug) : Identifiant unique (ex: `toiture`, `facade`, `isolation`)
- **Nom** : Nom affiché (ex: "Rénovation de toiture")
- **Prix/m²** : Tarif de base au m² (ex: 80€)
- **Description** : Description courte affichée

**Options additionnelles par service :**
Chaque service peut avoir des options comme :
- Isolation thermique renforcée : +25€/m²
- Couverture zinc : +40€/m²
- Fenêtres de toit : +15€/m²
- etc.

---

## 🎯 Services recommandés à configurer

### Service 1 : Rénovation de toiture
```
ID: toiture
Nom: Rénovation de toiture complète
Prix/m²: 80€
Description: Remplacement ou rénovation complète de votre toiture

Options:
- Isolation thermique : +25€/m²
- Velux / fenêtres de toit : +15€/m²
- Couverture zinc : +40€/m²
- Ardoise naturelle : +50€/m²
- Zinguerie complète : +20€/m²
```

### Service 2 : Ravalement de façade
```
ID: facade
Nom: Ravalement de façade
Prix/m²: 60€
Description: Nettoyage et rénovation de votre façade

Options:
- Isolation thermique extérieure (ITE) : +50€/m²
- Peinture de finition premium : +20€/m²
- Traitement anti-mousse : +8€/m²
- Enduit décoratif : +15€/m²
```

### Service 3 : Isolation des combles
```
ID: isolation-combles
Nom: Isolation des combles
Prix/m²: 35€
Description: Isolation thermique pour économies d'énergie

Options:
- Laine de roche haute performance : +10€/m²
- Pare-vapeur renforcé : +5€/m²
- Isolation phonique : +8€/m²
- Aménagement combles : +45€/m²
```

### Service 4 : Charpente
```
ID: charpente
Nom: Rénovation de charpente
Prix/m²: 120€
Description: Réparation ou remplacement de charpente

Options:
- Traitement anti-insectes/anti-humidité : +15€/m²
- Renforcement structure : +30€/m²
- Charpente traditionnelle sur-mesure : +60€/m²
- Surélévation : +80€/m²
```

### Service 5 : Zinguerie
```
ID: zinguerie
Nom: Travaux de zinguerie
Prix/m²: 70€
Description: Gouttières, chenaux, noues, faîtage zinc

Options:
- Zinc naturel (vs prépatiné) : +15€/m²
- Cuivre : +45€/m²
- Protection anti-mousse : +8€/m²
- Système récupération eau pluie : +12€/m²
```

---

## 🧮 Calcul des coûts

### Formule appliquée :

```
Coût Total = (Prix base/m² × Surface) 
             × Multiplicateur Qualité
             × Multiplicateur Urgence  
             × Multiplicateur Type propriété
             + Somme options additionnelles
```

### Multiplicateurs automatiques :

**Qualité :**
- Standard : ×1.0
- Premium : ×1.4 
- Luxe : ×2.0

**Urgence :**
- Normal (2-4 semaines) : ×1.0
- Urgent (sous 1 semaine) : ×1.25
- Urgence (48h) : ×1.6

**Type de propriété :**
- Maison : ×1.0
- Appartement : ×0.9 (moins complexe)
- Commerce : ×1.3 (normes ERP)
- Industriel : ×1.5 (hauteur, sécurité)

**Dégressivité :**
- Surface > 100m² : Jusqu'à -15% (progressif)

---

## 📊 Fourchette de prix

Le simulateur génère automatiquement :
- **Coût central** : Estimation arrondie au millier supérieur
- **Min** : -20% du coût central
- **Max** : +20% du coût central

Exemple : Pour 12 500€
- Min : 10 000€
- Central : 12 000€  (arrondi)
- Max : 15 000€

---

## 🎨 Personnalisation visuelle

### Modifications possibles :

**Dans `/resources/views/simulator/index.blade.php` :**

1. **Couleurs** : Classes Tailwind
   - Primary : `bg-blue-600`, `text-blue-600`
   - Success : `bg-green-600`
   - Warning : `bg-yellow-50`

2. **Icônes** : SVG Heroicons
   - Modifier les `<svg>` dans le template

3. **Textes CTA** :
   - "Obtenir un devis personnalisé gratuit"
   - "Calculer le coût estimé"

---

## 📈 SEO du Simulateur

### Optimisations recommandées :

1. **Page dédiée par service** :
   - `/simulateur/toiture`
   - `/simulateur/facade`
   - `/simulateur/isolation`
   - Chacune avec contenu SEO unique

2. **Schema.org Calculator** :
```html
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebApplication",
  "name": "Simulateur de Coûts Travaux Couverture",
  "url": "https://couvreur-chevigny-saint-sauveur.fr/simulateur",
  "applicationCategory": "FinanceApplication",
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "EUR"
  }
}
</script>
```

3. **Optimiser pour recherches type** :
   - "simulateur coût toiture"
   - "calculer prix rénovation toiture"
   - "estimer coût travaux couverture"

---

## 🔗 Intégration site

### Ajouter liens vers simulateur :

**1. Menu navigation principal :**
```html
<a href="{{ route('simulator.index') }}">
    <i class="fas fa-calculator"></i> Simulateur de coûts
</a>
```

**2. Barre sticky (top ou bottom) :**
```html
<div class="fixed bottom-0 w-full bg-blue-600 text-white py-3 text-center z-50">
    <a href="{{ route('simulator.index') }}" class="text-white font-bold">
        💰 Simulateur gratuit : Estimez vos travaux en 2 min →
    </a>
</div>
```

**3. Dans chaque page service :**
```html
<div class="cta-simulator">
    <h3>Combien coûtent vos travaux ?</h3>
    <p>Utilisez notre simulateur gratuit pour une estimation immédiate</p>
    <a href="{{ route('simulator.index') }}" class="btn btn-primary">
        Estimer mon projet
    </a>
</div>
```

**4. Dans les articles de blog :**
```html
<div class="encadre-simulateur">
    <strong>💡 Estimation rapide :</strong> 
    Utilisez notre <a href="{{ route('simulator.index') }}">simulateur de coûts</a> 
    pour obtenir une fourchette de prix instantanée.
</div>
```

---

## 📊 Tracking & Analytics

### Événements à suivre :

```javascript
// Dans simulator/index.blade.php
// Ajouter Google Analytics events

// Début simulation
gtag('event', 'simulator_start', {
    'event_category': 'simulator',
    'event_label': 'start'
});

// Service sélectionné
gtag('event', 'simulator_service_selected', {
    'event_category': 'simulator',
    'event_label': serviceType
});

// Calcul effectué
gtag('event', 'simulator_calculation', {
    'event_category': 'simulator',
    'value': estimatedCost,
    'event_label': serviceType
});

// Clic CTA devis
gtag('event', 'simulator_cta_click', {
    'event_category': 'simulator',
    'event_label': 'devis_request'
});
```

### KPIs à surveiller :
- Taux d'utilisation (simulations / visiteurs)
- Taux de complétion (calculs / débuts)
- Taux de conversion (devis après simulation)
- Valeur moyenne estimée
- Services les plus demandés

---

## 🚀 Améliorations futures

### Phase 2 : Fonctionnalités avancées

1. **Export PDF de l'estimation**
   - Bouton "Télécharger mon estimation"
   - PDF brandé avec détails projet
   - Include CTA devis personnalisé

2. **Sauvegarde et reprise**
   - Permettre sauvegarder estimation
   - Recevoir par email
   - Reprendre plus tard

3. **Comparaison matériaux**
   - Onglets matériaux avec prix différents
   - Tableau comparatif avantages/inconvénients
   - Photos référence

4. **Simulateur par étapes** (wizard)
   - Étape par étape avec progression
   - Plus engageant
   - Meilleur taux de complétion

5. **Intégration directe au formulaire devis**
   - Pré-remplir le formulaire avec données simulateur
   - Continuité UX
   - Augmentation conversions

6. **Versioning par ville**
   - Tarifs personnalisés par ville/région
   - Prendre en compte coût de vie local
   - Aides régionales spécifiques

---

## 💡 Conseils d'utilisation

### Pour maximiser les conversions :

1. **Promouvoir le simulateur**
   - Lien dans menu principal
   - Bannière homepage
   - Mention dans chaque article blog
   - Call-to-action dans pages services

2. **Optimiser UX**
   - Garder le processus court (< 2 min)
   - Design moderne et professionnel
   - Mobile-first parfait
   - Résultat instantané

3. **Être transparent**
   - Fourchettes réalistes
   - Disclaimers clairs
   - Explication détaillée des coûts
   - CTA devis personnalisé visible

4. **Suivre et optimiser**
   - Analytics sur chaque étape
   - A/B testing CTA
   - Tester différents tarifs
   - Ajuster selon retours clients

---

## ✅ Checklist mise en production

- [ ] Configurer tous les services avec tarifs réalistes
- [ ] Ajouter 3-5 options par service
- [ ] Rédiger disclaimers juridiques
- [ ] Tester calcul avec différents paramètres
- [ ] Vérifier responsive mobile
- [ ] Ajouter Google Analytics events
- [ ] Créer lien dans menu principal
- [ ] Promouvoir sur homepage
- [ ] Tester conversion devis après simulation
- [ ] Surveiller métriques première semaine

---

## 📞 Support

En cas de problème :
1. Vérifier logs : `storage/logs/laravel.log`
2. Configuration : `/admin/simulator`
3. Tester route : `/simulateur`
4. Vérifier Setting : `cost_simulator_config`

---

*Simulateur déjà implémenté et fonctionnel - Prêt à l'emploi !*

