# Couvreur Ads Pilot

Base SaaS Next.js pour automatiser et superviser des campagnes Google Ads locales pour couvreurs, toiture, zinguerie, isolation, rénovation et dépannage fuite toiture.

Le projet est volontairement sécurisé par défaut :

- Mode `humanValidationMode` activé par défaut.
- Mode démo activé si `APP_DEMO_MODE=true` ou si les identifiants Google Ads/OpenAI sont absents.
- Publication Google Ads en `dry-run` dans le wizard tant que les vrais identifiants ne sont pas branchés.
- Aucune hausse de budget automatique.
- Aucune création de campagne réelle sans validation.
- Aucune suppression de campagne automatique.
- Les exclusions automatiques sont bloquées dès qu'un terme commercial protégé est détecté, sauf intention clairement inutile comme `formation`, `salaire`, `stage`, `tuto`, `pdf`.

## Installation

```bash
cd ads
cp .env.example .env
npm install
npx prisma generate
npx prisma migrate dev
npm run dev
```

Application locale :

```text
http://localhost:3000
```

## Variables d'environnement

```env
DATABASE_URL=
NEXTAUTH_SECRET=
NEXTAUTH_URL=
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_ADS_DEVELOPER_TOKEN=
GOOGLE_ADS_LOGIN_CUSTOMER_ID=
OPENAI_API_KEY=
REDIS_URL=
RESEND_API_KEY=
APP_DEMO_MODE=true
TOKEN_ENCRYPTION_KEY=
```

## Pages

- `/` : landing marketing.
- `/login` : connexion email/password et Google, structure Auth.js prête.
- `/dashboard` : métriques, alertes, prochaines analyses.
- `/settings/google-ads` : OAuth Google Ads et customer IDs.
- `/campaigns/new` : wizard de création campagne couvreur.
- `/campaigns` : liste des campagnes.
- `/campaigns/[id]` : détail, termes de recherche, recommandations.
- `/recommendations` : validation/rejet recommandations IA.
- `/reports` : rapports et résumé IA.
- `/settings` : mode IA, fréquence, budget max, alertes, négatifs globaux.

## API routes

- `POST /api/google-ads/oauth/start`
- `GET /api/google-ads/oauth/callback`
- `GET /api/google-ads/accounts`
- `POST /api/campaigns/generate`
- `POST /api/campaigns/publish`
- `GET /api/campaigns`
- `GET /api/campaigns/[id]`
- `POST /api/campaigns/[id]/scan`
- `POST /api/recommendations/[id]/accept`
- `POST /api/recommendations/[id]/reject`
- `POST /api/reports/generate`

## Google OAuth

1. Créer un projet Google Cloud.
2. Activer OAuth consent screen.
3. Ajouter le scope `https://www.googleapis.com/auth/adwords`.
4. Ajouter l'URL de callback :

```text
http://localhost:3000/api/google-ads/oauth/callback
```

5. Renseigner :

```env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
```

Les tokens sont chiffrés via `lib/security/encryption.ts`.

## Google Ads API

Renseigner :

```env
GOOGLE_ADS_DEVELOPER_TOKEN=
GOOGLE_ADS_LOGIN_CUSTOMER_ID=
```

Les services sont séparés dans :

```text
lib/google-ads/client.ts
lib/google-ads/oauth.ts
```

TODO avec vrais identifiants :

- Lister les customers via Google Ads API.
- Créer budget, campaign, ad groups, keywords, responsive search ads, call extension, sitelinks et negative keywords.
- Lire les performances via GAQL.
- Lire les search terms via `search_term_view`.
- Ajouter les exclusions via `campaignCriteria.mutate`.

## OpenAI

Renseigner :

```env
OPENAI_API_KEY=
```

Services :

```text
lib/ai/openai.ts
lib/ai/prompts.ts
```

Fonctions :

- Génération de structure campagne.
- Analyse des termes de recherche.
- Génération de résumé de rapport.

## Jobs

Le job principal est :

```text
lib/jobs/scheduled-scan.ts
```

Test en mode démo :

```bash
npm run scan:demo
```

BullMQ est préparé dans :

```text
lib/jobs/queue.ts
```

Pour Vercel Cron, appeler une route qui exécute `runScheduledScan()` toutes les 2h ou 3h selon les paramètres utilisateur.

## Mode démo

Avec `APP_DEMO_MODE=true`, l'application affiche :

- Campagne `Couvreur Chalon-sur-Saône`.
- Budget `15 €/jour`.
- Offre `-30% sur vos travaux`.
- Services couverture, isolation, rénovation, zinguerie.
- Termes utiles et inutiles : `formation couvreur`, `salaire couvreur`, `castorama plaque toiture`, etc.

Ce mode permet de tester le dashboard, le wizard, les recommandations, les rapports et les scans sans appeler Google Ads.

## Sécurité métier

Fichier :

```text
lib/security/ads-safety.ts
```

Règles incluses :

- Budget jamais augmenté automatiquement.
- Création campagne toujours validée humainement.
- Pas de suppression automatique.
- Pas d'exclusion automatique de termes contenant `couvreur`, `toiture`, `fuite`, `réparation`, `zinguerie`, `isolation`, `rénovation`, sauf présence claire d'intention inutile.
- Toute action réelle doit écrire un `ActionLog`.

## Prochaines étapes production

- Brancher les vraies sessions Auth.js sur toutes les pages protégées.
- Écrire en base les campagnes générées par le wizard.
- Remplacer les retours démo par Prisma dans les API.
- Ajouter un vrai export PDF.
- Envoyer les rapports via Resend.
- Ajouter une route cron sécurisée par token.
