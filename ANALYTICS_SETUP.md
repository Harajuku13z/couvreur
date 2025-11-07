# Configuration Google Analytics avec Spatie Laravel Analytics

## 📋 Prérequis

1. Un compte Google Analytics avec une propriété configurée
2. Un compte de service Google Cloud avec les permissions Analytics

## 🔧 Étapes de configuration

### 1. Créer un compte de service Google Cloud

1. Allez sur [Google Cloud Console](https://console.cloud.google.com/)
2. Créez un nouveau projet ou sélectionnez un projet existant
3. Activez l'API **Google Analytics Reporting API**
4. Créez un compte de service :
   - Allez dans **IAM & Admin > Service Accounts**
   - Cliquez sur **Create Service Account**
   - Donnez un nom (ex: "analytics-service")
   - Cliquez sur **Create and Continue**
   - Attribuez le rôle **Viewer** ou **Analytics Viewer**
   - Cliquez sur **Done**

### 2. Télécharger les credentials JSON

1. Dans la liste des comptes de service, cliquez sur celui que vous venez de créer
2. Allez dans l'onglet **Keys**
3. Cliquez sur **Add Key > Create new key**
4. Sélectionnez **JSON** et cliquez sur **Create**
5. Le fichier JSON sera téléchargé

### 3. Configurer les permissions dans Google Analytics

1. Allez sur [Google Analytics](https://analytics.google.com/)
2. Sélectionnez votre propriété
3. Allez dans **Admin > Property Access Management**
4. Cliquez sur **+** pour ajouter un utilisateur
5. Entrez l'email du compte de service (format: `nom@projet.iam.gserviceaccount.com`)
6. Donnez les permissions **Viewer**
7. Cliquez sur **Add**

### 4. Récupérer le View ID

1. Dans Google Analytics, allez dans **Admin > View Settings**
2. Notez le **View ID** (format: `123456789`)

### 5. Configurer Laravel

1. Créez le dossier `storage/app/analytics/` :
   ```bash
   mkdir -p storage/app/analytics
   ```

2. Placez le fichier JSON téléchargé dans `storage/app/analytics/service-account-credentials.json`

3. Ajoutez dans votre fichier `.env` :
   ```env
   ANALYTICS_VIEW_ID=123456789
   ```

4. Vérifiez que le fichier `config/analytics.php` existe et contient :
   ```php
   'view_id' => env('ANALYTICS_VIEW_ID'),
   'service_account_credentials_json' => storage_path('app/analytics/service-account-credentials.json'),
   ```

### 6. Tester la configuration

1. Allez sur `/admin/visits`
2. Si tout est bien configuré, vous devriez voir les statistiques de visites

## 🐛 Dépannage

### Erreur "Permission denied"
- Vérifiez que le compte de service a bien les permissions dans Google Analytics
- Vérifiez que l'API Google Analytics Reporting est activée

### Erreur "View ID not found"
- Vérifiez que le View ID dans `.env` est correct
- Vérifiez que le compte de service a accès à cette vue

### Erreur "Credentials not found"
- Vérifiez que le fichier JSON est bien dans `storage/app/analytics/service-account-credentials.json`
- Vérifiez les permissions du fichier (lecture)

## 📊 Utilisation

Une fois configuré, vous pouvez :
- Voir les statistiques de visites dans `/admin/visits`
- Les appels téléphoniques sont automatiquement trackés dans Google Analytics
- Les événements sont envoyés avec les métadonnées (page source, ville, pays)

