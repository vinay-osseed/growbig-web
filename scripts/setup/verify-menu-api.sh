#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "site-platform/web/modules/custom/site_platform_menu/site_platform_menu.info.yml"
  "site-platform/web/modules/custom/site_platform_menu/config/install/node.type.site_menu.yml"
  "site-platform/web/modules/custom/site_platform_menu/config/install/node.type.site_menu_item.yml"
  "site-platform/web/modules/custom/site_platform_api/src/Controller/MenuApiController.php"
  "scripts/setup/verify-menu-api.php"
  "docs/implementation/phase-07-menu-model-api.md"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "Missing: $file"
    exit 1
  fi
done

ddev drush en site_platform_core site_platform_site site_platform_page site_platform_menu site_platform_api -y
ddev drush cr

ddev drush php:script /var/www/html/scripts/setup/verify-basic-site-page-api.php
ddev drush php:script /var/www/html/scripts/setup/verify-menu-api.php

menus_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/menus?site=growbig')"
main_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/menus/main?site=growbig')"
osseed_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/menus/main?site=osseed')"
missing_json="$(curl -s 'https://site-platform.ddev.site/api/v1/menus/missing?site=growbig')"

printf '%s' "$menus_json" | grep -q '"key":"main"'
printf '%s' "$main_json" | grep -q '"title":"Home"'
printf '%s' "$main_json" | grep -q '"title":"About"'
printf '%s' "$main_json" | grep -q '"title":"Contact"'
printf '%s' "$main_json" | grep -q '"isButton":true'
printf '%s' "$osseed_json" | grep -q '"title":"Home"'
printf '%s' "$missing_json" | grep -q '"code":"menu_not_found"'

echo "Menu API verification passed."
