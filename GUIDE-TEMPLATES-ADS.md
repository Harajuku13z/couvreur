# Guide d'utilisation du Système de Templates d'Annonces

## 🎯 Vue d'ensemble

Le système de templates d'annonces permet de créer des templates de contenu générés par l'IA et de les réutiliser pour créer des annonces personnalisées pour différentes villes.

## 🚀 Installation

### 1. Exécuter les migrations

```bash
# En production, exécuter les requêtes SQL suivantes:

# Création de la table ad_templates
CREATE TABLE IF NOT EXISTS ad_templates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    service_name VARCHAR(255) NOT NULL,
    service_slug VARCHAR(255) NOT NULL,
    content_html LONGTEXT NOT NULL,
    short_description TEXT NOT NULL,
    long_description TEXT NOT NULL,
    icon VARCHAR(50) DEFAULT 'fas fa-tools',
    meta_title VARCHAR(160) NOT NULL,
    meta_description TEXT NOT NULL,
    meta_keywords TEXT NOT NULL,
    og_title VARCHAR(160) NOT NULL,
    og_description TEXT NOT NULL,
    twitter_title VARCHAR(160) NOT NULL,
    twitter_description TEXT NOT NULL,
    ai_prompt_used JSON NULL,
    ai_response_data JSON NULL,
    is_active BOOLEAN DEFAULT TRUE,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP NULL DEFAULT NULL,
    updated_at TIMESTAMP NULL DEFAULT NULL,
    INDEX idx_service_slug_active (service_slug, is_active),
    INDEX idx_service_name (service_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

# Ajout de la colonne template_id à la table ads
ALTER TABLE ads 
ADD COLUMN template_id BIGINT UNSIGNED NULL AFTER city_id,
ADD INDEX idx_template_id (template_id),
ADD CONSTRAINT fk_ads_template_id 
    FOREIGN KEY (template_id) 
    REFERENCES ad_templates(id) 
    ON DELETE SET NULL;
```

## 📋 Utilisation

### 1. Accéder aux Templates

- URL: `/admin/ads/templates`
- Menu: Admin > Annonces > Templates

### 2. Créer un Template

1. Cliquer sur "Créer un Template"
2. Sélectionner un service existant
3. Ajouter des instructions personnalisées (optionnel)
4. Cliquer sur "Créer le Template"

Le système génère automatiquement:
- Contenu HTML complet avec 10 prestations
- FAQ détaillée
- Métadonnées SEO
- Boutons de partage social
- Placeholders pour les variables de ville

### 3. Générer des Annonces

1. Depuis la liste des templates, cliquer sur l'icône "+"
2. Sélectionner les villes souhaitées
3. Cliquer sur "Générer les Annonces"

Le système:
- Remplace automatiquement `[VILLE]`, `[RÉGION]`, `[DÉPARTEMENT]`
- Génère des URLs uniques
- Crée des titres personnalisés
- Assure la cohérence du contenu

## 🔧 Fonctionnalités

### Templates
- **Création automatique** via IA
- **Réutilisation** pour plusieurs villes
- **Personnalisation** par service
- **Gestion centralisée** du contenu
- **Statut actif/inactif**

### Annonces
- **Génération en masse** à partir de templates
- **Personnalisation automatique** par ville
- **Métadonnées SEO** optimisées
- **Contenu cohérent** et professionnel
- **Évite la duplication** de contenu

### Variables Dynamiques
- `[VILLE]` → Nom de la ville
- `[RÉGION]` → Région de la ville
- `[DÉPARTEMENT]` → Département de la ville
- `[FORM_URL]` → URL du formulaire de devis
- `[URL]` → URL de l'annonce
- `[TITRE]` → Titre de l'annonce

## 📊 Avantages

### Économie de Temps
- ✅ Un template = plusieurs annonces
- ✅ Génération automatique du contenu
- ✅ Personnalisation instantanée

### Qualité du Contenu
- ✅ Contenu généré par IA professionnel
- ✅ Structure cohérente et optimisée SEO
- ✅ 10 prestations détaillées par service
- ✅ FAQ complète

### Maintenance
- ✅ Mise à jour centralisée
- ✅ Gestion des templates en un endroit
- ✅ Suivi de l'utilisation
- ✅ Activation/désactivation facile

## 🎨 Structure du Contenu

Chaque template génère:

```html
<div class="grid md:grid-cols-2 gap-8">
    <!-- Colonne gauche -->
    <div class="space-y-6">
        <!-- Introduction -->
        <div class="space-y-4">
            <p>Service professionnel de [SERVICE] à [VILLE]...</p>
        </div>
        
        <!-- Engagement qualité -->
        <div class="bg-blue-50 p-6 rounded-lg">
            <h3>Notre Engagement Qualité</h3>
        </div>
        
        <!-- 10 Prestations -->
        <h3>Nos Prestations [SERVICE]</h3>
        <ul class="space-y-3">
            <li><i class="fas fa-check"></i> <strong>Prestation 1</strong> - Description</li>
            <!-- ... 9 autres prestations ... -->
        </ul>
        
        <!-- FAQ -->
        <div class="bg-gray-50 p-6 rounded-lg">
            <h4>FAQ</h4>
            <!-- 3 questions/réponses -->
        </div>
    </div>
    
    <!-- Colonne droite -->
    <div class="space-y-6">
        <!-- Pourquoi choisir -->
        <div class="bg-green-50 p-6 rounded-lg">
            <h3>Pourquoi choisir ce service</h3>
        </div>
        
        <!-- Expertise locale -->
        <h3>Notre Expertise Locale</h3>
        
        <!-- Financement -->
        <div class="bg-yellow-50 p-6 rounded-lg">
            <h4>Financement et aides</h4>
        </div>
        
        <!-- CTA Devis -->
        <div class="bg-gradient-to-r from-blue-50 to-green-50 p-6 rounded-lg">
            <h4>Besoin d'un devis?</h4>
            <a href="[FORM_URL]">Demande de devis</a>
        </div>
        
        <!-- Informations pratiques -->
        <div class="bg-gray-50 p-6 rounded-lg">
            <h4>Informations Pratiques</h4>
            <!-- 3 points clés -->
        </div>
        
        <!-- Partage social -->
        <div class="mt-8 pt-6 border-t">
            <h4>Partager ce service</h4>
            <!-- Facebook, WhatsApp, Email -->
        </div>
    </div>
</div>
```

## 🔄 Workflow Recommandé

1. **Créer des templates** pour vos services principaux
2. **Tester** avec quelques villes
3. **Générer en masse** pour toutes les villes
4. **Surveiller** l'utilisation et les performances
5. **Mettre à jour** les templates si nécessaire

## 🛠️ Maintenance

### Mise à jour d'un Template
- Modifier le template dans l'admin
- Les annonces existantes gardent leur contenu
- Les nouvelles annonces utilisent le template mis à jour

### Désactivation d'un Template
- Le template devient inactif
- Les annonces existantes restent publiées
- Aucune nouvelle annonce ne peut être créée

### Suppression d'un Template
- Les annonces existantes gardent leur contenu
- La référence au template est supprimée
- Les annonces restent fonctionnelles

## 📈 Statistiques

Le système fournit:
- Nombre total de templates
- Templates actifs/inactifs
- Nombre d'annonces créées
- Utilisation moyenne par template
- Détail des annonces par template

## 🎯 Bonnes Pratiques

1. **Créer un template par service** principal
2. **Tester** avec quelques villes avant la génération en masse
3. **Personnaliser** les instructions IA si nécessaire
4. **Surveiller** la qualité du contenu généré
5. **Mettre à jour** régulièrement les templates
6. **Désactiver** les templates obsolètes plutôt que les supprimer

---

*Ce système révolutionne la création d'annonces en permettant de générer du contenu de qualité professionnelle à grande échelle tout en maintenant la personnalisation locale.*
