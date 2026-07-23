#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

fail() { printf 'FAILED: %s\n' "$*"; exit 1; }

required_files=(
  ".github/workflows/docker-images.yml"
  "docker/site-platform/Dockerfile"
  "docker/site-platform/apache-vhost.conf"
  "docker/site-platform/entrypoint.sh"
  "docker/site-platform/prod-php.ini"
)

for file in "${required_files[@]}"; do
  [ -f "$file" ] || fail "Missing required file: $file"
done

[ -x docker/site-platform/entrypoint.sh ] || fail "Entrypoint must be executable."

grep -Fq "branches:" .github/workflows/docker-images.yml || fail "Workflow branch trigger missing."
grep -Fq "- dev" .github/workflows/docker-images.yml || fail "Workflow dev trigger missing."
grep -Fq "- prod" .github/workflows/docker-images.yml || fail "Workflow prod trigger missing."
grep -Fq "value=dev" .github/workflows/docker-images.yml || fail "dev image tag missing."
grep -Fq "value=prod" .github/workflows/docker-images.yml || fail "prod image tag missing."
grep -Fq "value=latest" .github/workflows/docker-images.yml || fail "latest image tag missing."
grep -Fq "docker/site-platform/Dockerfile" .github/workflows/docker-images.yml || fail "Dockerfile path missing in workflow."
grep -Fq "composer install" docker/site-platform/Dockerfile || fail "Composer install missing in Dockerfile."
grep -Fq "php:8.4-apache" docker/site-platform/Dockerfile || fail "Runtime PHP 8.4 Apache image missing."
grep -Fq "DocumentRoot /var/www/html/web" docker/site-platform/apache-vhost.conf || fail "Apache docroot must be Drupal web/."

git diff --check
printf 'Docker image workflow verification passed.\n'
