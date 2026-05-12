#!/usr/bin/env bash

set -euo pipefail

if [ $# -lt 4 ]; then
  echo "Usage: $0 <port> <user@host> <remote_app_dir> <branch> [release_name] [release_version] [release_date]"
  echo "Exemple:"
  echo "  $0 65002 u668825112@193.203.170.118 domains/envoyez-dubois.fr/public_html main \"SEO Indexation\" \"2026.05.12\" \"2026-05-12 12:00:00\""
  exit 1
fi

PORT="$1"
TARGET="$2"
REMOTE_APP_DIR="$3"
BRANCH="$4"
RELEASE_NAME="${5:-}"
RELEASE_VERSION="${6:-}"
RELEASE_DATE="${7:-}"

encode_b64() {
  printf '%s' "$1" | base64 | tr -d '\n'
}

REMOTE_APP_DIR_B64="$(encode_b64 "$REMOTE_APP_DIR")"
BRANCH_B64="$(encode_b64 "$BRANCH")"
RELEASE_NAME_B64="$(encode_b64 "$RELEASE_NAME")"
RELEASE_VERSION_B64="$(encode_b64 "$RELEASE_VERSION")"
RELEASE_DATE_B64="$(encode_b64 "$RELEASE_DATE")"

echo "➡️ Déploiement sur ${TARGET}:${REMOTE_APP_DIR} (${BRANCH})"

ssh -t -p "$PORT" "$TARGET" \
  "REMOTE_APP_DIR_B64='$REMOTE_APP_DIR_B64' BRANCH_B64='$BRANCH_B64' RELEASE_NAME_B64='$RELEASE_NAME_B64' RELEASE_VERSION_B64='$RELEASE_VERSION_B64' RELEASE_DATE_B64='$RELEASE_DATE_B64' bash -s" <<'REMOTE'
set -euo pipefail

decode_b64() {
  printf '%s' "$1" | base64 --decode
}

REMOTE_APP_DIR="$(decode_b64 "$REMOTE_APP_DIR_B64")"
BRANCH="$(decode_b64 "$BRANCH_B64")"
RELEASE_NAME="$(decode_b64 "$RELEASE_NAME_B64")"
RELEASE_VERSION="$(decode_b64 "$RELEASE_VERSION_B64")"
RELEASE_DATE="$(decode_b64 "$RELEASE_DATE_B64")"

cd "$REMOTE_APP_DIR"

echo "📥 Mise à jour git..."
git fetch origin "$BRANCH"
git checkout "$BRANCH"
git pull --ff-only origin "$BRANCH"

if [ -n "$RELEASE_NAME" ] || [ -n "$RELEASE_VERSION" ] || [ -n "$RELEASE_DATE" ]; then
  echo "🏷️ Mise à jour de la version affichée..."
  export RELEASE_NAME RELEASE_VERSION RELEASE_DATE
  php -r '
    $path = ".env";
    $content = file_exists($path) ? file_get_contents($path) : "";
    $updates = [
      "APP_RELEASE_NAME" => getenv("RELEASE_NAME") ?: "",
      "APP_VERSION" => getenv("RELEASE_VERSION") ?: "",
      "APP_RELEASE_DATE" => getenv("RELEASE_DATE") ?: "",
    ];

    foreach ($updates as $key => $value) {
      if ($value === "") {
        continue;
      }

      $line = $key . "=\"" . str_replace("\"", "\\\"", $value) . "\"";

      if (preg_match("/^" . preg_quote($key, "/") . "=.*/m", $content)) {
        $content = preg_replace("/^" . preg_quote($key, "/") . "=.*/m", $line, $content);
      } else {
        $content .= ($content === "" ? "" : PHP_EOL) . $line;
      }
    }

    file_put_contents($path, $content);
  '
fi

echo "📦 Installation des dépendances..."
composer install --no-dev --optimize-autoloader

echo "🗄️ Migrations..."
php artisan migrate --force

echo "🧹 Nettoyage des caches..."
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo "⚙️ Reconstruction des caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🗺️ Génération des sitemaps..."
php artisan sitemap:generate-daily

echo "✅ Déploiement terminé"
REMOTE
