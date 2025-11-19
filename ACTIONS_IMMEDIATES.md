# ⚡ ACTIONS IMMÉDIATES - COPIER-COLLER

**Temps nécessaire** : 15-30 minutes
**Impact** : CRITIQUE pour récupération Google

---

## 🔴 ÉTAPE 1 : DÉPLOYER SUR PRODUCTION (5 min)

```bash
# SSH sur votre serveur
ssh votre-serveur

# Aller dans le dossier de l'application
cd /path/to/couvreur-chevigny-saint-sauveur

# Pull les derniers changements
git pull origin main

# Installer dépendances (si nouvelles)
composer install --optimize-autoloader --no-dev

# Vider tous les caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan optimize

echo "✅ Déploiement terminé !"
```

---

## 🔴 ÉTAPE 2 : CONFIGURER LE DOMAINE (2 min)

```bash
# Ouvrir PHP artisan tinker
php artisan tinker

# Exécuter ces commandes dans tinker :
App\Models\Setting::set('site_url', 'https://couvreur-chevigny-saint-sauveur.fr');
App\Models\Setting::get('site_url');  # Vérifier que c'est bien enregistré
exit
```

**Alternative via l'admin web** :
1. Aller sur `https://couvreur-chevigny-saint-sauveur.fr/admin`
2. Configuration ou Settings
3. Chercher `site_url`
4. Mettre `https://couvreur-chevigny-saint-sauveur.fr`
5. Sauvegarder

---

## 🔴 ÉTAPE 3 : DIAGNOSTIC & CORRECTION AUTO (2 min)

```bash
# Lancer le diagnostic avec auto-correction
php artisan seo:diagnose --fix

# ✅ Vous devriez voir :
# - ✅ site_url configuré
# - ✅ sitemap.xml existe
# - ✅ robots.txt existe
# - ✅ Credentials configurés
# etc.

# Si des problèmes sont signalés, notez-les
```

---

## 🔴 ÉTAPE 4 : RÉGÉNÉRER SITEMAP AVEC BON DOMAINE (1 min)

```bash
# Régénérer le sitemap
php artisan sitemap:generate-daily

# Vérifier que le sitemap contient le BON domaine
curl https://couvreur-chevigny-saint-sauveur.fr/sitemap.xml | head -30

# ✅ Vous DEVEZ voir des URLs comme :
# <loc>https://couvreur-chevigny-saint-sauveur.fr/</loc>
# <loc>https://couvreur-chevigny-saint-sauveur.fr/services/toiture</loc>
# etc.

# ❌ Si vous voyez un AUTRE domaine = PROBLÈME
# → Vérifier étape 2 (site_url) et recommencer
```

---

## 🔴 ÉTAPE 5 : GOOGLE SEARCH CONSOLE (10 min)

### A. Soumettre le sitemap

1. Aller sur https://search.google.com/search-console
2. Sélectionner votre propriété (couvreur-chevigny-saint-sauveur.fr)
3. Menu gauche : **Sitemaps**
4. Supprimer l'ancien sitemap s'il existe
5. Ajouter nouveau sitemap : `https://couvreur-chevigny-saint-sauveur.fr/sitemap.xml`
6. **Envoyer**
7. Attendre 1-2 min puis rafraîchir
8. ✅ Statut doit être "Réussite" ou "En attente"

### B. Demander indexation pages clés (10 min)

**Pour CHAQUE page ci-dessous :**

1. Copier l'URL complète
2. Aller dans GSC : **Inspection d'URL** (en haut)
3. Coller l'URL
4. Appuyer Entrée
5. Attendre résultat
6. Cliquer **"Demander une indexation"**
7. Patienter 1-2 min
8. ✅ "Demande d'indexation envoyée"
9. Passer à la suivante

**URLs prioritaires (Top 20) :**
```
https://couvreur-chevigny-saint-sauveur.fr/
https://couvreur-chevigny-saint-sauveur.fr/services
https://couvreur-chevigny-saint-sauveur.fr/services/renovation-toiture
https://couvreur-chevigny-saint-sauveur.fr/services/couverture
https://couvreur-chevigny-saint-sauveur.fr/services/facade
https://couvreur-chevigny-saint-sauveur.fr/services/isolation
https://couvreur-chevigny-saint-sauveur.fr/services/charpente
https://couvreur-chevigny-saint-sauveur.fr/blog
https://couvreur-chevigny-saint-sauveur.fr/simulateur
https://couvreur-chevigny-saint-sauveur.fr/contact
https://couvreur-chevigny-saint-sauveur.fr/avis
https://couvreur-chevigny-saint-sauveur.fr/nos-realisations
# + Top 8 articles blog ou annonces avec plus de contenu
```

---

## 🔴 ÉTAPE 6 : ACTIVER OPTIMISATIONS (2 min)

### Via l'admin web :

1. **Aller sur** : `https://couvreur-chevigny-saint-sauveur.fr/admin/seo-automation`

2. **Vérifier ces paramètres** :
   - ✅ Automatisation SEO = **Activée**
   - ✅ Articles par jour = **3-5** (pas plus !)
   - ✅ Personnalisation IA = **Activée**
   - ✅ SerpAPI = **Activée**
   - ✅ Google Indexing = **Configuré**

3. **Sauvegarder**

---

## 🔴 ÉTAPE 7 : ANALYSER QUALITÉ CONTENU (3 min)

```bash
# Lancer l'analyse
php artisan seo:analyze-quality

# ✅ Noter les statistiques :
# - Combien d'articles < 500 mots ? (à enrichir ou supprimer)
# - Combien de titres dupliqués ? (à rendre unique)
# - Longueur moyenne ? (objectif : 1500+ mots)
```

**Si beaucoup de contenu < 500 mots** :

```bash
# Dans tinker
php artisan tinker

# Marquer comme draft les contenus très courts (à vérifier manuellement)
App\Models\Ad::whereRaw('LENGTH(content_html) < 3000')->update(['status' => 'draft']);

# Vérifier combien ont été modifiés
App\Models\Ad::where('status', 'draft')->count();
exit
```

---

## 📊 ÉTAPE 8 : SUIVRE RÉSULTATS (Quotidien)

### Google Search Console :

**Quotidiennement (5 min/jour) :**

1. Vue d'ensemble : Noter **impressions du jour**
2. Performances > Derniers 7 jours :
   - Impressions (devrait augmenter)
   - Clics (devrait suivre)
   - Position moyenne (devrait baisser)
3. Couverture :
   - Pages indexées (devrait augmenter)
   - Pages exclues (devrait diminuer)

**Créer un tableau de suivi :**
| Date | Impressions | Clics | Pages indexées | Position moy. |
|------|-------------|-------|----------------|---------------|
| J+0  | -           | -     | -              | -             |
| J+1  | ...         | ...   | ...            | ...           |

### Google Analytics :

- Utilisateurs organiques
- Pages vues
- Taux de rebond
- Conversions (formulaires)

---

## ⏰ CALENDRIER DES ACTIONS

### AUJOURD'HUI (Jour 0) :
- [x] Déployer corrections
- [x] Configurer site_url
- [x] Diagnostic --fix
- [x] Régénérer sitemap
- [ ] Soumettre GSC
- [ ] Indexer Top 20

### JOUR 1 :
- [ ] Vérifier sitemap accepté GSC
- [ ] Configurer robots.txt si pas fait
- [ ] Activer optimisations admin
- [ ] Analyser qualité contenu
- [ ] Nettoyer contenus < 500 mots

### JOUR 2-7 :
- [ ] Surveiller impressions GSC (devrait démarrer J+3-5)
- [ ] Enrichir Top 10 articles
- [ ] Créer 1 page pilier premium
- [ ] Configurer simulateur services
- [ ] Promouvoir simulateur homepage

### SEMAINE 2 :
- [ ] Objectif : 500-2000 impressions/jour
- [ ] Objectif : 10-30 visites/jour
- [ ] Optimiser Top 10 pages qui rankent
- [ ] Créer contenu frais (3-5 articles/semaine)

### MOIS 1 :
- [ ] Objectif : 5000-10000 impressions/jour
- [ ] Objectif : 50-150 visites/jour
- [ ] Stratégie backlinks locaux
- [ ] Audit complet et ajustements

---

## 🆘 EN CAS DE PROBLÈME

### Le sitemap contient encore le mauvais domaine :

```bash
# 1. Vérifier la config
php artisan tinker
>>> App\Models\Setting::get('site_url');
>>> config('app.url');

# 2. Si c'est toujours incorrect, forcer :
>>> App\Models\Setting::set('site_url', 'https://couvreur-chevigny-saint-sauveur.fr');
>>> App\Models\Setting::clearCache();
>>> exit

# 3. Régénérer
php artisan config:clear
php artisan cache:clear
php artisan sitemap:generate-daily

# 4. Re-vérifier
curl https://couvreur-chevigny-saint-sauveur.fr/sitemap.xml | head -30
```

### Les impressions ne remontent pas après 7 jours :

1. Vérifier sitemap accepté dans GSC (aucune erreur)
2. Vérifier pages indexées (doit augmenter)
3. Demander indexation de plus de pages (50-100)
4. Créer du contenu frais de HAUTE qualité
5. Vérifier pas de pénalité manuelle (GSC > Actions manuelles)

### Pas accès au serveur SSH :

- Utiliser l'admin web pour tout :
  - `/admin/seo-automation` : Configuration
  - `/admin/indexation` : Sitemap + Indexation
  - `/admin/simulator` : Simulateur
  - Outils de diagnostic disponibles dans admin

---

## ✅ VALIDATION FINALE

**Avant de considérer que c'est fait, vérifier** :

```bash
# 1. Sitemap correct
curl https://couvreur-chevigny-saint-sauveur.fr/sitemap.xml | grep -o "https://couvreur-chevigny-saint-sauveur.fr" | head -5
# ✅ Doit afficher votre domaine 5 fois

# 2. Robots.txt présent
curl https://couvreur-chevigny-saint-sauveur.fr/robots.txt
# ✅ Doit afficher le contenu du robots.txt

# 3. Sitemap soumis GSC
# ✅ Vérifier dans GSC > Sitemaps : statut "Réussite"

# 4. Indexation demandée
# ✅ Vérifier GSC > Couverture : "En attente" ou "Indexée" pour vos Top 20

# 5. Analytics fonctionne
# ✅ Vérifier Google Analytics : données temps réel actives
```

---

## 📞 CHECKLIST RÉCAPITULATIVE

Cochez au fur et à mesure :

**Déploiement :**
- [ ] Git pull fait
- [ ] Caches vidés
- [ ] Commandes optimize exécutées

**Configuration :**
- [ ] site_url = bon domaine
- [ ] APP_URL = bon domaine (.env)
- [ ] Diagnostic sans erreur critique

**Sitemap :**
- [ ] Régénéré avec bon domaine
- [ ] Accessible publiquement
- [ ] Soumis à Google Search Console
- [ ] Statut "Réussite" dans GSC

**Indexation :**
- [ ] Top 20 pages demandées
- [ ] Google Indexing API configuré
- [ ] Personnalisation IA activée

**Suivi :**
- [ ] GSC consulté quotidiennement
- [ ] Tableau de suivi créé
- [ ] Analytics vérifié

---

## 🎯 SI VOUS NE FAITES QU'UNE CHOSE

**Faites ceci MAINTENANT** :

1. `git pull origin main` sur le serveur
2. `php artisan config:clear && php artisan cache:clear`
3. Vérifier `site_url` dans l'admin = votre vrai domaine
4. `php artisan sitemap:generate-daily`
5. Soumettre sitemap.xml dans Google Search Console

**Puis attendez 3-7 jours et surveillez GSC.**

---

## 📈 SUIVI SIMPLIFIÉ

### Que regarder dans Google Search Console :

**Chaque matin** (5 min) :
1. Performances > Impressions hier : Noter le chiffre
2. Couverture > Pages indexées : Noter le nombre

**Objectif Jour 7** :
- Impressions : > 100/jour (début de récupération)
- Pages indexées : +10-20% vs Jour 0

**Si pas de mouvement après 7 jours** :
→ Demander indexation de 50-100 pages supplémentaires
→ Créer 2-3 pages piliers premium (3000+ mots)
→ Vérifier aucune erreur/pénalité GSC

---

**🚀 LANCEZ-VOUS !** Chaque heure compte pour la récupération.

*Guide créé le 2025-11-19*

