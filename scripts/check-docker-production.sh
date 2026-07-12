#!/usr/bin/env bash
set -euo pipefail

echo "Checking Docker production files..."

required_files=(
  ".dockerignore"
  ".env.prod.example"
  "docker-compose.prod.yml"
  "docker/site-platform/Dockerfile"
  "docker/site-platform/apache-vhost.conf"
  "docker/site-platform/prod-php.ini"
  "docker/site-platform/entrypoint.sh"
  "docker/site-frontend/Dockerfile"
  "docker/site-frontend/nginx.conf"
  "docker/site-frontend/index.html"
  ".github/workflows/docker-images.yml"
  "docker/caddy/Caddyfile"
  "site-platform/web/sites/default/settings.prod.php"
  "site-platform/web/sites/default/services.prod.yml"
  "scripts/docker-build-push.sh"
  "scripts/prod-deploy.sh"
)

for file in "${required_files[@]}"; do
  if [ ! -f "$file" ]; then
    echo "Missing required Docker production file: $file" >&2
    exit 1
  fi
done

php -l site-platform/web/sites/default/settings.prod.php

if command -v docker >/dev/null 2>&1; then
  docker compose --env-file .env.prod.example -f docker-compose.prod.yml config >/dev/null
fi

echo "Docker production files verified."
