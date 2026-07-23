#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "docs/implementation/phase-36b-safe-admin-workspace-hotfix.md"
  "docs/admin/site-platform-admin-workspace-runtime.md"
  "site-platform/web/modules/custom/site_platform_admin/site_platform_admin.info.yml"
  "site-platform/web/modules/custom/site_platform_admin/site_platform_admin.routing.yml"
  "site-platform/web/modules/custom/site_platform_admin/site_platform_admin.links.menu.yml"
  "site-platform/web/modules/custom/site_platform_admin/src/Controller/SitePlatformAdminController.php"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "Missing: $file"
    exit 1
  fi
done

php -l site-platform/web/modules/custom/site_platform_admin/src/Controller/SitePlatformAdminController.php >/dev/null

grep -Fq "site_platform_admin.sites" site-platform/web/modules/custom/site_platform_admin/site_platform_admin.routing.yml
grep -Fq "site_platform_admin.pages" site-platform/web/modules/custom/site_platform_admin/site_platform_admin.routing.yml
grep -Fq "site_platform_admin.forms" site-platform/web/modules/custom/site_platform_admin/site_platform_admin.routing.yml
grep -Fq "site_platform_admin.legacy_wrappers" site-platform/web/modules/custom/site_platform_admin/site_platform_admin.routing.yml

grep -Fq "function sites" site-platform/web/modules/custom/site_platform_admin/src/Controller/SitePlatformAdminController.php
grep -Fq "function pages" site-platform/web/modules/custom/site_platform_admin/src/Controller/SitePlatformAdminController.php
grep -Fq "function forms" site-platform/web/modules/custom/site_platform_admin/src/Controller/SitePlatformAdminController.php
grep -Fq "function legacyWrappers" site-platform/web/modules/custom/site_platform_admin/src/Controller/SitePlatformAdminController.php

if grep -Fq "Drupal\\Core\\Url" site-platform/web/modules/custom/site_platform_admin/src/Controller/SitePlatformAdminController.php; then
  echo "Unsafe Url import found in controller."
  exit 1
fi

if grep -Fq "fromTextAndUrl" site-platform/web/modules/custom/site_platform_admin/src/Controller/SitePlatformAdminController.php; then
  echo "Unsafe Link::fromTextAndUrl usage found in controller."
  exit 1
fi

ddev drush cr >/dev/null

ddev drush ev 'foreach (["site_platform_admin.overview", "site_platform_admin.sites", "site_platform_admin.pages", "site_platform_admin.forms", "site_platform_admin.legacy_wrappers"] as $route) { echo \Drupal::service("router.route_provider")->getRouteByName($route)->getPath() . PHP_EOL; }' | grep -Fq '/admin/site-platform/pages'

ddev drush ev '$c = \Drupal::service("class_resolver")->getInstanceFromDefinition(\Drupal\site_platform_admin\Controller\SitePlatformAdminController::class); foreach (["overview", "sites", "pages", "contentBlocks", "mediaAssets", "forms", "menus", "legacyWrappers"] as $method) { $build = $c->{$method}(); \Drupal::service("renderer")->renderRoot($build); } echo "rendered";' | grep -Fq 'rendered'

echo "Admin workspace runtime hotfix verification passed."
