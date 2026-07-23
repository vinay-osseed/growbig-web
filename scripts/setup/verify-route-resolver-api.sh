#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "site-platform/web/modules/custom/site_platform_api/site_platform_api.routing.yml"
  "site-platform/web/modules/custom/site_platform_api/src/Controller/PageApiController.php"
  "docs/implementation/phase-06-route-resolver-api.md"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "Missing: $file"
    exit 1
  fi
done

ddev drush en site_platform_core site_platform_site site_platform_page site_platform_api -y
ddev drush cr

ddev drush php:script /var/www/html/scripts/setup/verify-basic-site-page-api.php

front_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/routes?site=growbig')"
about_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/routes/about?site=growbig')"
osseed_front_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/routes?site=osseed')"
missing_json="$(curl -s 'https://site-platform.ddev.site/api/v1/routes/missing?site=growbig')"

printf '%s' "$front_json" | grep -q '"path":"/"'
printf '%s' "$front_json" | grep -q '"title":"GrowBig Home"'
printf '%s' "$about_json" | grep -q '"path":"/about"'
printf '%s' "$about_json" | grep -q '"title":"GrowBig About"'
printf '%s' "$osseed_front_json" | grep -q '"title":"OSSeed Home"'
printf '%s' "$missing_json" | grep -q '"code":"route_not_found"'

echo "Route resolver API verification passed."
