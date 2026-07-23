<?php

/**
 * Applies the final editor-facing Site Platform backend experience.
 *
 * Run with:
 *   ddev drush scr scripts/setup/site_platform_apply_editor_experience.php
 */

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\media\Entity\MediaType;
use Drupal\node\Entity\NodeType;

$changed = [];
$module_installer = \Drupal::service('module_installer');
$modules = ['file', 'image', 'media', 'media_library', 'link', 'options'];
$missing = array_values(array_filter($modules, static fn (string $module): bool => !\Drupal::moduleHandler()->moduleExists($module)));
if ($missing) {
  $module_installer->install($missing, TRUE);
  $changed[] = 'modules: ' . implode(', ', $missing);
}

site_platform_ensure_image_media_type($changed);
site_platform_ensure_media_reference_field('node', 'site_profile', 'field_site_logo_media', 'Logo', 'Select the site logo from the Media Library.', $changed);
site_platform_ensure_media_reference_field('node', 'site_profile', 'field_site_favicon_media', 'Favicon', 'Select the favicon/icon from the Media Library.', $changed);
site_platform_ensure_link_field('node', 'site_profile', 'field_social_links_items', 'Social links', 'Add one social profile per row. Use the link title for the platform name, for example LinkedIn, X, Facebook, Instagram.', $changed);
site_platform_ensure_media_reference_field('node', 'site_media_asset', 'field_media_entity', 'Media Library item', 'Select the managed Drupal media item.', $changed);
site_platform_ensure_media_reference_field('paragraph', 'site_hero', 'field_component_media', 'Media', 'Select hero image/video from the Media Library.', $changed);

site_platform_apply_site_profile_display($changed);
site_platform_apply_page_display($changed);
site_platform_apply_media_asset_display($changed);
site_platform_apply_paragraph_displays($changed);
site_platform_apply_other_dropdown_displays($changed);

\Drupal::service('cache.discovery')->deleteAll();
\Drupal::service('router.builder')->rebuild();

print "Applied final editor-facing backend experience for:\n";
foreach ($changed as $item) {
  print "- {$item}\n";
}

/**
 * Ensures an Image media type exists.
 */
function site_platform_ensure_image_media_type(array &$changed): void {
  if (!class_exists(MediaType::class)) {
    return;
  }

  if (!MediaType::load('image')) {
    $media_type = MediaType::create([
      'id' => 'image',
      'label' => 'Image',
      'description' => 'Reusable image media for Site Platform pages and branding.',
      'source' => 'image',
      'queue_thumbnail_downloads' => FALSE,
      'new_revision' => TRUE,
      'source_configuration' => [
        'source_field' => 'field_media_image',
      ],
      'field_map' => [
        'name' => 'name',
      ],
    ]);
    $media_type->save();
    $changed[] = 'media.type.image';
  }

  if (!FieldStorageConfig::loadByName('media', 'field_media_image')) {
    FieldStorageConfig::create([
      'entity_type' => 'media',
      'field_name' => 'field_media_image',
      'type' => 'image',
      'settings' => [
        'uri_scheme' => 'public',
        'default_image' => [],
      ],
      'cardinality' => 1,
    ])->save();
    $changed[] = 'field.storage.media.field_media_image';
  }

  if (!FieldConfig::loadByName('media', 'image', 'field_media_image')) {
    FieldConfig::create([
      'entity_type' => 'media',
      'bundle' => 'image',
      'field_name' => 'field_media_image',
      'label' => 'Image',
      'required' => TRUE,
      'settings' => [
        'file_extensions' => 'png gif jpg jpeg webp svg',
        'file_directory' => 'media/images/[date:custom:Y]-[date:custom:m]',
        'max_filesize' => '',
        'alt_field' => TRUE,
        'alt_field_required' => TRUE,
        'title_field' => FALSE,
        'title_field_required' => FALSE,
      ],
    ])->save();
    $changed[] = 'field.field.media.image.field_media_image';
  }
}

/**
 * Ensures a media reference field exists on a bundle.
 */
function site_platform_ensure_media_reference_field(string $entity_type, string $bundle, string $field_name, string $label, string $description, array &$changed): void {
  if (!FieldStorageConfig::loadByName($entity_type, $field_name)) {
    FieldStorageConfig::create([
      'entity_type' => $entity_type,
      'field_name' => $field_name,
      'type' => 'entity_reference',
      'cardinality' => 1,
      'settings' => [
        'target_type' => 'media',
      ],
    ])->save();
    $changed[] = 'field.storage.' . $entity_type . '.' . $field_name;
  }

  if (!FieldConfig::loadByName($entity_type, $bundle, $field_name)) {
    FieldConfig::create([
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'field_name' => $field_name,
      'label' => $label,
      'description' => $description,
      'required' => FALSE,
      'settings' => [
        'handler' => 'default:media',
        'handler_settings' => [
          'target_bundles' => ['image' => 'image'],
          'auto_create' => FALSE,
        ],
      ],
    ])->save();
    $changed[] = 'field.field.' . $entity_type . '.' . $bundle . '.' . $field_name;
  }
}

/**
 * Ensures a structured link field exists.
 */
function site_platform_ensure_link_field(string $entity_type, string $bundle, string $field_name, string $label, string $description, array &$changed): void {
  if (!FieldStorageConfig::loadByName($entity_type, $field_name)) {
    FieldStorageConfig::create([
      'entity_type' => $entity_type,
      'field_name' => $field_name,
      'type' => 'link',
      'cardinality' => -1,
      'settings' => [],
    ])->save();
    $changed[] = 'field.storage.' . $entity_type . '.' . $field_name;
  }

  if (!FieldConfig::loadByName($entity_type, $bundle, $field_name)) {
    FieldConfig::create([
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'field_name' => $field_name,
      'label' => $label,
      'description' => $description,
      'required' => FALSE,
      'settings' => [
        'title' => DRUPAL_OPTIONAL,
        'link_type' => 17,
      ],
    ])->save();
    $changed[] = 'field.field.' . $entity_type . '.' . $bundle . '.' . $field_name;
  }
}

/**
 * Applies Site Profile editor display.
 */
function site_platform_apply_site_profile_display(array &$changed): void {
  $fields = [
    ['title', 'string_textfield'],
    ['field_site_key', 'string_textfield'],
    ['field_is_active', 'boolean_checkbox'],
    ['field_is_default', 'boolean_checkbox'],
    ['field_site_logo_media', 'media_library_widget'],
    ['field_site_favicon_media', 'media_library_widget'],
    ['field_brand_primary_color', 'string_textfield'],
    ['field_frontend_domains', 'string_textarea'],
    ['field_api_domains', 'string_textarea'],
    ['field_admin_domains', 'string_textarea'],
    ['field_contact_email', 'email_default'],
    ['field_contact_phone', 'string_textfield'],
    ['field_contact_address', 'string_textarea'],
    ['field_social_links_items', 'link_default'],
    ['field_ga_measurement_id', 'string_textfield'],
    ['field_gtm_container_id', 'string_textfield'],
    ['field_default_language', 'string_textfield'],
    ['field_enabled_languages', 'string_textarea'],
    ['field_timezone', 'string_textfield'],
  ];
  site_platform_apply_form_display('node', 'site_profile', $fields, ['field_logo_url', 'field_favicon_url', 'field_social_links', 'field_site_weight', 'field_is_demo', 'field_demo_source']);
  site_platform_apply_view_display('node', 'site_profile', $fields, ['field_logo_url', 'field_favicon_url', 'field_social_links', 'field_site_weight', 'field_is_demo', 'field_demo_source']);
  $changed[] = 'editor-display.node.site_profile';
}

/**
 * Applies Landing Page editor display.
 */
function site_platform_apply_page_display(array &$changed): void {
  $fields = [
    ['title', 'string_textfield'],
    ['field_site_profile', 'options_select'],
    ['field_page_key', 'string_textfield'],
    ['field_page_slug', 'string_textfield'],
    ['field_page_path', 'string_textfield'],
    ['field_seo_title', 'string_textfield'],
    ['field_seo_description', 'string_textarea'],
    ['field_page_components', 'paragraphs'],
  ];
  site_platform_apply_form_display('node', 'site_page', $fields, ['field_page_template', 'field_page_weight', 'field_is_demo', 'field_demo_source']);
  site_platform_apply_view_display('node', 'site_page', $fields, ['field_page_template', 'field_page_weight', 'field_is_demo', 'field_demo_source']);
  $changed[] = 'editor-display.node.site_page';
}

/**
 * Applies Media Asset editor display.
 */
function site_platform_apply_media_asset_display(array &$changed): void {
  $fields = [
    ['title', 'string_textfield'],
    ['field_site_profile', 'options_select'],
    ['field_media_entity', 'media_library_widget'],
    ['field_media_key', 'string_textfield'],
    ['field_media_kind', 'options_select'],
    ['field_media_alt', 'string_textfield'],
    ['field_media_credit', 'string_textfield'],
    ['field_media_is_active', 'boolean_checkbox'],
    ['field_media_url', 'string_textarea'],
  ];
  site_platform_apply_form_display('node', 'site_media_asset', $fields, ['field_media_weight', 'field_is_demo', 'field_demo_source']);
  site_platform_apply_view_display('node', 'site_media_asset', $fields, ['field_media_weight', 'field_is_demo', 'field_demo_source']);
  $changed[] = 'editor-display.node.site_media_asset';
}

/**
 * Applies paragraph editor displays.
 */
function site_platform_apply_paragraph_displays(array &$changed): void {
  $hero = [
    ['field_component_admin_label', 'string_textfield'],
    ['field_component_key', 'string_textfield'],
    ['field_component_title', 'string_textfield'],
    ['field_component_summary', 'string_textarea'],
    ['field_component_body', 'string_textarea'],
    ['field_component_media', 'media_library_widget'],
    ['field_component_button_label', 'string_textfield'],
    ['field_component_button_path', 'string_textfield'],
    ['field_component_variant', 'string_textfield'],
  ];
  site_platform_apply_form_display('paragraph', 'site_hero', $hero, ['field_component_media_url']);
  site_platform_apply_view_display('paragraph', 'site_hero', $hero, ['field_component_media_url']);

  $rich = [
    ['field_component_admin_label', 'string_textfield'],
    ['field_component_key', 'string_textfield'],
    ['field_component_title', 'string_textfield'],
    ['field_component_body', 'string_textarea'],
    ['field_component_variant', 'string_textfield'],
  ];
  site_platform_apply_form_display('paragraph', 'site_rich_text', $rich, []);
  site_platform_apply_view_display('paragraph', 'site_rich_text', $rich, []);

  $cta = [
    ['field_component_admin_label', 'string_textfield'],
    ['field_component_key', 'string_textfield'],
    ['field_component_title', 'string_textfield'],
    ['field_component_summary', 'string_textarea'],
    ['field_component_button_label', 'string_textfield'],
    ['field_component_button_path', 'string_textfield'],
    ['field_component_variant', 'string_textfield'],
  ];
  site_platform_apply_form_display('paragraph', 'site_cta', $cta, []);
  site_platform_apply_view_display('paragraph', 'site_cta', $cta, []);
  $changed[] = 'editor-display.paragraphs';
}

/**
 * Improves reference widgets on the remaining content types.
 */
function site_platform_apply_other_dropdown_displays(array &$changed): void {
  $maps = [
    'site_content_block' => [
      ['title', 'string_textfield'],
      ['field_site_profile', 'options_select'],
      ['field_content_source', 'string_textfield'],
      ['field_content_key', 'string_textfield'],
      ['field_content_label', 'string_textfield'],
      ['field_content_variant', 'string_textfield'],
      ['field_content_summary', 'string_textarea'],
      ['field_content_body', 'string_textarea'],
      ['field_content_is_active', 'boolean_checkbox'],
    ],
    'site_menu' => [
      ['title', 'string_textfield'],
      ['field_site_profile', 'options_select'],
      ['field_menu_key', 'string_textfield'],
      ['field_menu_label', 'string_textfield'],
      ['field_menu_is_active', 'boolean_checkbox'],
    ],
    'site_form' => [
      ['title', 'string_textfield'],
      ['field_site_profile', 'options_select'],
      ['field_form_key', 'string_textfield'],
      ['field_form_label', 'string_textfield'],
      ['field_form_description', 'string_textarea'],
      ['field_form_success_message', 'string_textarea'],
      ['field_form_is_active', 'boolean_checkbox'],
    ],
  ];
  foreach ($maps as $bundle => $fields) {
    if (NodeType::load($bundle)) {
      site_platform_apply_form_display('node', $bundle, $fields, ['field_is_demo', 'field_demo_source', 'field_content_weight', 'field_menu_weight']);
      site_platform_apply_view_display('node', $bundle, $fields, ['field_is_demo', 'field_demo_source', 'field_content_weight', 'field_menu_weight']);
      $changed[] = 'editor-display.node.' . $bundle;
    }
  }
}

/**
 * Applies form display components.
 */
function site_platform_apply_form_display(string $entity_type, string $bundle, array $fields, array $remove): void {
  $id = $entity_type . '.' . $bundle . '.default';
  $display = EntityFormDisplay::load($id) ?: EntityFormDisplay::create([
    'targetEntityType' => $entity_type,
    'bundle' => $bundle,
    'mode' => 'default',
    'status' => TRUE,
  ]);

  $weight = -10;
  foreach ($fields as [$field_name, $widget]) {
    if ($field_name !== 'title' && !FieldConfig::loadByName($entity_type, $bundle, $field_name)) {
      continue;
    }
    $display->setComponent($field_name, [
      'type' => $widget,
      'weight' => $weight,
      'region' => 'content',
      'settings' => site_platform_widget_settings($widget),
      'third_party_settings' => [],
    ]);
    $weight++;
  }

  foreach (array_merge(['created', 'path', 'promote', 'sticky', 'uid'], $remove) as $hidden) {
    $display->removeComponent($hidden);
  }
  $display->save();
}

/**
 * Applies view display components.
 */
function site_platform_apply_view_display(string $entity_type, string $bundle, array $fields, array $remove): void {
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
    if (!FieldConfig::loadByName($entity_type, $bundle, $field_name)) {
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

  foreach (array_merge(['links'], $remove) as $hidden) {
    $display->removeComponent($hidden);
  }
  $display->save();
}

/**
 * Widget settings by widget type.
 */
function site_platform_widget_settings(string $widget): array {
  return match ($widget) {
    'string_textfield' => ['size' => 60, 'placeholder' => ''],
    'string_textarea' => ['rows' => 5, 'placeholder' => ''],
    'entity_reference_autocomplete' => ['match_operator' => 'CONTAINS', 'match_limit' => 10, 'size' => 60, 'placeholder' => ''],
    'boolean_checkbox' => ['display_label' => TRUE],
    'number' => ['placeholder' => ''],
    'email_default' => ['size' => 60, 'placeholder' => ''],
    'options_select' => [],
    'link_default' => ['placeholder_url' => '', 'placeholder_title' => 'Platform name, e.g. LinkedIn'],
    'media_library_widget' => ['media_types' => ['image']],
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

/**
 * Formatter settings by field.
 */
function site_platform_view_formatter(string $field_name): array {
  if (str_contains($field_name, 'media')) {
    return ['type' => 'entity_reference_entity_view', 'settings' => ['view_mode' => 'default', 'link' => FALSE]];
  }
  if (str_contains($field_name, 'social_links_items')) {
    return ['type' => 'link', 'settings' => ['trim_length' => 80, 'url_only' => FALSE, 'url_plain' => FALSE, 'rel' => '', 'target' => '_blank']];
  }
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
