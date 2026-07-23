#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "site-platform/web/modules/custom/site_platform_media/site_platform_media.info.yml"
  "site-platform/web/modules/custom/site_platform_media/config/install/node.type.site_media_asset.yml"
  "site-platform/web/modules/custom/site_platform_api/src/Controller/MediaApiController.php"
  "site-platform/web/modules/custom/site_platform_api/site_platform_api.routing.yml"
  "site-platform/web/modules/custom/site_platform_setup/src/Commands/SitePlatformSetupCommands.php"
  "setup/examples/sample.site.yml"
  "docs/implementation/phase-17-media-asset-foundation.md"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "Missing: $file"
    exit 1
  fi
done

php -l site-platform/web/modules/custom/site_platform_api/src/Controller/MediaApiController.php >/dev/null
php -l site-platform/web/modules/custom/site_platform_setup/src/Commands/SitePlatformSetupCommands.php >/dev/null

ddev drush en webform site_platform_core site_platform_site site_platform_page site_platform_menu site_platform_component site_platform_form site_platform_content site_platform_media site_platform_setup site_platform_api -y
ddev drush cr

ddev drush config:get node.type.site_media_asset >/dev/null
ddev drush config:get field.field.node.site_media_asset.field_media_key >/dev/null
ddev drush config:get field.field.node.site_media_asset.field_media_kind >/dev/null
ddev drush config:get field.field.node.site_media_asset.field_media_url >/dev/null
ddev drush config:get field.field.node.site_media_asset.field_media_alt >/dev/null

dry_run_output="$(ddev drush site-platform:setup-import /var/www/html/setup/examples/sample.site.yml --dry-run)"
printf '%s' "$dry_run_output" | grep -q 'Page Components: 4'
printf '%s' "$dry_run_output" | grep -q 'Site Media Assets: 2'

ddev drush site-platform:setup-import /var/www/html/setup/examples/sample.site.yml >/dev/null

media_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/media?site=yamltest')"
hero_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/media/hero-image?site=yamltest')"
missing_json="$(curl -s 'https://site-platform.ddev.site/api/v1/media/missing?site=yamltest')"
page_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/pages/home?site=yamltest')"
reset_dry="$(ddev drush site-platform:setup-reset-site yamltest)"

printf '%s' "$media_json" | grep -q '"type":"SiteMediaAsset"'
printf '%s' "$media_json" | grep -q '"key":"hero-image"'
printf '%s' "$media_json" | grep -q '"key":"brochure"'
printf '%s' "$hero_json" | grep -q '"kind":"image"'
printf '%s' "$hero_json" | grep -q '"alt":"YAML hero image"'
printf '%s' "$missing_json" | grep -q '"code":"media_not_found"'
printf '%s' "$page_json" | grep -q '"components"'
printf '%s' "$page_json" | grep -q 'YAML home hero'
printf '%s' "$reset_dry" | grep -q 'Site Media Asset: 2'

echo "Media asset model, YAML import, and API verification passed."
