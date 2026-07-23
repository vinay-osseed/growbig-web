#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

say() { printf '%s\n' "$*"; }
fail() { say "FAILED: $*"; exit 1; }

controller="site-platform/web/modules/custom/site_platform_admin/src/Controller/SitePlatformAdminController.php"
module="site-platform/web/modules/custom/site_platform_admin/site_platform_admin.module"
apply_script="scripts/setup/site_platform_apply_editor_experience.php"

for file in \
  "$module" \
  "$apply_script" \
  "scripts/setup/verify-site-platform-editor-experience.sh" \
  "site-platform/web/modules/custom/site_platform_admin/css/site-platform-admin.css" \
  "$controller"; do
  [ -f "$file" ] || fail "Missing required file: $file"
done

php -l "$module" >/dev/null
php -l "$apply_script" >/dev/null
php -l "$controller" >/dev/null

grep -Fq "site_platform_admin_page_handoff_markup" "$module" || fail "Page handoff helper missing."
grep -Fq "field_site_logo_media" "$apply_script" || fail "Logo media field setup missing."
grep -Fq "field_site_favicon_media" "$apply_script" || fail "Favicon media field setup missing."
grep -Fq "field_social_links_items" "$apply_script" || fail "Structured social links field setup missing."
grep -Fq "field_component_media" "$apply_script" || fail "Hero media field setup missing."
grep -Fq "options_select" "$apply_script" || fail "Dropdown widget setup missing."
grep -Fq "media_library_widget" "$apply_script" || fail "Media library widget setup missing."

say "Rebuilding Drupal cache..."
ddev drush cr >/dev/null

say "Checking required modules are enabled..."
ddev drush ev '
foreach (["media", "media_library", "link", "image", "file"] as $module) {
  if (!\Drupal::moduleHandler()->moduleExists($module)) {
    throw new \RuntimeException($module . " is not enabled.");
  }
}
echo "modules-ok";
' | grep -Fq 'modules-ok'

say "Checking editor-facing fields exist..."
ddev drush ev '
$required = [
  ["node", "site_profile", "field_site_logo_media"],
  ["node", "site_profile", "field_site_favicon_media"],
  ["node", "site_profile", "field_social_links_items"],
  ["node", "site_media_asset", "field_media_entity"],
  ["paragraph", "site_hero", "field_component_media"],
];
foreach ($required as [$entity_type, $bundle, $field]) {
  if (!\Drupal\field\Entity\FieldConfig::loadByName($entity_type, $bundle, $field)) {
    throw new \RuntimeException("Missing field " . implode(".", [$entity_type, $bundle, $field]));
  }
}
echo "fields-ok";
' | grep -Fq 'fields-ok'

say "Checking active editor form widgets..."
ddev drush ev '
$checks = [
  "node.site_profile.default" => [
    "field_site_logo_media" => "media_library_widget",
    "field_site_favicon_media" => "media_library_widget",
    "field_social_links_items" => "link_default",
  ],
  "node.site_page.default" => [
    "field_site_profile" => "options_select",
    "field_page_components" => "paragraphs",
  ],
  "node.site_media_asset.default" => [
    "field_site_profile" => "options_select",
    "field_media_entity" => "media_library_widget",
  ],
  "paragraph.site_hero.default" => [
    "field_component_media" => "media_library_widget",
  ],
];
foreach ($checks as $display_id => $fields) {
  $display = \Drupal\Core\Entity\Entity\EntityFormDisplay::load($display_id);
  if (!$display) {
    throw new \RuntimeException("Missing display " . $display_id);
  }
  foreach ($fields as $field => $widget) {
    $component = $display->getComponent($field);
    if (!$component || ($component["type"] ?? "") !== $widget) {
      throw new \RuntimeException("Unexpected widget for " . $display_id . ":" . $field);
    }
  }
}
echo "widgets-ok";
' | grep -Fq 'widgets-ok'

say "Checking old temporary fields are hidden from primary forms..."
ddev drush ev '
$checks = [
  "node.site_profile.default" => ["field_logo_url", "field_favicon_url", "field_social_links"],
  "node.site_page.default" => ["field_page_template", "field_page_weight", "field_is_demo", "field_demo_source"],
  "paragraph.site_hero.default" => ["field_component_media_url"],
];
foreach ($checks as $display_id => $fields) {
  $display = \Drupal\Core\Entity\Entity\EntityFormDisplay::load($display_id);
  foreach ($fields as $field) {
    if ($display && $display->getComponent($field)) {
      throw new \RuntimeException("Field should be hidden from primary form: " . $display_id . ":" . $field);
    }
  }
}
echo "hidden-ok";
' | grep -Fq 'hidden-ok'

say "Checking page add form renders with editor guide..."
ddev drush ev '
$node = \Drupal\node\Entity\Node::create(["type" => "site_page", "title" => "Verifier page"]);
$form = \Drupal::service("entity.form_builder")->getForm($node, "default");
$out = (string) \Drupal::service("renderer")->renderRoot($form);
foreach (["Frontend / API handoff", "field_site_profile", "field_page_components"] as $needle) {
  if (strpos($out, $needle) === FALSE) {
    throw new \RuntimeException("Missing page form output: " . $needle);
  }
}
echo "page-form-ok";
' | grep -Fq 'page-form-ok'

say "Checking repository diff formatting..."
git diff --check

say "Site Platform editor experience verification passed."
