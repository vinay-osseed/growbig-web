#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "site-platform/web/modules/custom/site_platform_admin/site_platform_admin.info.yml"
  "site-platform/web/modules/custom/site_platform_admin/site_platform_admin.permissions.yml"
  "site-platform/web/modules/custom/site_platform_admin/site_platform_admin.routing.yml"
  "site-platform/web/modules/custom/site_platform_admin/site_platform_admin.links.menu.yml"
  "site-platform/web/modules/custom/site_platform_admin/src/Controller/SitePlatformAdminController.php"
  "docs/implementation/phase-21-admin-permission-readiness.md"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "Missing: $file"
    exit 1
  fi
done

php -l site-platform/web/modules/custom/site_platform_admin/src/Controller/SitePlatformAdminController.php >/dev/null

grep -q 'administer site platform:' site-platform/web/modules/custom/site_platform_admin/site_platform_admin.permissions.yml
grep -q 'manage site platform content:' site-platform/web/modules/custom/site_platform_admin/site_platform_admin.permissions.yml
grep -q 'view site platform reports:' site-platform/web/modules/custom/site_platform_admin/site_platform_admin.permissions.yml

ddev drush en webform site_platform_core site_platform_site site_platform_page site_platform_menu site_platform_component site_platform_form site_platform_content site_platform_media site_platform_setup site_platform_api site_platform_admin -y
ddev drush cr

route_path="$(ddev drush ev 'echo \Drupal::service("router.route_provider")->getRouteByName("site_platform_admin.overview")->getPath();')"
printf '%s' "$route_path" | grep -q '/admin/site-platform'

ddev drush ev 'echo \Drupal::moduleHandler()->moduleExists("site_platform_admin") ? "enabled" : "disabled";' | grep -q 'enabled'

echo "Admin and permission readiness verification passed."
