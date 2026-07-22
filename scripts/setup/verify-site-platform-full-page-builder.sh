#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

say() { printf '%s\n' "$*"; }
fail() { say "FAILED: $*"; exit 1; }

apply_script="scripts/setup/site_platform_apply_full_page_builder.php"
module="site-platform/web/modules/custom/site_platform_admin/site_platform_admin.module"
css="site-platform/web/modules/custom/site_platform_admin/css/site-platform-admin.css"

for file in \
  "docs/admin/site-platform-full-page-builder-completion.md" \
  "scripts/setup/finalize-site-platform-full-page-builder.sh" \
  "scripts/setup/verify-site-platform-full-page-builder.sh" \
  "$apply_script" \
  "$module" \
  "$css"; do
  [ -f "$file" ] || fail "Missing required file: $file"
done

php -l "$apply_script" >/dev/null
php -l "$module" >/dev/null

grep -Fq "site_platform_admin_page_builder_guide_markup" "$module" || fail "Page builder guide helper missing."
grep -Fq "field_component_items" "$apply_script" || fail "Nested component item field setup missing."
grep -Fq "site_card_grid" "$apply_script" || fail "Card Grid setup missing."
grep -Fq "site_partner_strip" "$apply_script" || fail "Partner Strip setup missing."
grep -Fq "site_form_embed" "$apply_script" || fail "Webform Embed setup missing."
grep -Fq "site-platform-builder-guide" "$css" || fail "Page builder guide CSS missing."

say "Rebuilding Drupal cache..."
ddev drush cr >/dev/null

say "Checking full page-builder paragraph bundles..."
ddev drush ev '
$required = [
  "site_section_header",
  "site_card_grid",
  "site_card_item",
  "site_partner_strip",
  "site_partner_item",
  "site_stats_grid",
  "site_stat_item",
  "site_image_text",
  "site_process_steps",
  "site_step_item",
  "site_testimonials",
  "site_testimonial_item",
  "site_faq",
  "site_faq_item",
  "site_contact_block",
  "site_form_embed",
];
$storage = \Drupal::entityTypeManager()->getStorage("paragraphs_type");
foreach ($required as $bundle) {
  if (!$storage->load($bundle)) {
    throw new \RuntimeException("Missing paragraph bundle " . $bundle);
  }
}
echo "bundles-ok";
' | grep -Fq 'bundles-ok'

say "Checking Landing Page component picker supports all major frontend sections..."
ddev drush ev '
$field = \Drupal\field\Entity\FieldConfig::loadByName("node", "site_page", "field_page_components");
if (!$field) {
  throw new \RuntimeException("Missing page components field.");
}
$settings = $field->getSetting("handler_settings") ?: [];
$target_bundles = $settings["target_bundles"] ?? [];
foreach (["site_hero", "site_section_header", "site_card_grid", "site_partner_strip", "site_stats_grid", "site_image_text", "site_cta", "site_process_steps", "site_testimonials", "site_faq", "site_contact_block", "site_form_embed"] as $bundle) {
  if (!isset($target_bundles[$bundle])) {
    throw new \RuntimeException("Landing Page does not allow component " . $bundle);
  }
}
echo "targets-ok";
' | grep -Fq 'targets-ok'

say "Checking nested child components and widgets..."
ddev drush ev '
$checks = [
  "paragraph.site_card_grid.default" => ["field_component_items" => "paragraphs"],
  "paragraph.site_card_item.default" => ["field_component_title" => "string_textfield"],
  "paragraph.site_partner_strip.default" => ["field_component_items" => "paragraphs"],
  "paragraph.site_partner_item.default" => ["field_component_media" => "media_library_widget"],
  "paragraph.site_form_embed.default" => ["field_component_form" => "options_select"],
  "paragraph.site_contact_block.default" => ["field_contact_email" => "email_default"],
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

say "Checking page add form shows the page-builder guide and component selector..."
ddev drush ev '
$node = \Drupal\node\Entity\Node::create(["type" => "site_page", "title" => "Verifier page"]);
$form = \Drupal::service("entity.form_builder")->getForm($node, "default");
$out = (string) \Drupal::service("renderer")->renderRoot($form);
foreach (["Page Builder Guide", "Card Grid", "Partner / Logo Strip", "field_page_components"] as $needle) {
  if (strpos($out, $needle) === FALSE) {
    throw new \RuntimeException("Missing page builder output: " . $needle);
  }
}
echo "page-form-ok";
' | grep -Fq 'page-form-ok'

say "Checking repository diff formatting..."
git diff --check

say "Site Platform full page builder verification passed."
