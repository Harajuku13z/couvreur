# 🤖 Guide Automatisation SEO - Configuration & Fonctionnement

## 📍 Accès

**Admin** : https://couvreur-chevigny-saint-sauveur.fr/admin/seo-automation

**Mot de passe** : `elizo` (si demandé)

---

## 🎯 Comment ça fonctionne

### Principe de base :

1. **Planification intelligente** :
   - Articles répartis sur 12h (période de travail)
   - Intervalles calculés automatiquement
   - Rotation équitable entre villes favorites

2. **Sélection mots-clés** :
   - Sélection **aléatoire** dans votre liste
   - Évite les mots-clés utilisés récemment (14 jours)
   - **Variation maximale** pour éviter duplication

3. **Publication horaire** :
   - Articles publiés aux heures planifiées
   - `published_at` = heure planifiée (pas now())
   - Respecte votre configuration

4. **Indexation automatique** :
   - Demande d'indexation Google automatique
   - Logs visibles avec statut explicite
   - Vérification statut après 2 secondes

---

## ⚙️ CONFIGURATION (Personnalisable)

### 1. Heure de début

**Par défaut** : 08:00

**Configurable** : Oui, dans l'admin

**Impact** :
- Articles commencent à partir de cette heure
- Période de travail : 12h après (ex: 08h00 → 20h00)

**Comment modifier** :
1. Aller sur `/admin/seo-automation`
2. Section "Configuration Horaires"
3. Champ "Heure de publication"
4. Choisir heure (format 24h : 00:00 à 23:59)
5. Sauvegarder

### 2. Nombre d'articles par jour

**Par défaut** : 5 articles par ville

**Configurable** : Oui, de 1 à 50

**Calcul automatique** :
```
Total articles/jour = Articles par ville × Nombre villes favorites

Exemples :
- 5 articles × 2 villes = 10 articles/jour
- 3 articles × 5 villes = 15 articles/jour
- 10 articles × 1 ville = 10 articles/jour
```

**Comment modifier** :
1. Admin SEO Automation
2. Section "Configuration"
3. Champ "Articles par jour par ville"
4. Mettre 1-50 (recommandé : 3-5 max)
5. Sauvegarder

**⚠️ ATTENTION** :
- Ne pas dépasser 5 articles/jour par ville
- Google pénalise la surproduction
- Privilégier qualité sur quantité

### 3. Intervalle entre articles

**Calculé automatiquement** :
```
Intervalle (minutes) = 720 minutes (12h) ÷ Total articles/jour

Exemples :
- 10 articles/jour = 72 minutes d'intervalle
- 15 articles/jour = 48 minutes d'intervalle
- 30 articles/jour = 24 minutes d'intervalle
```

**Minimum** : 5 minutes (sécurité anti-spam)

**Pas configurable** : Automatique pour répartition optimale

### 4. Mots-clés utilisés

**Sélection** : Aléatoire dans votre liste

**Avantages** :
- ✅ Variation maximale (anti-duplication)
- ✅ Couverture large sémantique
- ✅ Évite cannibalisation mots-clés

**Filtrage** :
- Exclut mots-clés utilisés < 14 jours pour même ville
- Si tous utilisés récemment → Prend au hasard

**Comment configurer vos mots-clés** :
1. Admin `/admin/keywords` ou dans SEO Automation
2. Section "Mots-clés personnalisés"
3. Ajouter 20-30 mots-clés pertinents
4. Sauvegarder

**Exemples mots-clés recommandés** :
- "rénovation toiture"
- "couverture zinc"
- "isolation combles"
- "ravalement façade"
- "charpente traditionnelle"
- "zinguerie moderne"
- etc.

### 5. Indexation Google automatique

**Activée par défaut** : Oui

**Comportement** :
1. Article créé et publié
2. URL générée : `/blog/{slug}`
3. Demande indexation envoyée à Google API
4. Attente 2 secondes
5. Vérification statut (indexé ou non)
6. Enregistrement dans logs avec statut

**Logs visibles** :
- Dans `/admin/seo-automation`
- Colonne "Statut" : published / indexed
- Métadonnées : `index_requested`, `index_requested_at`

**Conditions pour indexation** :
- Google Search Console configuré
- Credentials JSON valides
- Compte service = propriétaire GSC

**Si échec indexation** :
- Article quand même publié
- Statut = "published" (pas "indexed")
- Log avec message d'erreur
- Vous pouvez réindexer manuellement

---

## 📅 EXEMPLE DE PLANIFICATION

### Configuration :
- Heure début : **08:00**
- Articles par ville : **5**
- Villes favorites : **2** (Paris, Lyon)
- Total articles/jour : **10**

### Résultat :
```
Période travail : 08:00 → 20:00 (12h)
Intervalle : 720 min ÷ 10 articles = 72 minutes

Horaires planifiés :
08:00 - Article Paris (mot-clé A)
09:12 - Article Lyon (mot-clé B)
10:24 - Article Paris (mot-clé C)
11:36 - Article Lyon (mot-clé D)
12:48 - Article Paris (mot-clé E)
14:00 - Article Lyon (mot-clé F)
15:12 - Article Paris (mot-clé G)
16:24 - Article Lyon (mot-clé H)
17:36 - Article Paris (mot-clé I)
18:48 - Article Lyon (mot-clé J)
```

**Notes** :
- Rotation entre villes
- Mot-clé différent à chaque fois (aléatoire)
- Intervalles réguliers
- Articles publiés à l'heure planifiée
- Indexation auto après chaque création

---

## 🔧 PARAMÈTRES AVANCÉS

### Mode d'exécution

**2 modes disponibles** :

#### A. Exécution directe (Recommandé)
**Avantages** :
- ✅ Fiable (pas besoin de queue worker)
- ✅ Rapide (exécution immédiate)
- ✅ Logs en temps réel

**Configuration** :
```
seo_automation_direct_execution = true
```

**Comment activer** :
1. Admin SEO Automation
2. Cocher "Exécution directe"
3. Sauvegarder

#### B. Via Queue (Avancé)
**Avantages** :
- Permet suivi jobs
- Gestion erreurs avancée

**Inconvénients** :
- ❌ Nécessite queue worker actif
- ❌ Plus complexe

**Configuration** :
```
seo_automation_direct_execution = false
```

**Commande worker** :
```bash
php artisan queue:work --queue=seo-automation
```

### Mode Test (Ignorer quota)

**Utilité** : Tests sans limite

**Configuration** :
```
seo_automation_ignore_quota = true
```

**⚠️ ATTENTION** :
- Ne pas laisser activé en production !
- Permet création illimitée (spam Google)
- À utiliser seulement pour tests

**Comment activer** :
1. Admin SEO Automation
2. Cocher "Ignorer quota (mode test)"
3. Faire vos tests
4. **DÉCOCHER** avant de quitter !

---

## 📊 VOIR LES HORAIRES PLANIFIÉS

### Dans l'admin :

Section **"Horaires planifiés pour aujourd'hui"** affiche :

- ⏰ Heure prévue
- 🏙️ Ville concernée
- ✅ Créé / ⏳ En attente / ❌ Erreur
- 💬 Message si erreur

**Exemple** :
```
08:00 - Paris - ✅ Article créé
09:12 - Lyon - ✅ Article créé
10:24 - Paris - ⏳ En attente (pas encore l'heure)
11:36 - Lyon - ⏳ En attente
...
```

---

## 🎯 VÉRIFIER QUE ÇA FONCTIONNE

### 1. Vérifier les logs

```bash
# Sur le serveur
tail -f storage/logs/laravel.log | grep "SeoArticleScheduler\|RunSeoAutomations"

# Vous devriez voir :
# - "Création article planifié"
# - "Heure planifiée : XX:XX"
# - "Article créé avec published_at : ..."
```

### 2. Vérifier dans la base de données

```bash
php artisan tinker
>>> $today = App\Models\Article::whereDate('published_at', today())->get();
>>> foreach ($today as $article) {
>>>     echo "{$article->id} - {$article->title} - Published: {$article->published_at->format('H:i')}\n";
>>> }

# Vous devriez voir des heures réparties (08h00, 09h12, 10h24, etc.)
# PAS toutes à la même heure (12h34, 12h35, 12h36) !
```

### 3. Vérifier dans l'admin

1. Aller sur `/admin/seo-automation`
2. Section "Logs" : Voir dernières créations
3. Vérifier colonne "Créé le"
4. Les heures doivent être réparties selon votre config

### 4. Vérifier indexation automatique

1. Dans les logs SEO Automation
2. Métadonnées d'un log récent
3. Chercher `index_requested`
4. Doit être `true`
5. Chercher `index_requested_at`
6. Doit avoir un timestamp

---

## 🔄 COMPORTEMENTS NORMAUX

### 1. Mots-clés différents à chaque fois : NORMAL ✅

**C'est voulu** pour :
- Éviter sur-optimisation
- Diversifier sémantique
- Éviter duplication contenu
- Couvrir large spectre requêtes

**Si vous voulez FORCER un mot-clé** :
1. Génération manuelle dans admin
2. Champ "Mot-clé personnalisé"
3. Entrer votre mot-clé
4. Générer

### 2. 5 articles par ville : CONFIGURABLE ✅

**Par défaut** : 5

**Modifiable** : Oui, 1-50

**Recommandation** :
- 3-5 max pour qualité
- Ne pas dépasser 10 (risque spam)

### 3. Indexation automatique : NORMAL ✅

**C'est une FEATURE** !

**Avantages** :
- Pas besoin d'action manuelle
- Indexation immédiate
- Google notifié instantanément
- Gain de temps massif

**Si vous voulez DÉSACTIVER** :
→ Pas possible directement (feature intégrée)
→ Mais vous pouvez ne pas configurer Google API

---

## 🐛 PROBLÈMES FRÉQUENTS

### Problème 1 : "Horaires pas respectés"

**Symptômes** :
- Tous les articles créés à la même heure
- Ex: 12:34, 12:35, 12:36 au lieu de 08:00, 09:12, 10:24

**Causes** :
1. ❌ Cron pas configuré (articles créés manuellement)
2. ❌ Mode "ignorer quota" activé
3. ❌ Exécution manuelle forcée (--force)

**Solutions** :
1. Vérifier cron Laravel actif :
   ```bash
   # Vérifier que cette ligne est dans crontab :
   * * * * * cd /path && php artisan schedule:run >> /dev/null 2>&1
   ```

2. Désactiver "Ignorer quota" dans admin

3. Ne pas utiliser `--force` en production

4. Vérifier logs :
   ```bash
   grep "published_at" storage/logs/laravel.log | tail -20
   # Doit montrer des heures planifiées différentes
   ```

### Problème 2 : "Pas de demande d'indexation"

**Symptômes** :
- Articles créés mais pas de mention "indexation"
- Statut = "published" jamais "indexed"

**Causes** :
1. ❌ Google Search Console non configuré
2. ❌ Credentials JSON invalides
3. ❌ Compte service pas propriétaire GSC

**Solutions** :
1. Vérifier dans `/admin/indexation` :
   - Credentials configurés ?
   - Test connexion = ✅ ?

2. Vérifier compte service dans GSC :
   - Email service account ajouté ?
   - Rôle = Propriétaire ?

3. Vérifier logs :
   ```bash
   grep "index_requested" storage/logs/laravel.log | tail -20
   ```

### Problème 3 : "Trop d'articles créés"

**Symptômes** :
- Plus de X articles/jour que configuré
- Quota dépassé

**Causes** :
1. ❌ "Ignorer quota" activé
2. ❌ Exécutions multiples manuelles

**Solutions** :
1. Désactiver "Ignorer quota"
2. Ne lancer manuellement qu'une fois/jour max
3. Laisser le cron automatique faire le travail

---

## 📝 CONFIGURATION RECOMMANDÉE

### Pour qualité maximale :

```
Heure début : 08:00
Articles par ville : 3-5 max
Villes favorites : 5-10 max
Mode exécution : Directe
Ignorer quota : OFF
Personnalisation IA : ON
SerpAPI : ON (si clé disponible)
```

### Calcul automatique :
- 5 articles × 5 villes = **25 articles/jour**
- Intervalle : 720 min ÷ 25 = **29 minutes**
- Période : 08:00 → 20:00

### Timeline exemple :
```
08:00 - Ville A
08:29 - Ville B
08:58 - Ville C
09:27 - Ville D
09:56 - Ville E
10:25 - Ville A (2ème article)
10:54 - Ville B (2ème article)
...
```

---

## 🚀 DÉMARRAGE RAPIDE

### Configuration initiale (15 min) :

```bash
# 1. Vérifier cron Laravel actif
crontab -l | grep "schedule:run"
# Doit afficher : * * * * * cd /path && php artisan schedule:run

# 2. Configurer mots-clés
# Via admin /admin/keywords : Ajouter 20-30 mots-clés

# 3. Marquer villes favorites
# Via /admin/cities : Cocher "Favorite" sur 5-10 villes max

# 4. Configurer automatisation
# Via /admin/seo-automation :
# - Heure : 08:00
# - Articles/jour : 5
# - Exécution directe : ON
# - Ignorer quota : OFF
# - Sauvegarder

# 5. Tester
php artisan seo:run-automations --force
# Vérifier qu'un article est créé

# 6. Activer automatisation
# Dans admin : Toggle "Automatisation SEO" à ON

# 7. Vérifier logs quotidiennement
tail -f storage/logs/laravel.log | grep "SEO"
```

---

## 📊 VÉRIFICATIONS QUOTIDIENNES

### Chaque matin (2 min) :

1. **Admin SEO Automation** :
   - Voir logs dernières 24h
   - Vérifier nombre articles créés
   - Vérifier pas d'erreurs

2. **Horaires planifiés** :
   - Section dédiée dans admin
   - Vérifier articles créés aux bonnes heures
   - Si manques : Identifier pourquoi (logs)

3. **Indexation** :
   - Vérifier statut articles récents
   - Filtrer "indexed" vs "published"
   - Si beaucoup "published" : Problème indexation API

---

## 💡 OPTIMISATIONS

### 1. Nombre d'articles optimal

**Recommandation Google** :
- Max 5-10 nouveaux contenus/jour
- Au-delà = risque spam/pénalité

**Formule optimale** :
```
Si 1 ville favorite : 5 articles/jour = OK
Si 2 villes : 3 articles/ville = 6 total = OK
Si 5 villes : 2 articles/ville = 10 total = Limite
Si 10 villes : 1 article/ville = 10 total = Limite
```

**Règle d'or** :
- Total ≤ 10 articles/jour
- Minimum 30 min d'intervalle
- Qualité > Quantité

### 2. Mots-clés stratégiques

**Nombre optimal** : 30-50 mots-clés

**Éviter** :
- ❌ Mots-clés trop génériques ("couvreur")
- ❌ Mots-clés hors sujet
- ❌ Duplication ("toiture" et "toitures")

**Privilégier** :
- ✅ Longue traîne ("rénovation toiture ardoise")
- ✅ Intent clair ("prix couverture zinc")
- ✅ Local ("couvreur + ville")
- ✅ Variations sémantiques

### 3. Villes favorites sélection

**Critères de choix** :
- ✅ Villes avec fort potentiel (population, recherches)
- ✅ Zones géographiques différentes (diversité)
- ✅ Villes où vous intervenez réellement
- ❌ Pas 100 villes (surcharge inutile)

**Nombre optimal** : 5-10 villes

---

## 🎯 COMMANDES UTILES

### Tester l'automatisation

```bash
# Voir si c'est le bon moment
php artisan seo:run-automations
# Si message "Pas encore le moment" = OK, système attend

# Forcer création immédiate (test)
php artisan seo:run-automations --force
# Crée 1 article immédiatement

# Voir stats planification
php artisan tinker
>>> $scheduler = app(\App\Services\SeoArticleScheduler::class);
>>> $stats = $scheduler->getScheduleStats();
>>> print_r($stats);
```

### Voir horaires planifiés

```bash
php artisan tinker
>>> $scheduler = app(\App\Services\SeoArticleScheduler::class);
>>> $times = $scheduler->getScheduledTimes();
>>> foreach ($times as $t) {
>>>     echo "{$t['time']} - {$t['city']['name']} - " . ($t['article_created'] ? 'Créé ✅' : 'En attente ⏳') . "\n";
>>> }
```

### Vérifier articles aujourd'hui

```bash
php artisan tinker
>>> $articles = App\Models\Article::whereDate('published_at', today())->get();
>>> foreach ($articles as $article) {
>>>     echo "{$article->published_at->format('H:i')} - {$article->title}\n";
>>> }
# Les heures doivent être DIFFÉRENTES et RÉPARTIES
```

---

## ✅ CHECKLIST CONFIGURATION

- [ ] Cron Laravel actif (`schedule:run` chaque minute)
- [ ] Mots-clés configurés (20-30 minimum)
- [ ] Villes favorites (5-10 recommandé)
- [ ] Heure début définie (ex: 08:00)
- [ ] Articles/jour configuré (3-5 recommandé)
- [ ] Exécution directe activée
- [ ] Ignorer quota DÉSACTIVÉ
- [ ] Google Search Console configuré
- [ ] Automatisation SEO activée (toggle ON)
- [ ] Test création réussi (`--force`)
- [ ] Logs sans erreur
- [ ] Horaires respectés (vérification BDD)
- [ ] Indexation automatique fonctionne

---

## 🆘 SI ÇA NE FONCTIONNE PAS

### Diagnostic complet :

```bash
# 1. Vérifier configuration
php artisan seo:diagnose

# 2. Vérifier horaires
php artisan tinker
>>> App\Models\Setting::get('seo_automation_time');
>>> App\Models\Setting::get('seo_automation_articles_per_day');
>>> App\Models\Setting::get('seo_automation_enabled');

# 3. Vérifier cron
php artisan schedule:list
# Doit afficher : seo:run-automations

# 4. Tester manuellement
php artisan seo:run-automations --force

# 5. Vérifier résultat
>>> $last = App\Models\Article::latest('published_at')->first();
>>> echo "Published at: " . $last->published_at->format('Y-m-d H:i:s');
>>> echo "Created at: " . $last->created_at->format('Y-m-d H:i:s');
# published_at doit être l'heure planifiée !
```

---

## 📞 RÉSUMÉ ULTRA-RAPIDE

**Pour que tout fonctionne** :

1. ✅ Cron actif
2. ✅ Mots-clés configurés (30+)
3. ✅ Villes favorites (5-10)
4. ✅ Configuration horaires sauvegardée
5. ✅ Google API configuré
6. ✅ Automatisation activée (toggle)
7. ✅ Ignorer quota = OFF
8. ✅ Exécution directe = ON

**Vérifier** :
- Logs quotidiens
- Horaires respectés (published_at)
- Indexation auto (logs avec index_requested = true)
- Pas d'erreurs

**Résultat** :
- X articles/jour créés (selon config)
- Aux heures planifiées (répartis sur 12h)
- Mots-clés variés (aléatoire)
- Villes en rotation
- Indexation Google automatique

---

**🎉 Système déjà très avancé - Il suffit de bien le configurer !**

*Guide créé le 2025-11-19*

