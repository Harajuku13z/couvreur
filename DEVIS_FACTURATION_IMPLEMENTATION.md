# Système de Devis & Facturation avec IA (GROQ)

## ✅ Implémentation Complète

### 1. Base de Données
- ✅ **Migrations créées** :
  - `2025_01_30_000001_create_clients_table.php`
  - `2025_01_30_000002_create_devis_table.php`
  - `2025_01_30_000003_create_ligne_devis_table.php`
  - `2025_01_30_000004_create_factures_table.php`

### 2. Modèles Eloquent
- ✅ **Client** (`app/Models/Client.php`)
  - Relations : `devis()`, `factures()`
  - Accesseurs : `nom_complet`, `adresse_complete`
  
- ✅ **Devis** (`app/Models/Devis.php`)
  - Génération automatique de numéro (DEV-YYYY-XXXXX)
  - Relations : `client()`, `lignesDevis()`, `facture()`
  - Méthodes : `recalculateTotals()`, `validate()` (créer facture)
  - Statuts : Brouillon, En Attente, Accepté, Refusé

- ✅ **LigneDevis** (`app/Models/LigneDevis.php`)
  - Calcul automatique du total_ligne
  - Relation : `devis()`

- ✅ **Facture** (`app/Models/Facture.php`)
  - Génération automatique de numéro (FAC-YYYY-XXXXX)
  - Relations : `devis()`, `client()`
  - Méthodes : `markAsPaid()`, `isOverdue()`
  - Statuts : Impayée, Payée, Annulée

### 3. Services IA
- ✅ **GroqQuotationService** (`app/Services/GroqQuotationService.php`)
  - Génération de lignes de devis à partir d'une description libre
  - Utilise l'API GROQ via `AiService`
  - Parsing et validation du JSON retourné
  - Ajustement proportionnel si prix final estimé fourni

### 4. Services PDF
- ✅ **PdfService** (`app/Services/PdfService.php`)
  - Génération PDF pour devis et factures
  - Utilise `barryvdh/laravel-dompdf`
  - Stockage dans `storage/app/devis/` et `storage/app/factures/`

### 5. Mailables
- ✅ **DevisSent** (`app/Mail/DevisSent.php`)
  - Envoi de devis par email avec PDF joint
  
- ✅ **FactureSent** (`app/Mail/FactureSent.php`)
  - Envoi de facture par email avec PDF joint

### 6. Contrôleurs Admin
- ✅ **DevisController** (`app/Http/Controllers/Admin/DevisController.php`)
  - `index()` : Liste avec filtres
  - `create()` : Formulaire de création
  - `generateLines()` : Génération IA des lignes (AJAX)
  - `store()` : Sauvegarde du devis
  - `show()` : Affichage d'un devis
  - `edit()` : Formulaire d'édition
  - `update()` : Mise à jour
  - `validate()` : Valider devis et créer facture
  - `destroy()` : Suppression

- ✅ **FactureController** (`app/Http/Controllers/Admin/FactureController.php`)
  - `index()` : Liste avec filtres
  - `show()` : Affichage d'une facture
  - `markAsPaid()` : Marquer comme payée
  - `destroy()` : Suppression

- ✅ **ClientController** (`app/Http/Controllers/Admin/ClientController.php`)
  - `index()` : Liste des clients
  - `store()` : Création (AJAX)
  - `search()` : Recherche pour autocomplete

- ✅ **QuotationStatsController** (`app/Http/Controllers/Admin/QuotationStatsController.php`)
  - `dashboard()` : Tableau de bord avec statistiques
  - **Utilise les Cursors** pour :
    - CA Total (factures payées)
    - CA Potentiel (devis acceptés non payés)
    - Taux de conversion

### 7. Routes
- ✅ Routes ajoutées dans `routes/web.php` :
  - `/admin/quotations/dashboard` : Tableau de bord
  - `/admin/clients/*` : Gestion clients
  - `/admin/devis/*` : Gestion devis
  - `/admin/factures/*` : Gestion factures

### 8. Dépendances
- ✅ `barryvdh/laravel-dompdf` ajouté à `composer.json`

## 📋 À Faire (Vues Blade)

Les vues suivantes doivent être créées :

### Vues Admin
1. `resources/views/admin/devis/index.blade.php` - Liste des devis
2. `resources/views/admin/devis/create.blade.php` - Formulaire de création
3. `resources/views/admin/devis/edit.blade.php` - Formulaire d'édition
4. `resources/views/admin/devis/show.blade.php` - Affichage d'un devis
5. `resources/views/admin/factures/index.blade.php` - Liste des factures
6. `resources/views/admin/factures/show.blade.php` - Affichage d'une facture
7. `resources/views/admin/clients/index.blade.php` - Liste des clients
8. `resources/views/admin/quotations/dashboard.blade.php` - Tableau de bord

### Vues PDF
9. `resources/views/pdfs/devis.blade.php` - Template PDF devis
10. `resources/views/pdfs/facture.blade.php` - Template PDF facture

### Vues Email
11. `resources/views/emails/devis_sent.blade.php` - Email devis
12. `resources/views/emails/facture_sent.blade.php` - Email facture

## 🚀 Installation

1. **Installer les dépendances** :
```bash
composer install
```

2. **Exécuter les migrations** :
```bash
php artisan migrate
```

3. **Publier la configuration dompdf** (si nécessaire) :
```bash
php artisan vendor:publish --provider="Barryvdh\DomPDF\ServiceProvider"
```

## 📝 Utilisation

### Créer un devis avec IA

1. Aller sur `/admin/devis/create`
2. Sélectionner ou créer un client
3. Saisir la description globale des travaux
4. (Optionnel) Ajouter superficie et prix final estimé
5. Cliquer sur "Générer avec l'IA"
6. L'IA génère les lignes de devis détaillées
7. Ajuster manuellement si nécessaire
8. Sauvegarder

### Valider un devis

1. Aller sur `/admin/devis/{id}`
2. Cliquer sur "Valider le devis"
3. Une facture est automatiquement créée avec le statut "Impayée"

### Marquer une facture comme payée

1. Aller sur `/admin/factures/{id}`
2. Cliquer sur "Marquer comme payée"
3. Le CA est automatiquement mis à jour

### Tableau de bord

- `/admin/quotations/dashboard`
- Affiche :
  - CA Total (utilise cursors)
  - CA Potentiel
  - Taux de conversion
  - Factures en attente
  - Statistiques par statut

## 🔧 Optimisations avec Cursors

Les cursors sont utilisés dans :
- **QuotationStatsController** : Calcul du CA total et CA potentiel
- Permet de traiter des milliers de factures sans problème de mémoire

Exemple :
```php
$paidInvoices = Facture::where('statut', 'Payée')->cursor();
foreach ($paidInvoices as $invoice) {
    $totalCA += $invoice->prix_total_ttc;
}
```

## 📧 Envoi par Email

Pour envoyer un devis par email :
```php
use App\Mail\DevisSent;
use Illuminate\Support\Facades\Mail;

Mail::to($client->email)->send(new DevisSent($devis));
```

## 📄 Génération PDF

Pour générer un PDF :
```php
use App\Services\PdfService;

$pdfService = new PdfService();
$path = $pdfService->generateDevisPdf($devis);
```

## ⚠️ Notes Importantes

1. **GROQ API** : Le service utilise déjà `AiService` qui gère GROQ. Assurez-vous que la clé API est configurée dans les settings.

2. **Stockage PDF** : Les PDFs sont stockés dans `storage/app/devis/` et `storage/app/factures/`. Assurez-vous que ces dossiers existent et sont accessibles en écriture.

3. **Numérotation** : Les numéros de devis et factures sont générés automatiquement au format `DEV-YYYY-XXXXX` et `FAC-YYYY-XXXXX`.

4. **Calculs automatiques** : Les totaux HT et TTC sont recalculés automatiquement à chaque sauvegarde du devis.

