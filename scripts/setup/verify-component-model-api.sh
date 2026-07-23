#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "site-platform/web/modules/custom/site_platform_component/site_platform_component.info.yml"
  "site-platform/web/modules/custom/site_platform_component/config/install/paragraphs.paragraphs_type.site_hero.yml"
  "site-platform/web/modules/custom/site_platform_component/config/install/paragraphs.paragraphs_type.site_rich_text.yml"
  "site-platform/web/modules/custom/site_platform_component/config/install/paragraphs.paragraphs_type.site_cta.yml"
  "site-platform/web/modules/custom/site_platform_component/config/install/field.field.node.site_page.field_page_components.yml"
  "site-platform/web/modules/custom/site_platform_api/src/Controller/PageApiController.php"
  "scripts/setup/verify-component-api.php"
  "docs/implementation/phase-08-component-model-api.md"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "Missing: $file"
    exit 1
  fi
done

ddev drush en site_platform_core site_platform_site site_platform_page site_platform_menu site_platform_component site_platform_api -y
ddev drush cr

ddev drush config:get paragraphs.paragraphs_type.site_hero >/dev/null
ddev drush config:get paragraphs.paragraphs_type.site_rich_text >/dev/null
ddev drush config:get paragraphs.paragraphs_type.site_cta >/dev/null
ddev drush config:get field.field.node.site_page.field_page_components >/dev/null

ddev drush php:script /var/www/html/scripts/setup/verify-basic-site-page-api.php
ddev drush php:script /var/www/html/scripts/setup/verify-component-api.php

home_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/pages/home?site=growbig')"
about_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/pages/about?site=growbig')"
osseed_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/pages/home?site=osseed')"

printf '%s' "$home_json" | grep -q '"components"'
printf '%s' "$home_json" | grep -q '"type":"hero"'
printf '%s' "$home_json" | grep -q '"type":"rich_text"'
printf '%s' "$home_json" | grep -q '"type":"cta"'
printf '%s' "$home_json" | grep -q '"buttonLabel":"Get Started"'
printf '%s' "$about_json" | grep -q '"About GrowBig"'
printf '%s' "$osseed_json" | grep -q '"OSSeed Technologies LLP"'

echo "Component model and API verification passed."
