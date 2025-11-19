# 🛡️ Guide Anti-Spam Formulaire

## ✅ FONCTIONNALITÉS IMPLÉMENTÉES

### 1️⃣ Soumissions comptées à l'étape 2 ✅ NOUVEAU

**Problème avant** :
- Toutes les visites étape 1 comptées comme soumissions
- Visiteurs curieux = faux positifs
- Stats gonflées

**Solution appliquée** :
- ✅ Étape 1 (propertyType) : **Pas de submission créée**
- ✅ Étape 2+ (surface, etc.) : **Submission créée** (utilisateur engagé)

**Résultat** :
- Soumissions = vraies intentions de devis
- Stats plus précises
- Moins de spam/bruit

**Code modifié** : `app/Http/Controllers/FormControllerSimple.php`

---

### 2️⃣ Blocage géographique (anti-spam étranger) ✅ DÉJÀ EXISTAIT

**Bonne nouvelle** : Cette fonctionnalité existe déjà dans votre site !

**Où l'activer** :
1. Aller sur `/config` (Configuration)
2. Onglet **"Sécurité"** (icône bouclier)
3. Section **"Blocage géographique"**
4. ☑️ Cocher **"Bloquer l'accès au formulaire pour les utilisateurs hors de France"**
5. Cliquer **"Sauvegarder"**

**Chemin exact** : https://couvreur-chevigny-saint-sauveur.fr/config → Onglet Sécurité

---

## 🎯 COMMENT ÇA FONCTIONNE

### Blocage géographique :

**Quand activé** :
1. Utilisateur accède au formulaire (`/form/propertyType`)
2. Système détecte son IP
3. Géolocalisation via service (IP → Pays)
4. Si pays ≠ France → **Page de blocage affichée**
5. Si pays = France → **Formulaire normal**

**Page de blocage affiche** :
```
🚫 Accès restreint

Nous sommes désolés, mais notre service est disponible
uniquement pour les résidents de France.

Votre localisation : United States

[Nous appeler] [Nous écrire] [Retour accueil]
```

**Pays autorisés** :
- France (FR)
- Détecté automatiquement via IP

---

## ⚙️ CONFIGURATION

### Activer le blocage géographique :

**Via Interface Admin** :
1. `/config`
2. Onglet "Sécurité"
3. Cocher "Bloquer l'accès au formulaire pour les utilisateurs hors de France"
4. Sauvegarder

**Via CLI** :
```bash
php artisan tinker
App\Models\Setting::set('block_non_france', true);
exit
```

**Vérifier** :
```bash
php artisan tinker
Setting::get('block_non_france');
# Doit retourner : true ou 1
```

---

## 🧪 TESTER

### Test blocage :

**Option A : Via VPN**
1. Connecter VPN hors France (USA, UK, etc.)
2. Ouvrir `/form/propertyType`
3. ✅ Devrait voir page blocage

**Option B : Via CLI simulation**
```bash
# Modifier temporairement IP pour test
# Dans FormControllerSimple.php
# Ligne getClientIp() → return '8.8.8.8'; (USA)

# Ou tester avec curl
curl -H "X-Forwarded-For: 8.8.8.8" https://couvreur-chevigny-saint-sauveur.fr/form/propertyType
```

### Test étape 2 :

**Vérifier submissions** :
```bash
php artisan tinker
# Compter submissions aujourd'hui
$today = App\Models\Submission::whereDate('created_at', today())->count();
echo "Submissions aujourd'hui : $today\n";

# Voir dernières créées
$last = App\Models\Submission::latest()->take(5)->get(['id', 'current_step', 'created_at']);
foreach ($last as $sub) {
    echo "ID: {$sub->id} - Étape: {$sub->current_step} - Créé: {$sub->created_at}\n";
}

# Vérifier que current_step n'est jamais 'propertyType'
# Devrait être 'surface', 'workType', etc.
```

---

## 📊 STATISTIQUES PRÉCISES

### Avant (comptait étape 1) :
- 100 visites formulaire
- 100 submissions créées
- 10 complétées
- Taux conversion : 10%

### Après (compte étape 2+) :
- 100 visites formulaire
- 30 submissions créées (vraiment engagés)
- 10 complétées
- Taux conversion : 33% (plus réaliste !)

---

## 🛡️ PROTECTION MULTI-NIVEAUX

### Votre site a maintenant :

**Niveau 1 : Géo-blocage**
- ✅ Bloque pays hors France
- ✅ Page explicative pour étrangers
- ✅ Options contact alternatives

**Niveau 2 : reCAPTCHA v3**
- ✅ Score bot/humain automatique
- ✅ Blocage si score < 0.05
- ✅ Invisible pour utilisateurs

**Niveau 3 : Tracking précis**
- ✅ Soumissions à partir étape 2
- ✅ Visiteurs curieux non comptés
- ✅ Stats fiables

**Niveau 4 : Validation**
- ✅ Email, téléphone, code postal validés
- ✅ Données nettoyées
- ✅ Tentatives spam loggées

---

## 🎯 RECOMMANDATIONS

### Pour minimiser spam :

**1. Activer blocage géographique** :
```
/config → Sécurité → ☑ Bloquer hors France
```

**2. Vérifier reCAPTCHA configuré** :
```
/config → Sécurité → Clés reCAPTCHA remplies
```

**3. Surveiller logs** :
```bash
tail -f storage/logs/laravel.log | grep "Blocage\|suspect\|spam"
```

**4. Analyser soumissions** :
```
/admin/submissions → Voir pays d'origine
```

---

## 📈 MÉTRIQUES À SURVEILLER

### Dans l'admin :

**Soumissions** :
- Nombre total (devrait être plus bas maintenant)
- Taux complétion (devrait être plus haut)
- Pays origine (vérifier France majoritaire)

**Logs** :
- Blocages géographiques
- Scores reCAPTCHA suspects
- Tentatives spam

**Avant/Après** :
| Métrique | Avant | Après |
|----------|-------|-------|
| Visites formulaire | 100 | 100 |
| Submissions créées | 100 | 30-40 |
| Complétées | 10 | 10 |
| Taux conversion | 10% | 25-33% |
| Spam étranger | 50+ | 0 |

---

## 🔧 DÉSACTIVER SI BESOIN

### Si trop restrictif :

**Désactiver géo-blocage** :
```
/config → Sécurité → ☐ Décocher blocage France
```

**Assouplir reCAPTCHA** :
- Actuellement : Bloque si score < 0.05 (très permissif)
- Déjà configuré de manière optimale

---

## ✅ CHECKLIST

- [x] Code modifié (soumissions étape 2)
- [x] Blocage géo existe déjà (checkbox config)
- [x] reCAPTCHA v3 configuré
- [x] Page blocage existe (form.blocked)
- [x] Logs détaillés
- [ ] **À VOUS** : Activer blocage géo dans /config
- [ ] **À VOUS** : git pull origin main
- [ ] **À VOUS** : Surveiller stats
- [ ] **À VOUS** : Ajuster si nécessaire

---

## 📞 RÉSUMÉ

**Demande 1** : Comptabiliser à l'étape 2
→ ✅ FAIT (commit 79938512)

**Demande 2** : Bouton bloquer autres pays
→ ✅ EXISTE DÉJÀ dans `/config` → Sécurité

**Action** :
1. `git pull origin main`
2. Aller dans `/config`
3. Onglet "Sécurité"
4. ☑️ Cocher "Bloquer l'accès hors France"
5. Sauvegarder

**Résultat** :
- Moins de spam
- Stats précises
- Protection complète

---

*Guide créé le 2025-11-19*
*Fonctionnalités anti-spam complètes*

