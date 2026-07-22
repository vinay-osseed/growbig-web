#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

say() { printf '%s\n' "$*"; }
fail() { say "FAILED: $*"; exit 1; }

api_controller="site-platform/web/modules/custom/site_platform_native/src/Controller/NativeApiController.php"
admin_controller="site-platform/web/modules/custom/site_platform_native/src/Controller/NativeAdminController.php"
routes="site-platform/web/modules/custom/site_platform_native/site_platform_native.routing.yml"

for file in \
  docs/admin/site-platform-api-index-and-native-module-usage.md \
  scripts/setup/finalize-site-platform-api-index.sh \
  scripts/setup/verify-site-platform-api-index.sh \
  "$api_controller" \
  "$admin_controller" \
  "$routes"; do
  [ -f "$file" ] || fail "Missing required file: $file"
done

php -l "$api_controller" >/dev/null
php -l "$admin_controller" >/dev/null

grep -Fq "api_pages" "$routes" || fail "Missing /api/v2/pages route."
grep -Fq "api_forms" "$routes" || fail "Missing /api/v2/forms route."
grep -Fq "api_datasets" "$routes" || fail "Missing /api/v2/datasets route."
grep -Fq "public function pages" "$api_controller" || fail "Missing pages index controller."
grep -Fq "public function forms" "$api_controller" || fail "Missing forms index controller."
grep -Fq "public function datasets" "$api_controller" || fail "Missing datasets index controller."
grep -Fq "domain_alias" "$api_controller" || fail "Domain Alias support is not represented."
grep -Fq "enabledFeatureSummary" "$api_controller" || fail "Enabled feature summary missing."

say "Rebuilding Drupal cache..."
ddev drush cr >/dev/null

say "Checking enabled native/contrib modules that should now be used..."
ddev drush ev '
$required = ["domain", "domain_access", "domain_alias", "domain_config", "domain_content", "domain_source", "rest", "basic_auth", "webform", "webform_node", "webform_schema", "jsonapi", "menu_ui", "media_library", "metatag", "redirect"];
$missing = [];
foreach ($required as $module) {
  if (!\Drupal::moduleHandler()->moduleExists($module)) {
    $missing[] = $module;
  }
}
if ($missing) {
  throw new \RuntimeException("Missing expected enabled modules: " . implode(", ", $missing));
}
echo "modules-ok";
' | grep -Fq 'modules-ok'

say "Checking API routes exist..."
ddev drush ev '
$routes = [
  "site_platform_native.api_bootstrap",
  "site_platform_native.api_pages",
  "site_platform_native.api_page",
  "site_platform_native.api_navigation",
  "site_platform_native.api_forms",
  "site_platform_native.api_form",
  "site_platform_native.api_datasets",
  "site_platform_native.api_dataset",
  "site_platform_native.api_careers",
];
foreach ($routes as $route) {
  \Drupal::service("router.route_provider")->getRouteByName($route);
}
echo "routes-ok";
' | grep -Fq 'routes-ok'

SITE_KEY="$(ddev drush ev '
$storage = \Drupal::entityTypeManager()->getStorage("node");
$ids = $storage->getQuery()->accessCheck(FALSE)->condition("type", "site_profile")->range(0, 1)->execute();
foreach ($storage->loadMultiple($ids) as $site) {
  if ($site->hasField("field_site_key") && !$site->get("field_site_key")->isEmpty()) { echo trim($site->get("field_site_key")->getString()); return; }
}
echo "growbig";
')"

BASE_URL="${BASE_URL:-https://site-platform.ddev.site}"
TMP_OUT="${TMPDIR:-/tmp}/site_platform_api_index_check.out"

say "Checking index and detail API endpoints for site ${SITE_KEY} at ${BASE_URL}..."
for path in \
  "/api/v2/bootstrap?site=${SITE_KEY}" \
  "/api/v2/pages?site=${SITE_KEY}" \
  "/api/v2/pages?site=${SITE_KEY}&include=full" \
  "/api/v2/navigation?site=${SITE_KEY}&menu=main" \
  "/api/v2/forms?site=${SITE_KEY}" \
  "/api/v2/datasets?site=${SITE_KEY}"; do
  code="$(curl -k -sS -o "$TMP_OUT" -w '%{http_code}' "${BASE_URL}${path}" || true)"
  if [ "$code" != "200" ]; then
    say "Response body for failed API check (${path}):"
    cat "$TMP_OUT" || true
    fail "API endpoint failed: ${path} returned HTTP ${code}"
  fi
done
rm -f "$TMP_OUT"

git diff --check
say "Site Platform API index verification passed."
