#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

BASE_URL="${BASE_URL:-https://site-platform.ddev.site}"
TMP_OUT="${TMPDIR:-/tmp}/site_platform_backend_usable_api_check.out"
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
  "docs/admin/site-platform-backend-usability-completion.md"
  "site-platform/web/modules/custom/site_platform_admin/site_platform_admin.module"
  "site-platform/web/modules/custom/site_platform_admin/site_platform_admin.libraries.yml"
  "site-platform/web/modules/custom/site_platform_admin/css/site-platform-admin.css"
  "scripts/setup/finalize-backend-admin-usability.sh"
  "scripts/setup/site_platform_apply_admin_ux.php"
  "scripts/setup/site_platform_delete_demo_data.php"
  "scripts/setup/verify-backend-admin-usable.sh"
  "$controller"
)

for file in "${required_files[@]}"; do
  require_file "$file"
done

say "Checking PHP syntax..."
php -l "$controller" >/dev/null
php -l scripts/setup/site_platform_apply_admin_ux.php >/dev/null
php -l scripts/setup/site_platform_delete_demo_data.php >/dev/null
php -l site-platform/web/modules/custom/site_platform_admin/site_platform_admin.module >/dev/null

say "Checking safe admin controller links..."
grep -Fq "function linksCell" "$controller" || fail "Clickable link cell helper missing."
grep -Fq "function nodeActionsCell" "$controller" || fail "Node action helper missing."
grep -Fq "Html::escape" "$controller" || fail "HTML escaping missing."
grep -Fq "Markup::create" "$controller" || fail "Safe markup helper missing."

if grep -Fq "Drupal\\Core\\Url" "$controller"; then
  fail "Unsafe Url import found in controller."
fi

if grep -Fq "fromTextAndUrl" "$controller"; then
  fail "Unsafe Link::fromTextAndUrl usage found in controller."
fi

say "Checking admin CSS library..."
grep -Fq "site_platform_admin/admin_workspace" site-platform/web/modules/custom/site_platform_admin/site_platform_admin.module || fail "Admin workspace CSS library is not attached."
grep -Fq "site-platform-admin-actions" site-platform/web/modules/custom/site_platform_admin/css/site-platform-admin.css || fail "Admin action CSS missing."

say "Rebuilding Drupal cache..."
ddev drush cr >/dev/null

say "Checking active edit-form display config..."
ddev drush ev '
$required = [
  "node.site_profile.default" => ["field_site_key", "field_frontend_domains", "field_contact_email"],
  "node.site_page.default" => ["field_site_profile", "field_page_path", "field_seo_title", "field_page_components"],
  "node.site_content_block.default" => ["field_content_source", "field_content_key", "field_content_body"],
  "node.site_media_asset.default" => ["field_media_key", "field_media_kind", "field_media_url"],
  "node.site_menu.default" => ["field_menu_key", "field_menu_is_active"],
  "node.site_menu_item.default" => ["field_menu_title", "field_menu_path", "field_menu_weight"],
  "node.site_form.default" => ["field_form_key", "field_form_label", "field_form_is_active"],
  "node.site_form_field.default" => ["field_form_field_key", "field_form_field_type", "field_form_field_required"],
  "paragraph.site_hero.default" => ["field_component_title", "field_component_body", "field_component_button_path"],
  "paragraph.site_rich_text.default" => ["field_component_title", "field_component_body"],
  "paragraph.site_cta.default" => ["field_component_title", "field_component_button_label", "field_component_button_path"],
];
foreach ($required as $display_id => $fields) {
  $display = \Drupal\Core\Entity\Entity\EntityFormDisplay::load($display_id);
  if (!$display) {
    throw new \RuntimeException("Missing form display " . $display_id);
  }
  foreach ($fields as $field) {
    if (!$display->getComponent($field)) {
      throw new \RuntimeException("Missing " . $field . " on " . $display_id);
    }
  }
}
echo "form-display-ok";
' | grep -Fq 'form-display-ok'

say "Checking active view display config..."
ddev drush ev '
$required = [
  "node.site_page.default" => ["field_site_profile", "field_page_path", "field_seo_title", "field_page_components"],
  "node.site_content_block.default" => ["field_content_source", "field_content_key", "field_content_body"],
  "node.site_media_asset.default" => ["field_media_key", "field_media_url"],
];
foreach ($required as $display_id => $fields) {
  $display = \Drupal\Core\Entity\Entity\EntityViewDisplay::load($display_id);
  if (!$display) {
    throw new \RuntimeException("Missing view display " . $display_id);
  }
  foreach ($fields as $field) {
    if (!$display->getComponent($field)) {
      throw new \RuntimeException("Missing " . $field . " on " . $display_id);
    }
  }
}
echo "view-display-ok";
' | grep -Fq 'view-display-ok'

say "Checking content type labels..."
ddev drush ev '
$expected = [
  "site_profile" => "Site Profile",
  "site_page" => "Landing Page",
  "site_content_block" => "Content Block",
  "site_media_asset" => "Media Asset Metadata",
  "site_menu" => "Legacy API Menu Wrapper",
  "site_form" => "Legacy API Form Wrapper",
];
foreach ($expected as $bundle => $label) {
  $type = \Drupal\node\Entity\NodeType::load($bundle);
  if (!$type || $type->label() !== $label) {
    throw new \RuntimeException("Unexpected label for " . $bundle);
  }
}
echo "labels-ok";
' | grep -Fq 'labels-ok'

say "Checking admin routes and rendered CRUD links..."
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
    throw new \RuntimeException("Unexpected route path for " . $route . ": " . $path);
  }
}
$controller = \Drupal::service("class_resolver")->getInstanceFromDefinition(\Drupal\site_platform_admin\Controller\SitePlatformAdminController::class);
foreach (["overview", "sites", "pages", "contentBlocks", "mediaAssets", "forms", "menus", "legacyWrappers"] as $method) {
  $build = $controller->{$method}();
  $out = (string) \Drupal::service("renderer")->renderRoot($build);
  if (strpos($out, "href=") === FALSE) {
    throw new \RuntimeException("No clickable links rendered for " . $method);
  }
}
echo "admin-render-ok";
' | grep -Fq 'admin-render-ok'

say "Checking demo/test data cleanup..."
ddev drush ev '
$storage = \Drupal::entityTypeManager()->getStorage("node");
$bundles = ["site_profile", "site_page", "site_content_block", "site_media_asset", "site_menu", "site_menu_item", "site_form", "site_form_field", "site_form_submission"];
$bad = [];
foreach ($bundles as $bundle) {
  $ids = $storage->getQuery()->accessCheck(FALSE)->condition("type", $bundle)->execute();
  foreach ($storage->loadMultiple($ids) as $node) {
    if ($node->hasField("field_is_demo") && !$node->get("field_is_demo")->isEmpty() && (bool) $node->get("field_is_demo")->value) {
      $bad[] = $node->id() . ":" . $bundle . ":demo";
    }
    if ($node->hasField("field_demo_source") && trim((string) $node->get("field_demo_source")->getString()) !== "") {
      $bad[] = $node->id() . ":" . $bundle . ":demo_source";
    }
    if ($bundle === "site_profile" && $node->hasField("field_site_key")) {
      $key = strtolower(trim((string) $node->get("field_site_key")->getString()));
      if ($key === "yamltest" || str_contains($key, "demo")) {
        $bad[] = $node->id() . ":" . $bundle . ":" . $key;
      }
    }
  }
}
if ($bad) {
  throw new \RuntimeException("Demo/test records still found: " . implode(", ", $bad));
}
echo "demo-clean-ok";
' | grep -Fq 'demo-clean-ok'

say "Checking Webform availability..."
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

say "Finding non-demo Site Profile for API smoke..."
SITE_KEY="$(ddev drush ev '
$storage = \Drupal::entityTypeManager()->getStorage("node");
$ids = $storage->getQuery()->accessCheck(FALSE)->condition("type", "site_profile")->sort("title")->execute();
foreach ($storage->loadMultiple($ids) as $node) {
  if (!$node->hasField("field_site_key") || $node->get("field_site_key")->isEmpty()) {
    continue;
  }
  $key = trim((string) $node->get("field_site_key")->getString());
  $lower = strtolower($key);
  if ($key !== "" && $lower !== "yamltest" && !str_contains($lower, "demo") && !str_contains($lower, "test")) {
    echo $key;
    return;
  }
}
')"

if [ -z "$SITE_KEY" ]; then
  fail "No non-demo Site Profile key found for API smoke. Add a real Site Profile first."
fi

say "Checking core public API endpoints for site ${SITE_KEY} at ${BASE_URL}..."
api_paths=(
  "/api/v1/site?site=${SITE_KEY}"
  "/api/v1/pages?site=${SITE_KEY}"
  "/api/v1/menus?site=${SITE_KEY}"
  "/api/v1/content?site=${SITE_KEY}"
  "/api/v1/media?site=${SITE_KEY}"
  "/api/v1/seo?site=${SITE_KEY}"
  "/api/v1/analytics?site=${SITE_KEY}"
  "/api/v1/search?site=${SITE_KEY}&q=brochure"
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

say "Backend admin usability verification passed."
