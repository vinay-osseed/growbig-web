#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "site-platform/web/modules/custom/site_platform_content/site_platform_content.info.yml"
  "site-platform/web/modules/custom/site_platform_content/config/install/node.type.site_content_block.yml"
  "site-platform/web/modules/custom/site_platform_content/config/install/field.field.node.site_content_block.field_content_key.yml"
  "site-platform/web/modules/custom/site_platform_api/src/Controller/ContentApiController.php"
  "scripts/setup/verify-content-api.php"
  "docs/implementation/phase-10-reusable-content-api.md"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "Missing: $file"
    exit 1
  fi
done

ddev drush en site_platform_core site_platform_site site_platform_page site_platform_menu site_platform_component site_platform_form site_platform_content site_platform_api -y
ddev drush cr

ddev drush config:get node.type.site_content_block >/dev/null
ddev drush config:get field.field.node.site_content_block.field_content_key >/dev/null
ddev drush config:get field.field.node.site_content_block.field_content_source >/dev/null
ddev drush config:get field.field.node.site_content_block.field_content_body >/dev/null

ddev drush php:script /var/www/html/scripts/setup/verify-content-api.php

all_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/content?site=growbig')"
global_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/content/global?site=growbig')"
item_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/content/global/footer_cta?site=growbig')"
osseed_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/content/global/footer_cta?site=osseed')"
missing_json="$(curl -s 'https://site-platform.ddev.site/api/v1/content/global/missing?site=growbig')"

printf '%s' "$all_json" | grep -q '"source":"global"'
printf '%s' "$all_json" | grep -q '"source":"contact"'
printf '%s' "$global_json" | grep -q '"key":"footer_cta"'
printf '%s' "$global_json" | grep -q '"key":"announcement"'
printf '%s' "$item_json" | grep -q '"label":"Footer CTA"'
printf '%s' "$item_json" | grep -q '"variant":"dark"'
printf '%s' "$osseed_json" | grep -q '"Contact OSSeed'
printf '%s' "$missing_json" | grep -q '"code":"content_not_found"'

echo "Reusable content API verification passed."
