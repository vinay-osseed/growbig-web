#!/usr/bin/env bash
set -euo pipefail

fail() {
  echo "FAILED: $1" >&2
  exit 1
}

check_file() {
  test -f "$1" || fail "Missing file: $1"
}

check_executable() {
  test -x "$1" || fail "File is not executable: $1"
}

check_contains() {
  local file="$1"
  local pattern="$2"
  grep -Fq -- "$pattern" "$file" || fail "Missing pattern in $file: $pattern"
}

echo "Verifying Docker image workflow..."

check_file ".github/workflows/docker-images.yml"
check_file "docker/site-platform/Dockerfile"
check_file "docker/site-platform/apache-vhost.conf"
check_file "docker/site-platform/entrypoint.sh"
check_file "docker/site-platform/prod-php.ini"
check_file "site-platform/composer.json"
check_file "site-platform/composer.lock"

check_executable "docker/site-platform/entrypoint.sh"

check_contains ".github/workflows/docker-images.yml" "branches:"
check_contains ".github/workflows/docker-images.yml" "dev"
check_contains ".github/workflows/docker-images.yml" "prod"
check_contains ".github/workflows/docker-images.yml" "DOCKERHUB_USERNAME"
check_contains ".github/workflows/docker-images.yml" "DOCKERHUB_TOKEN"
check_contains ".github/workflows/docker-images.yml" "growbig-site-platform"
check_contains ".github/workflows/docker-images.yml" ":dev"
check_contains ".github/workflows/docker-images.yml" ":prod"
check_contains ".github/workflows/docker-images.yml" ":latest"
check_contains ".github/workflows/docker-images.yml" "docker/build-push-action@v6"

check_contains "docker/site-platform/Dockerfile" "FROM php:8.4-apache-bookworm AS php-base"
check_contains "docker/site-platform/Dockerfile" "docker-php-ext-install"
check_contains "docker/site-platform/Dockerfile" "gd"
check_contains "docker/site-platform/Dockerfile" "pdo_mysql"
check_contains "docker/site-platform/Dockerfile" "composer install"
check_contains "docker/site-platform/Dockerfile" "--no-dev"
check_contains "docker/site-platform/Dockerfile" "COPY site-platform /var/www/html/site-platform"

echo "Docker image workflow verification passed."
