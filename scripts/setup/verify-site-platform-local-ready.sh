#!/usr/bin/env bash
set -euo pipefail

fail() {
  echo "FAILED: $1" >&2
  exit 1
}

check_file() {
  test -f "$1" || fail "Missing file: $1"
}

check_contains() {
  local file="$1"
  local pattern="$2"
  grep -Fq -- "$pattern" "$file" || fail "Missing pattern in $file: $pattern"
}

check_file "docker/site-platform/apache-vhost.conf"
check_file "docker/site-platform/Dockerfile"
check_file "scripts/setup/site_platform_local_single_backend_closeout.php"
check_file "docs/architecture/single-backend-template.md"

check_contains "docker/site-platform/apache-vhost.conf" "DocumentRoot /var/www/html/site-platform/web"
check_contains "docker/site-platform/apache-vhost.conf" "<Directory /var/www/html/site-platform/web>"
check_contains "docker/site-platform/Dockerfile" "test -f /var/www/html/site-platform/web/core/includes/bootstrap.inc"
check_contains "docker/site-platform/Dockerfile" "test -d /var/www/html/site-platform/scripts/setup"
check_contains "docs/architecture/single-backend-template.md" "One Drupal backend"
check_contains "docs/architecture/single-backend-template.md" "Multisite"

echo "Local Site Platform closeout verification passed."
