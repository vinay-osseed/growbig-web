<?php

/**
 * Applies active Site Platform admin/edit UX configuration safely.
 *
 * Run with:
 *   ddev drush scr scripts/setup/site_platform_apply_admin_ux.php
 */

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\node\Entity\NodeType;

$changed = [];

function site_platform_bundle_label_updates(): array {
  return [
    'site_profile' => [
      'name' => 'Site Profile',
      'description' => 'Top-level brand/site/domain profile. One profile powers one isolated frontend site.',
      'help' => 'Create one Site Profile per brand or landing-page site. Pages, content blocks, media assets, and form wrappers reference this profile.',
    ],
    'site_page' => [
      'name' => 'Landing Page',
      'description' => 'Frontend page record with route path, SEO metadata, template, and ordered Paragraph sections.',
      'help' => 'Use this for real frontend pages. Attach Hero, Rich Text, CTA, and future component paragraphs in Page components.',
    ],
    'site_content_block' => [
      'name' => 'Content Block',
      'description' => 'Reusable source/key content exposed through the Site Platform content API.',
      'help' => 'Use this for shared footer notes, announcements, office information, and other reusable API content.',
    ],
    'site_media_asset' => [
      'name' => 'Media Asset Metadata',
      'description' => 'Frontend-safe media metadata exposed by the media API. Binary files should move toward Drupal Media Library.',
      'help' => 'Use this for API media references. Store title, key, kind, URL, alt text, credit, and active state.',
    ],
    'site_menu' => [
      'name' => 'Legacy API Menu Wrapper',
      'description' => 'Compatibility menu wrapper used by the current API/YAML import. Long-term editor workflow should use Drupal core menus.',
      'help' => 'This is a compatibility wrapper, not the preferred editor workflow. Use Site Platform > Menus to review it.',
    ],
    'site_menu_item' => [
      'name' => 'Legacy API Menu Item Wrapper',
      'description' => 'Compatibility menu item wrapper used by the current API/YAML import. Long-term editor workflow should use Drupal core menu links.',
      'help' => 'This is a compatibility wrapper, not the preferred editor workflow. Use Site Platform > Menus to review it.',
    ],
    'site_form' => [
      'name' => 'Legacy API Form Wrapper',
      'description' => 'Compatibility form wrapper kept for API fallback. Real forms should use Drupal Webform.',
      'help' => 'Do not build production forms here. Build production forms in Drupal Webform and keep this only as an API compatibility wrapper when needed.',
    ],
    'site_form_field' => [
      'name' => 'Legacy API Form Field Wrapper',
      'description' => 'Compatibility form field wrapper kept for API fallback. Real fields should live in Drupal Webform.',
      'help' => 'Do not build production fields here. Use Drupal Webform fields instead.',
    ],
    'site_form_submission' => [
      'name' => 'Legacy API Form Submission Fallback',
      'description' => 'Fallback submission node used only when no matching Webform exists. Real submissions should live in Webform submissions.',
      'help' => 'Production submissions should be stored in Webform. This content type exists only as a fallback compatibility layer.',
    ],
  ];
}

function site_platform_node_form_map(): array {
  return [
    'site_profile' => [
      ['title', 'string_textfield'],
      ['field_site_key', 'string_textfield'],
      ['field_is_active', 'boolean_checkbox'],
      ['field_is_default', 'boolean_checkbox'],
      ['field_site_weight', 'number'],
      ['field_frontend_domains', 'string_textarea'],
      ['field_api_domains', 'string_textarea'],
      ['field_admin_domains', 'string_textarea'],
      ['field_default_language', 'string_textfield'],
      ['field_enabled_languages', 'string_textarea'],
      ['field_timezone', 'string_textfield'],
      ['field_brand_primary_color', 'string_textfield'],
      ['field_logo_url', 'string_textfield'],
      ['field_favicon_url', 'string_textfield'],
      ['field_contact_email', 'email_default'],
      ['field_contact_phone', 'string_textfield'],
      ['field_contact_address', 'string_textarea'],
      ['field_social_links', 'string_textarea'],
      ['field_ga_measurement_id', 'string_textfield'],
      ['field_gtm_container_id', 'string_textfield'],
    ],
    'site_page' => [
      ['title', 'string_textfield'],
      ['field_site_profile', 'entity_reference_autocomplete'],
      ['field_page_key', 'string_textfield'],
      ['field_page_slug', 'string_textfield'],
      ['field_page_path', 'string_textfield'],
      ['field_page_template', 'string_textfield'],
      ['field_page_weight', 'number'],
      ['field_seo_title', 'string_textfield'],
      ['field_seo_description', 'string_textarea'],
      ['field_page_components', 'paragraphs'],
      ['field_is_demo', 'boolean_checkbox'],
      ['field_demo_source', 'string_textfield'],
    ],
    'site_content_block' => [
      ['title', 'string_textfield'],
      ['field_site_profile', 'entity_reference_autocomplete'],
      ['field_content_source', 'string_textfield'],
      ['field_content_key', 'string_textfield'],
      ['field_content_label', 'string_textfield'],
      ['field_content_variant', 'string_textfield'],
      ['field_content_summary', 'string_textarea'],
      ['field_content_body', 'string_textarea'],
      ['field_content_weight', 'number'],
      ['field_content_is_active', 'boolean_checkbox'],
      ['field_is_demo', 'boolean_checkbox'],
      ['field_demo_source', 'string_textfield'],
    ],
    'site_media_asset' => [
      ['title', 'string_textfield'],
      ['field_site_profile', 'entity_reference_autocomplete'],
      ['field_media_key', 'string_textfield'],
      ['field_media_kind', 'options_select'],
      ['field_media_url', 'string_textarea'],
      ['field_media_alt', 'string_textfield'],
      ['field_media_credit', 'string_textfield'],
      ['field_media_weight', 'number'],
      ['field_media_is_active', 'boolean_checkbox'],
      ['field_is_demo', 'boolean_checkbox'],
      ['field_demo_source', 'string_textfield'],
    ],
    'site_menu' => [
      ['title', 'string_textfield'],
      ['field_site_profile', 'entity_reference_autocomplete'],
      ['field_menu_key', 'string_textfield'],
      ['field_menu_label', 'string_textfield'],
      ['field_menu_weight', 'number'],
      ['field_menu_is_active', 'boolean_checkbox'],
      ['field_is_demo', 'boolean_checkbox'],
      ['field_demo_source', 'string_textfield'],
    ],
    'site_menu_item' => [
      ['title', 'string_textfield'],
      ['field_site_profile', 'entity_reference_autocomplete'],
      ['field_site_menu', 'entity_reference_autocomplete'],
      ['field_menu_item_key', 'string_textfield'],
      ['field_menu_title', 'string_textfield'],
      ['field_menu_link_type', 'string_textfield'],
      ['field_menu_page', 'entity_reference_autocomplete'],
      ['field_menu_path', 'string_textfield'],
      ['field_menu_external_url', 'string_textfield'],
      ['field_menu_anchor', 'string_textfield'],
      ['field_menu_parent', 'entity_reference_autocomplete'],
      ['field_menu_target', 'string_textfield'],
      ['field_menu_is_button', 'boolean_checkbox'],
      ['field_menu_weight', 'number'],
      ['field_menu_is_enabled', 'boolean_checkbox'],
      ['field_is_demo', 'boolean_checkbox'],
      ['field_demo_source', 'string_textfield'],
    ],
    'site_form' => [
      ['title', 'string_textfield'],
      ['field_site_profile', 'entity_reference_autocomplete'],
      ['field_form_key', 'string_textfield'],
      ['field_form_label', 'string_textfield'],
      ['field_form_description', 'string_textarea'],
      ['field_form_success_message', 'string_textarea'],
      ['field_form_is_active', 'boolean_checkbox'],
      ['field_is_demo', 'boolean_checkbox'],
      ['field_demo_source', 'string_textfield'],
    ],
    'site_form_field' => [
      ['title', 'string_textfield'],
      ['field_site_profile', 'entity_reference_autocomplete'],
      ['field_site_form', 'entity_reference_autocomplete'],
      ['field_form_field_key', 'string_textfield'],
      ['field_form_field_label', 'string_textfield'],
      ['field_form_field_type', 'string_textfield'],
      ['field_form_field_required', 'boolean_checkbox'],
      ['field_form_field_placeholder', 'string_textfield'],
      ['field_form_field_help', 'string_textarea'],
      ['field_form_field_options', 'string_textarea'],
      ['field_form_field_weight', 'number'],
      ['field_is_demo', 'boolean_checkbox'],
      ['field_demo_source', 'string_textfield'],
    ],
    'site_form_submission' => [
      ['title', 'string_textfield'],
      ['field_site_profile', 'entity_reference_autocomplete'],
      ['field_site_form', 'entity_reference_autocomplete'],
      ['field_submission_status', 'string_textfield'],
      ['field_submission_payload', 'string_textarea'],
    ],
  ];
}

function site_platform_paragraph_form_map(): array {
  return [
    'site_hero' => [
      ['field_component_admin_label', 'string_textfield'],
      ['field_component_key', 'string_textfield'],
      ['field_component_variant', 'string_textfield'],
      ['field_component_title', 'string_textfield'],
      ['field_component_summary', 'string_textarea'],
      ['field_component_body', 'string_textarea'],
      ['field_component_media_url', 'string_textfield'],
      ['field_component_button_label', 'string_textfield'],
      ['field_component_button_path', 'string_textfield'],
    ],
    'site_rich_text' => [
      ['field_component_admin_label', 'string_textfield'],
      ['field_component_key', 'string_textfield'],
      ['field_component_variant', 'string_textfield'],
      ['field_component_title', 'string_textfield'],
      ['field_component_body', 'string_textarea'],
    ],
    'site_cta' => [
      ['field_component_admin_label', 'string_textfield'],
      ['field_component_key', 'string_textfield'],
      ['field_component_variant', 'string_textfield'],
      ['field_component_title', 'string_textfield'],
      ['field_component_summary', 'string_textarea'],
      ['field_component_button_label', 'string_textfield'],
      ['field_component_button_path', 'string_textfield'],
    ],
  ];
}

function site_platform_widget_settings(string $widget): array {
  return match ($widget) {
    'string_textfield' => ['size' => 60, 'placeholder' => ''],
    'string_textarea' => ['rows' => 5, 'placeholder' => ''],
    'entity_reference_autocomplete' => ['match_operator' => 'CONTAINS', 'match_limit' => 10, 'size' => 60, 'placeholder' => ''],
    'boolean_checkbox' => ['display_label' => TRUE],
    'number' => ['placeholder' => ''],
    'email_default' => ['size' => 60, 'placeholder' => ''],
    'paragraphs' => [
      'title' => 'Page section',
      'title_plural' => 'Page sections',
      'edit_mode' => 'open',
      'closed_mode' => 'summary',
      'autocollapse' => 'none',
      'closed_mode_threshold' => 0,
      'add_mode' => 'dropdown',
      'form_display_mode' => 'default',
      'default_paragraph_type' => '',
      'features' => [
        'add_above' => TRUE,
        'collapse_edit_all' => TRUE,
        'duplicate' => TRUE,
      ],
    ],
    default => [],
  };
}

function site_platform_view_formatter(string $field_name): array {
  if (str_contains($field_name, 'weight')) {
    return ['type' => 'number_integer', 'settings' => ['thousand_separator' => '', 'prefix_suffix' => TRUE]];
  }
  if (str_contains($field_name, 'is_') || str_ends_with($field_name, '_active') || str_ends_with($field_name, '_enabled') || str_ends_with($field_name, '_required') || str_ends_with($field_name, '_button')) {
    return ['type' => 'boolean', 'settings' => ['format' => 'yes-no', 'format_custom_true' => '', 'format_custom_false' => '']];
  }
  if ($field_name === 'field_site_profile' || str_contains($field_name, '_page') || str_contains($field_name, '_menu') || str_contains($field_name, '_form')) {
    return ['type' => 'entity_reference_label', 'settings' => ['link' => TRUE]];
  }
  if ($field_name === 'field_page_components') {
    return ['type' => 'entity_reference_revisions_entity_view', 'settings' => ['view_mode' => 'default', 'link' => FALSE]];
  }
  return ['type' => 'basic_string', 'settings' => []];
}

function site_platform_apply_form_display(string $entity_type, string $bundle, array $fields): void {
  $id = $entity_type . '.' . $bundle . '.default';
  $display = EntityFormDisplay::load($id) ?: EntityFormDisplay::create([
    'targetEntityType' => $entity_type,
    'bundle' => $bundle,
    'mode' => 'default',
    'status' => TRUE,
  ]);

  $weight = -10;
  foreach ($fields as [$field_name, $widget]) {
    $display->setComponent($field_name, [
      'type' => $widget,
      'weight' => $weight,
      'region' => 'content',
      'settings' => site_platform_widget_settings($widget),
      'third_party_settings' => [],
    ]);
    $weight++;
  }

  foreach (['created', 'path', 'promote', 'sticky', 'uid'] as $hidden) {
    $display->removeComponent($hidden);
  }
  $display->save();
}

function site_platform_apply_view_display(string $entity_type, string $bundle, array $fields): void {
  $id = $entity_type . '.' . $bundle . '.default';
  $display = EntityViewDisplay::load($id) ?: EntityViewDisplay::create([
    'targetEntityType' => $entity_type,
    'bundle' => $bundle,
    'mode' => 'default',
    'status' => TRUE,
  ]);

  $weight = 0;
  foreach ($fields as [$field_name]) {
    if ($field_name === 'title') {
      continue;
    }
    $formatter = site_platform_view_formatter($field_name);
    $display->setComponent($field_name, [
      'type' => $formatter['type'],
      'label' => 'above',
      'weight' => $weight,
      'region' => 'content',
      'settings' => $formatter['settings'],
      'third_party_settings' => [],
    ]);
    $weight++;
  }
  $display->removeComponent('links');
  $display->save();
}

foreach (site_platform_bundle_label_updates() as $bundle => $info) {
  $type = NodeType::load($bundle);
  if (!$type) {
    continue;
  }
  $type->set('name', $info['name']);
  $type->set('description', $info['description']);
  $type->set('help', $info['help']);
  $type->set('new_revision', TRUE);
  $type->set('display_submitted', FALSE);
  $type->save();
  $changed[] = 'node.type.' . $bundle;
}

foreach (site_platform_node_form_map() as $bundle => $fields) {
  if (NodeType::load($bundle)) {
    site_platform_apply_form_display('node', $bundle, $fields);
    site_platform_apply_view_display('node', $bundle, $fields);
    $changed[] = 'display.node.' . $bundle;
  }
}

$paragraph_storage = \Drupal::entityTypeManager()->getStorage('paragraphs_type');
foreach (site_platform_paragraph_form_map() as $bundle => $fields) {
  if ($paragraph_storage->load($bundle)) {
    site_platform_apply_form_display('paragraph', $bundle, $fields);
    site_platform_apply_view_display('paragraph', $bundle, $fields);
    $changed[] = 'display.paragraph.' . $bundle;
  }
}

\Drupal::service('cache.discovery')->deleteAll();
\Drupal::service('router.builder')->rebuild();

print "Applied active admin/editor UX config for:\n";
foreach ($changed as $item) {
  print "- {$item}\n";
}
