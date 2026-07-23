#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

BASE_URL="${BASE_URL:-https://site-platform.ddev.site}"
TMP_OUT="${TMPDIR:-/tmp}/site_platform_final_completion_api_check.out"
controller="site-platform/web/modules/custom/site_platform_admin/src/Controller/SitePlatformAdminController.php"

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

say "Checking required files..."
required_files=(
  "docs/admin/site-platform-admin-workspace-runtime.md"
  "docs/admin/site-platform-final-operator-checklist.md"
  "docs/implementation/phase-36b-safe-admin-workspace-hotfix.md"
  "docs/implementation/phase-36c-clickable-admin-crud.md"
  "docs/implementation/phase-37-final-backend-readiness-closeout.md"
  "docs/implementation/phase-38-final-completion-verifier.md"
  "scripts/setup/verify-admin-workspace-runtime-hotfix.sh"
  "scripts/setup/verify-admin-workspace-crud-links.sh"
  "scripts/setup/verify-final-backend-readiness.sh"
  "scripts/setup/verify-site-platform-final-completion.sh"
  "site-platform/web/modules/custom/site_platform_admin/site_platform_admin.info.yml"
  "site-platform/web/modules/custom/site_platform_admin/site_platform_admin.routing.yml"
  "site-platform/web/modules/custom/site_platform_admin/site_platform_admin.links.menu.yml"
  "$controller"
)

for file in "${required_files[@]}"; do
  require_file "$file"
done

say "Checking PHP syntax and safe controller patterns..."
php -l "$controller" >/dev/null

grep -Fq "function overview" "$controller" || fail "Overview page method missing."
grep -Fq "function sites" "$controller" || fail "Sites page method missing."
grep -Fq "function pages" "$controller" || fail "Pages page method missing."
grep -Fq "function contentBlocks" "$controller" || fail "Content Blocks page method missing."
grep -Fq "function mediaAssets" "$controller" || fail "Media Assets page method missing."
grep -Fq "function forms" "$controller" || fail "Forms page method missing."
grep -Fq "function menus" "$controller" || fail "Menus page method missing."
grep -Fq "function legacyWrappers" "$controller" || fail "Legacy Wrappers page method missing."
grep -Fq "function linksCell" "$controller" || fail "Clickable link cell helper missing."
grep -Fq "function nodeActionsCell" "$controller" || fail "Node action helper missing."
grep -Fq "function actionsMarkup" "$controller" || fail "Top action helper missing."
grep -Fq "Html::escape" "$controller" || fail "HTML escaping missing."
grep -Fq "Markup::create" "$controller" || fail "Safe markup helper missing."
grep -Fq "'/node/add/site_profile'" "$controller" || fail "Add Site Profile action missing."
grep -Fq "'/node/add/site_page'" "$controller" || fail "Add Landing Page action missing."
grep -Fq "'/admin/structure/webform/add'" "$controller" || fail "Add Webform action missing."
grep -Fq "'/admin/structure/menu'" "$controller" || fail "Native Drupal menu admin link missing."
grep -Fq "'/admin/content/files'" "$controller" || fail "Native Drupal files admin link missing."
grep -Fq "'/node/' . \$node->id() . '/delete'" "$controller" || fail "Delete node action missing."
grep -Fq "'/admin/structure/webform/manage/' . \$id . '/results/submissions'" "$controller" || fail "Webform results action missing."

if grep -Fq "Drupal\\Core\\Url" "$controller"; then
  fail "Unsafe Url import found in controller."
fi

if grep -Fq "fromTextAndUrl" "$controller"; then
  fail "Unsafe Link::fromTextAndUrl usage found in controller."
fi

say "Rebuilding Drupal cache..."
ddev drush cr >/dev/null

say "Checking Site Platform admin routes..."
ddev drush ev '
$routes = [
  "site_platform_admin.overview" => "/admin/site-platform",
  "site_platform_admin.sites" => "/admin/site-platform/sites",
  "site_platform_admin.pages" => "/admin/site-platform/pages",
  "site_platform_admin.content_blocks" => "/admin/site-platform/content-blocks",
  "site_platform_admin.media_assets" => "/admin/site-platform/media-assets",
  "site_platform_admin.forms" => "/admin/site-platform/forms",
  "site_platform_admin.menus" => "/admin/site-platform/menus",
  "site_platform_admin.legacy_wrappers" => "/admin/site-platform/legacy-wrappers",
];
foreach ($routes as $route => $expected) {
  $path = \Drupal::service("router.route_provider")->getRouteByName($route)->getPath();
  if ($path !== $expected) {
    throw new \RuntimeException("Unexpected route path for " . $route . ": " . $path . " expected " . $expected);
  }
}
echo "routes-ok";
' | grep -Fq 'routes-ok'

say "Rendering admin workspace pages and checking clickable links..."
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
  if (!is_numeric($count)) {
    throw new \RuntimeException("Count is not numeric for " . $bundle);
  }
}
echo "counts-ok";
' | grep -Fq 'counts-ok'

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
  "/api/v1/pages/home?site=yamltest"
  "/api/v1/routes/home?site=yamltest"
  "/api/v1/menus?site=yamltest"
  "/api/v1/menus/main?site=yamltest"
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
    say "Response body for failed API check (${path}):"
    cat "$TMP_OUT" || true
    fail "API endpoint failed: ${path} returned HTTP ${code}"
  fi
done

rm -f "$TMP_OUT"

say "Checking repository diff formatting..."
git diff --check

say "Site Platform final completion verification passed."
