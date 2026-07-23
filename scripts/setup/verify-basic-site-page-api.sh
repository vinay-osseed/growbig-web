#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "site-platform/web/modules/custom/site_platform_api/site_platform_api.info.yml"
  "site-platform/web/modules/custom/site_platform_api/site_platform_api.routing.yml"
  "site-platform/web/modules/custom/site_platform_api/src/Controller/SiteApiController.php"
  "site-platform/web/modules/custom/site_platform_api/src/Controller/PageApiController.php"
  "scripts/setup/verify-basic-site-page-api.php"
  "docs/implementation/phase-05-basic-site-page-api.md"
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

site_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/site?site=growbig')"
pages_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/pages?site=growbig')"
home_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/pages/home?site=growbig')"
osseed_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/pages/home?site=osseed')"

printf '%s' "$site_json" | grep -q '"siteKey":"growbig"'
printf '%s' "$pages_json" | grep -q '"pageKey":"home"'
printf '%s' "$home_json" | grep -q '"title":"GrowBig Home"'
printf '%s' "$osseed_json" | grep -q '"title":"OSSeed Home"'

echo "Basic Site Page API verification passed."
