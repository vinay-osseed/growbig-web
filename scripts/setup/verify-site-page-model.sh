#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "site-platform/web/modules/custom/site_platform_page/site_platform_page.info.yml"
  "site-platform/web/modules/custom/site_platform_page/README.md"
  "site-platform/web/modules/custom/site_platform_page/config/install/node.type.site_page.yml"
  "site-platform/web/modules/custom/site_platform_page/config/install/field.storage.node.field_site_profile.yml"
  "site-platform/web/modules/custom/site_platform_page/config/install/field.field.node.site_page.field_site_profile.yml"
  "site-platform/web/modules/custom/site_platform_page/config/install/field.storage.node.field_page_key.yml"
  "site-platform/web/modules/custom/site_platform_page/config/install/field.field.node.site_page.field_page_key.yml"
  "docs/implementation/phase-04-site-page-model.md"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "Missing: $file"
    exit 1
  fi
done

ddev drush en site_platform_core site_platform_site site_platform_page -y
ddev drush cr

ddev drush config:get node.type.site_page >/dev/null
ddev drush config:get field.field.node.site_page.field_site_profile >/dev/null
ddev drush config:get field.field.node.site_page.field_page_key >/dev/null
ddev drush config:get field.field.node.site_page.field_page_slug >/dev/null
ddev drush config:get field.field.node.site_page.field_page_path >/dev/null
ddev drush config:get field.field.node.site_page.field_page_template >/dev/null
ddev drush config:get field.field.node.site_page.field_seo_title >/dev/null
ddev drush config:get field.field.node.site_page.field_seo_description >/dev/null

echo "Site Page model verification passed."
