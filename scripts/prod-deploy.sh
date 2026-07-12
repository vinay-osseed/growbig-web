#!/usr/bin/env bash
set -euo pipefail

ENV_FILE="${1:-.env.prod}"

if [ ! -f "$ENV_FILE" ]; then
  echo "Missing env file: $ENV_FILE" >&2
  exit 1
fi

docker compose --env-file "$ENV_FILE" -f docker-compose.prod.yml pull
docker compose --env-file "$ENV_FILE" -f docker-compose.prod.yml up -d db
docker compose --env-file "$ENV_FILE" -f docker-compose.prod.yml up -d site-platform site-frontend caddy

docker compose --env-file "$ENV_FILE" -f docker-compose.prod.yml exec -T site-platform bash -lc 'cd /var/www/html && vendor/bin/drush updb -y && vendor/bin/drush cim -y && vendor/bin/drush cr'

echo "Production deploy complete."
