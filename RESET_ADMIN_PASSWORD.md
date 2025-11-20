# 🔑 Réinitialiser le Mot de Passe Admin

## 🎯 Solution Rapide (2 méthodes)

### Méthode 1 : Via Commande Artisan (Recommandée)

```bash
# Sur votre serveur
cd /path/to/couvreur

# Réinitialiser avec mot de passe personnalisé
php artisan admin:reset-password --username=admin --password=VotreNouveauMotDePasse --show

# Ou générer un mot de passe automatique
php artisan admin:reset-password --show
```

**Résultat** :
```
✅ Mot de passe admin réinitialisé avec succès !

📋 Identifiants :
┌─────────────────────┬──────────────────────┐
│ Champ               │ Valeur               │
├─────────────────────┼──────────────────────┤
│ Nom d'utilisateur   │ admin                 │
│ Mot de passe        │ VotreNouveauMotDePasse│
└─────────────────────┴──────────────────────┘

🔗 URL de connexion : /admin/login
```

---

### Méthode 2 : Via Tinker (Alternative)

```bash
php artisan tinker

# Définir nouveau mot de passe
App\Models\Setting::set('admin_username', 'admin');
App\Models\Setting::set('admin_password', 'VotreNouveauMotDePasse');

# Vérifier
App\Models\Setting::get('admin_username');
App\Models\Setting::get('admin_password');

exit
```

---

### Méthode 3 : Identifiants par Défaut

**Si rien n'est configuré dans Settings**, les identifiants par défaut sont :

- **Username** : `admin`
- **Password** : `admin`

**Essayez d'abord** : https://couvreur-chevigny-saint-sauveur.fr/admin/login
- Username : `admin`
- Password : `admin`

---

## 🔍 Vérifier les Identifiants Actuels

```bash
php artisan tinker

# Voir username actuel
App\Models\Setting::get('admin_username', 'admin');

# Voir password actuel (peut être hashé)
$pwd = App\Models\Setting::get('admin_password', 'admin');
echo $pwd;

exit
```

---

## 🛠️ Si Aucune Méthode Ne Fonctionne

### Option A : Modifier le Code Directement

Éditez `app/Http/Controllers/AdminController.php` ligne 43-44 :

```php
$adminUsername = 'admin';
$adminPassword = 'VotreNouveauMotDePasse';  // ← Changez ici
```

**Puis** :
```bash
git add app/Http/Controllers/AdminController.php
git commit -m "Fix: Reset admin password"
git push origin main
```

### Option B : Réinitialiser via Base de Données

```sql
-- Se connecter à MySQL
mysql -u votre_user -p votre_database

-- Voir les settings actuels
SELECT * FROM settings WHERE `key` IN ('admin_username', 'admin_password');

-- Réinitialiser
UPDATE settings SET `value` = 'admin' WHERE `key` = 'admin_username';
UPDATE settings SET `value` = 'VotreNouveauMotDePasse' WHERE `key` = 'admin_password';

-- Ou créer si n'existe pas
INSERT INTO settings (`key`, `value`) VALUES ('admin_username', 'admin') ON DUPLICATE KEY UPDATE `value` = 'admin';
INSERT INTO settings (`key`, `value`) VALUES ('admin_password', 'VotreNouveauMotDePasse') ON DUPLICATE KEY UPDATE `value` = 'VotreNouveauMotDePasse';
```

---

## ✅ Test de Connexion

1. Ouvrir : https://couvreur-chevigny-saint-sauveur.fr/admin/login
2. Entrer :
   - Username : `admin` (ou celui configuré)
   - Password : Votre nouveau mot de passe
3. Cliquer "Se connecter"
4. ✅ Devrait rediriger vers `/admin/dashboard`

---

## 🔒 Sécurité

**Recommandations** :
- ✅ Utiliser un mot de passe fort (12+ caractères, majuscules, chiffres, symboles)
- ✅ Ne pas partager le mot de passe
- ✅ Changer régulièrement
- ✅ Utiliser un gestionnaire de mots de passe

**Exemple de mot de passe fort** :
```
Admin2025!Secure
```

---

## 📞 En Cas de Problème

Si aucune méthode ne fonctionne :

1. **Vérifier les logs** :
   ```bash
   tail -f storage/logs/laravel.log | grep "connexion\|admin"
   ```

2. **Vérifier la session** :
   - Vider les cookies du navigateur
   - Tester en navigation privée

3. **Vérifier les routes** :
   ```bash
   php artisan route:list | grep admin.login
   ```

---

## 🎯 Résumé Rapide

**Solution la plus simple** :
```bash
php artisan admin:reset-password --password=VotreMotDePasse --show
```

**Puis connectez-vous avec** :
- Username : `admin`
- Password : `VotreMotDePasse`

**URL** : https://couvreur-chevigny-saint-sauveur.fr/admin/login

---

✅ **Commande créée** : `php artisan admin:reset-password`

