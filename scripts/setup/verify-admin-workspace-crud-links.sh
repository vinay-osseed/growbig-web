#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

controller="site-platform/web/modules/custom/site_platform_admin/src/Controller/SitePlatformAdminController.php"

required=(
  "docs/implementation/phase-36c-clickable-admin-crud.md"
  "docs/admin/site-platform-admin-workspace-runtime.md"
  "scripts/setup/verify-admin-workspace-crud-links.sh"
  "$controller"
  "site-platform/web/modules/custom/site_platform_admin/site_platform_admin.routing.yml"
  "site-platform/web/modules/custom/site_platform_admin/site_platform_admin.links.menu.yml"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "Missing: $file"
    exit 1
  fi
done

php -l "$controller" >/dev/null

grep -Fq "function linksCell" "$controller"
grep -Fq "function nodeActionsCell" "$controller"
grep -Fq "function actionsMarkup" "$controller"
grep -Fq "'/node/add/site_page'" "$controller"
grep -Fq "'/node/' . \$node->id() . '/delete'" "$controller"
grep -Fq "'/admin/structure/webform/add'" "$controller"
grep -Fq "'/admin/structure/webform/manage/' . \$id . '/results/submissions'" "$controller"
grep -Fq "Html::escape" "$controller"
grep -Fq "Markup::create" "$controller"

if grep -Fq "Drupal\\Core\\Url" "$controller"; then
  echo "Unsafe Url import found in controller."
  exit 1
fi

if grep -Fq "fromTextAndUrl" "$controller"; then
  echo "Unsafe Link::fromTextAndUrl usage found in controller."
  exit 1
fi

ddev drush cr >/dev/null

ddev drush ev 'foreach (["site_platform_admin.overview", "site_platform_admin.sites", "site_platform_admin.pages", "site_platform_admin.content_blocks", "site_platform_admin.media_assets", "site_platform_admin.forms", "site_platform_admin.menus", "site_platform_admin.legacy_wrappers"] as $route) { echo \Drupal::service("router.route_provider")->getRouteByName($route)->getPath() . PHP_EOL; }' | grep -Fq '/admin/site-platform/legacy-wrappers'

ddev drush ev '$c = \Drupal::service("class_resolver")->getInstanceFromDefinition(\Drupal\site_platform_admin\Controller\SitePlatformAdminController::class); foreach (["overview", "sites", "pages", "contentBlocks", "mediaAssets", "forms", "menus", "legacyWrappers"] as $method) { $build = $c->{$method}(); $out = (string) \Drupal::service("renderer")->renderRoot($build); if (strpos($out, "href=") === FALSE) { throw new \RuntimeException("No clickable links rendered for " . $method); } } echo "rendered";' | grep -Fq 'rendered'

echo "Admin workspace CRUD links verification passed."
