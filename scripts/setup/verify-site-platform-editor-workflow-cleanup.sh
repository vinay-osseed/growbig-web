#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

say() { printf '%s\n' "$*"; }
fail() { say "FAILED: $*"; exit 1; }

api_controller="site-platform/web/modules/custom/site_platform_native/src/Controller/NativeApiController.php"
admin_controller="site-platform/web/modules/custom/site_platform_native/src/Controller/NativeAdminController.php"
subscriber="site-platform/web/modules/custom/site_platform_native/src/EventSubscriber/EditorLoginRedirectSubscriber.php"

for file in \
  docs/admin/site-platform-editor-workflow-cleanup-and-api-hotfix.md \
  scripts/setup/finalize-site-platform-editor-workflow-cleanup.sh \
  scripts/setup/site_platform_patch_editor_workflow_source.py \
  scripts/setup/site_platform_cleanup_editor_workflow.php \
  scripts/setup/verify-site-platform-editor-workflow-cleanup.sh \
  site-platform/web/modules/custom/site_platform_native/site_platform_native.module \
  site-platform/web/modules/custom/site_platform_native/site_platform_native.services.yml \
  "$subscriber" \
  "$api_controller" \
  "$admin_controller"; do
  [ -f "$file" ] || fail "Missing required file: $file"
done

php -l "$api_controller" >/dev/null
php -l "$admin_controller" >/dev/null
php -l "$subscriber" >/dev/null
php -l site-platform/web/modules/custom/site_platform_native/site_platform_native.module >/dev/null
php -l scripts/setup/site_platform_cleanup_editor_workflow.php >/dev/null

if ! grep -Fq "private function json(array \$data" "$api_controller"; then
  fail "Native API JSON helper missing."
fi

if grep -Fq "site_platform_admin.legacy_wrappers" site-platform/web/modules/custom/site_platform_admin/site_platform_admin.links.menu.yml; then
  fail "Legacy Wrappers menu link still exists."
fi

if grep -Fq "site_platform_admin.legacy_wrappers" site-platform/web/modules/custom/site_platform_admin/site_platform_admin.routing.yml; then
  fail "Legacy Wrappers route still exists."
fi

say "Rebuilding Drupal cache..."
ddev drush cr >/dev/null

say "Checking legacy content types are removed from active editor workflow..."
ddev drush ev '
foreach (["site_menu", "site_menu_item", "site_form", "site_form_field", "site_form_submission"] as $bundle) {
  if (\Drupal\node\Entity\NodeType::load($bundle)) {
    throw new \RuntimeException("Legacy content type still active: " . $bundle);
  }
}
echo "legacy-clean";
' | grep -Fq 'legacy-clean'

say "Checking Dataset type dropdown has real options..."
ddev drush ev '
$storage = \Drupal\field\Entity\FieldStorageConfig::loadByName("node", "field_dataset_type");
if (!$storage) { throw new \RuntimeException("Missing field_dataset_type storage."); }
$values = $storage->getSetting("allowed_values") ?: [];
foreach (["list", "cards", "table", "pricing", "locations", "careers", "custom"] as $key) {
  if (!isset($values[$key])) { throw new \RuntimeException("Missing dataset type option " . $key); }
}
$field = \Drupal\field\Entity\FieldConfig::loadByName("node", "site_data_set", "field_dataset_type");
if (!$field || !$field->isRequired()) { throw new \RuntimeException("Dataset type must be required."); }
echo "dataset-options-ok";
' | grep -Fq 'dataset-options-ok'

say "Checking Domain is visible on Site Profile form and Start Here renders..."
ddev drush ev '
if (\Drupal\field\Entity\FieldConfig::loadByName("node", "site_profile", "field_site_domain")) {
  $display = \Drupal\Core\Entity\Entity\EntityFormDisplay::load("node.site_profile.default");
  if (!$display || !$display->getComponent("field_site_domain")) {
    throw new \RuntimeException("Domain field is not visible on Site Profile form.");
  }
}
$controller = \Drupal::service("class_resolver")->getInstanceFromDefinition(\Drupal\site_platform_native\Controller\NativeAdminController::class);
$out = (string) \Drupal::service("renderer")->renderRoot($controller->start());
foreach (["Sites and Domains", "Open Domain settings", "API Guide"] as $needle) {
  if (strpos($out, $needle) === FALSE) { throw new \RuntimeException("Start page missing " . $needle); }
}
echo "start-ok";
' | grep -Fq 'start-ok'

say "Checking API index endpoints..."
BASE_URL="${BASE_URL:-https://site-platform.ddev.site}"
SITE_KEY="$(ddev drush ev '
$storage = \Drupal::entityTypeManager()->getStorage("node");
$ids = $storage->getQuery()->accessCheck(FALSE)->condition("type", "site_profile")->range(0, 1)->execute();
foreach ($storage->loadMultiple($ids) as $site) {
  if ($site->hasField("field_site_key") && !$site->get("field_site_key")->isEmpty()) { echo trim($site->get("field_site_key")->getString()); return; }
}
echo "growbig";
')"
TMP_OUT="${TMPDIR:-/tmp}/site_platform_editor_workflow_cleanup_api_check.out"
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
say "Site Platform editor workflow cleanup verification passed."
