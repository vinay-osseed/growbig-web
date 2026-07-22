#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

BASE_URL="${BASE_URL:-https://site-platform.ddev.site}"
TMP_OUT="${TMPDIR:-/tmp}/site_platform_final_api_check.out"

say() {
  printf '%s\n' "$*"
}

fail() {
  say "FAILED: $*"
  exit 1
}

require_file() {
  [ -f "$1" ] || fail "Missing required file: $1"
}

controller="site-platform/web/modules/custom/site_platform_admin/src/Controller/SitePlatformAdminController.php"

required_files=(
  "docs/admin/site-platform-admin-workspace-runtime.md"
  "docs/admin/site-platform-final-operator-checklist.md"
  "docs/implementation/phase-36b-safe-admin-workspace-hotfix.md"
  "docs/implementation/phase-36c-clickable-admin-crud.md"
  "docs/implementation/phase-37-final-backend-readiness-closeout.md"
  "scripts/setup/verify-admin-workspace-runtime-hotfix.sh"
  "scripts/setup/verify-admin-workspace-crud-links.sh"
  "scripts/setup/verify-final-backend-readiness.sh"
  "site-platform/web/modules/custom/site_platform_admin/site_platform_admin.info.yml"
  "site-platform/web/modules/custom/site_platform_admin/site_platform_admin.routing.yml"
  "site-platform/web/modules/custom/site_platform_admin/site_platform_admin.links.menu.yml"
  "$controller"
)

for file in "${required_files[@]}"; do
  require_file "$file"
done

php -l "$controller" >/dev/null

grep -Fq "function linksCell" "$controller" || fail "Clickable link cell helper missing."
grep -Fq "function nodeActionsCell" "$controller" || fail "Node action helper missing."
grep -Fq "Markup::create" "$controller" || fail "Safe markup helper missing."
grep -Fq "Html::escape" "$controller" || fail "HTML escaping missing."

if grep -Fq "Drupal\\Core\\Url" "$controller"; then
  fail "Unsafe Url import found in controller."
fi

if grep -Fq "fromTextAndUrl" "$controller"; then
  fail "Unsafe Link::fromTextAndUrl usage found in controller."
fi

say "Running Phase 36B runtime verifier..."
./scripts/setup/verify-admin-workspace-runtime-hotfix.sh >/dev/null

say "Running Phase 36C CRUD link verifier..."
./scripts/setup/verify-admin-workspace-crud-links.sh >/dev/null

say "Rebuilding Drupal cache..."
ddev drush cr >/dev/null

say "Checking Site Platform admin routes..."
ddev drush ev '
$routes = [
  "site_platform_admin.overview",
  "site_platform_admin.sites",
  "site_platform_admin.pages",
  "site_platform_admin.content_blocks",
  "site_platform_admin.media_assets",
  "site_platform_admin.forms",
  "site_platform_admin.menus",
  "site_platform_admin.legacy_wrappers",
];
foreach ($routes as $route) {
  $path = \Drupal::service("router.route_provider")->getRouteByName($route)->getPath();
  if (strpos($path, "/admin/site-platform") !== 0) {
    throw new \RuntimeException("Unexpected route path for " . $route . ": " . $path);
  }
}
echo "routes-ok";
' | grep -Fq 'routes-ok'

say "Rendering Site Platform admin pages through Drupal..."
ddev drush ev '
$controller = \Drupal::service("class_resolver")->getInstanceFromDefinition(\Drupal\site_platform_admin\Controller\SitePlatformAdminController::class);
$methods = ["overview", "sites", "pages", "contentBlocks", "mediaAssets", "forms", "menus", "legacyWrappers"];
foreach ($methods as $method) {
  $build = $controller->{$method}();
  $out = (string) \Drupal::service("renderer")->renderRoot($build);
  if (strpos($out, "href=") === FALSE) {
    throw new \RuntimeException("No clickable links rendered for " . $method);
  }
}
echo "render-ok";
' | grep -Fq 'render-ok'

say "Checking managed record counts are readable..."
ddev drush ev '
$bundles = ["site_profile", "site_page", "site_content_block", "site_media_asset", "site_menu", "site_menu_item", "site_form", "site_form_field", "site_form_submission"];
$storage = \Drupal::entityTypeManager()->getStorage("node");
foreach ($bundles as $bundle) {
  $count = $storage->getQuery()->accessCheck(FALSE)->condition("type", $bundle)->count()->execute();
  echo $bundle . ":" . $count . PHP_EOL;
}
' >/dev/null

say "Checking native Webform availability..."
ddev drush ev '
if (!\Drupal::moduleHandler()->moduleExists("webform")) {
  throw new \RuntimeException("Webform module is not enabled.");
}
$webforms = \Drupal::entityTypeManager()->getStorage("webform")->loadMultiple();
if (!$webforms) {
  throw new \RuntimeException("No Webforms found.");
}
echo "webform-ok";
' | grep -Fq 'webform-ok'

say "Checking public API endpoints at ${BASE_URL}..."
api_paths=(
  "/api/v1/site?site=yamltest"
  "/api/v1/pages?site=yamltest"
  "/api/v1/menus?site=yamltest"
  "/api/v1/forms/contact?site=yamltest"
  "/api/v1/content?site=yamltest"
  "/api/v1/media?site=yamltest"
  "/api/v1/seo?site=yamltest"
  "/api/v1/analytics?site=yamltest"
  "/api/v1/search?site=yamltest&q=brochure"
)

for path in "${api_paths[@]}"; do
  code="$(curl -k -sS -o "$TMP_OUT" -w '%{http_code}' "${BASE_URL}${path}" || true)"
  if [ "$code" != "200" ]; then
    say "Response body for failed API check:"
    cat "$TMP_OUT" || true
    fail "API endpoint failed: ${path} returned HTTP ${code}"
  fi
done

rm -f "$TMP_OUT"

git diff --check

say "Final backend readiness verification passed."
