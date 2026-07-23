#!/usr/bin/env bash
set -Eeuo pipefail
ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"
say() { printf '%s\n' "$*"; }
fail() { say "FAILED: $*"; exit 1; }

for file in \
  docs/admin/site-platform-drupal-native-platform-rebuild.md \
  scripts/setup/site_platform_install_drupal_native_stack.sh \
  scripts/setup/site_platform_apply_domain_native_workflow.php \
  scripts/setup/verify-site-platform-drupal-native.sh \
  site-platform/web/modules/custom/site_platform_native/site_platform_native.info.yml \
  site-platform/web/modules/custom/site_platform_native/site_platform_native.routing.yml \
  site-platform/web/modules/custom/site_platform_native/src/Controller/NativeApiController.php \
  site-platform/web/modules/custom/site_platform_native/src/Controller/NativeAdminController.php; do
  [ -f "$file" ] || fail "Missing required file: $file"
done

php -l site-platform/web/modules/custom/site_platform_native/src/Controller/NativeApiController.php >/dev/null
php -l site-platform/web/modules/custom/site_platform_native/src/Controller/NativeAdminController.php >/dev/null
php -l site-platform/web/modules/custom/site_platform_native/site_platform_native.module >/dev/null
php -l scripts/setup/site_platform_apply_domain_native_workflow.php >/dev/null

ddev drush cr >/dev/null

say "Checking Drupal-native modules..."
ddev drush ev '
foreach (["domain", "domain_access", "layout_paragraphs", "pathauto", "metatag", "redirect", "simple_sitemap", "tour", "jsonapi", "menu_ui", "media_library", "webform"] as $module) {
  if (!\Drupal::moduleHandler()->moduleExists($module)) {
    throw new \RuntimeException($module . " is not enabled.");
  }
}
echo "modules-ok";
' | grep -Fq 'modules-ok'

say "Checking native routes..."
ddev drush ev '
$routes = ["site_platform_native.start", "site_platform_native.api_docs", "site_platform_native.api_bootstrap", "site_platform_native.api_page", "site_platform_native.api_navigation", "site_platform_native.api_form", "site_platform_native.api_dataset", "site_platform_native.api_careers"];
foreach ($routes as $route) {
  \Drupal::service("router.route_provider")->getRouteByName($route);
}
echo "routes-ok";
' | grep -Fq 'routes-ok'

say "Checking flexible dataset model..."
ddev drush ev '
if (!\Drupal\node\Entity\NodeType::load("site_data_set")) { throw new \RuntimeException("Missing Site Data Set content type."); }
if (!\Drupal\paragraphs\Entity\ParagraphsType::load("site_data_item")) { throw new \RuntimeException("Missing Site Data Item paragraph type."); }
foreach (["field_site_profile", "field_dataset_key", "field_dataset_type", "field_dataset_items"] as $field) {
  if (!\Drupal\field\Entity\FieldConfig::loadByName("node", "site_data_set", $field)) {
    throw new \RuntimeException("Missing dataset field " . $field);
  }
}
echo "dataset-ok";
' | grep -Fq 'dataset-ok'

say "Checking clean API responses..."
SITE_KEY="$(ddev drush ev '
$storage = \Drupal::entityTypeManager()->getStorage("node");
$ids = $storage->getQuery()->accessCheck(FALSE)->condition("type", "site_profile")->range(0, 1)->execute();
foreach ($storage->loadMultiple($ids) as $site) {
  if ($site->hasField("field_site_key") && !$site->get("field_site_key")->isEmpty()) { echo trim($site->get("field_site_key")->getString()); return; }
}
echo "growbig";
')"
BASE_URL="${BASE_URL:-https://site-platform.ddev.site}"
TMP_OUT="${TMPDIR:-/tmp}/site_platform_native_api_check.out"
for path in "/api/v2/bootstrap?site=${SITE_KEY}" "/api/v2/navigation?site=${SITE_KEY}&menu=main"; do
  code="$(curl -k -sS -o "$TMP_OUT" -w '%{http_code}' "${BASE_URL}${path}" || true)"
  if [ "$code" != "200" ]; then
    cat "$TMP_OUT" || true
    fail "API endpoint failed: ${path} returned ${code}"
  fi
done
rm -f "$TMP_OUT"

git diff --check
say "Drupal-native Site Platform verification passed."
