<?php

/**
 * Applies the full Site Platform page builder component model.
 *
 * Run with:
 *   ddev drush scr scripts/setup/site_platform_apply_full_page_builder.php
 */

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;
use Drupal\paragraphs\Entity\ParagraphsType;

$changed = [];

$module_installer = \Drupal::service('module_installer');
$required_modules = ['paragraphs', 'entity_reference_revisions', 'media', 'media_library', 'link', 'options', 'text', 'file', 'image'];
$missing = array_values(array_filter($required_modules, static fn (string $module): bool => !\Drupal::moduleHandler()->moduleExists($module)));
if ($missing) {
  $module_installer->install($missing, TRUE);
  $changed[] = 'modules: ' . implode(', ', $missing);
}

site_platform_pb_ensure_paragraph_types($changed);
site_platform_pb_ensure_common_fields($changed);
site_platform_pb_update_page_component_targets($changed);
site_platform_pb_apply_displays($changed);

\Drupal::service('cache.discovery')->deleteAll();
\Drupal::service('router.builder')->rebuild();

print "Applied full Site Platform page builder model for:\n";
foreach ($changed as $item) {
  print "- {$item}\n";
}

/**
 * Paragraph bundles needed for GrowBig-style pages and common landing pages.
 */
function site_platform_pb_paragraph_types(): array {
  return [
    'site_section_header' => ['Section Header', 'Eyebrow, title, and intro text for a page section.'],
    'site_card_grid' => ['Card Grid', 'Reusable grid/list section for services, features, industries, careers, and similar cards.'],
    'site_card_item' => ['Card Item', 'One card inside a card grid.'],
    'site_partner_strip' => ['Partner / Logo Strip', 'Partner/logo strip or ecosystem row.'],
    'site_partner_item' => ['Partner / Logo Item', 'One partner, logo, badge, or country item.'],
    'site_stats_grid' => ['Stats Grid', 'KPI/statistics section.'],
    'site_stat_item' => ['Stat Item', 'One statistic inside a stats grid.'],
    'site_image_text' => ['Image + Text Section', 'Two-column image and text section.'],
    'site_process_steps' => ['Process / Steps Section', 'Process, how-it-works, timeline, or step list section.'],
    'site_step_item' => ['Step Item', 'One step inside a process section.'],
    'site_testimonials' => ['Testimonials Section', 'Testimonials or review carousel section.'],
    'site_testimonial_item' => ['Testimonial Item', 'One testimonial quote.'],
    'site_faq' => ['FAQ Section', 'Frequently asked questions section.'],
    'site_faq_item' => ['FAQ Item', 'One question and answer.'],
    'site_contact_block' => ['Contact / Office Info Section', 'Contact details, office information, and map/embed content.'],
    'site_form_embed' => ['Webform Embed Section', 'Reference a Drupal Webform inside a landing page.'],
  ];
}

/**
 * Creates missing paragraph bundles.
 */
function site_platform_pb_ensure_paragraph_types(array &$changed): void {
  foreach (site_platform_pb_paragraph_types() as $id => [$label, $description]) {
    if (!ParagraphsType::load($id)) {
      ParagraphsType::create([
        'id' => $id,
        'label' => $label,
        'description' => $description,
      ])->save();
      $changed[] = 'paragraphs.type.' . $id;
    }
  }
}

/**
 * Field map by paragraph bundle.
 */
function site_platform_pb_paragraph_field_map(): array {
  return [
    'site_section_header' => [
      ['field_component_admin_label', 'string', 'Admin label'],
      ['field_component_key', 'string', 'Component key'],
      ['field_component_eyebrow', 'string', 'Eyebrow / label'],
      ['field_component_title', 'string', 'Title'],
      ['field_component_summary', 'string_long', 'Intro / summary'],
      ['field_component_alignment', 'list_string', 'Alignment'],
      ['field_component_variant', 'string', 'Variant'],
    ],
    'site_card_grid' => [
      ['field_component_admin_label', 'string', 'Admin label'],
      ['field_component_key', 'string', 'Component key'],
      ['field_component_eyebrow', 'string', 'Eyebrow / label'],
      ['field_component_title', 'string', 'Title'],
      ['field_component_summary', 'string_long', 'Intro / summary'],
      ['field_component_items', 'entity_reference_revisions', 'Cards'],
      ['field_component_columns', 'list_string', 'Columns'],
      ['field_component_variant', 'string', 'Variant'],
    ],
    'site_card_item' => [
      ['field_component_admin_label', 'string', 'Admin label'],
      ['field_component_key', 'string', 'Item key'],
      ['field_component_icon', 'string', 'Icon key / class'],
      ['field_component_media', 'entity_reference', 'Media'],
      ['field_component_title', 'string', 'Title'],
      ['field_component_summary', 'string_long', 'Summary'],
      ['field_component_button_label', 'string', 'Link label'],
      ['field_component_button_path', 'string', 'Link path'],
      ['field_component_variant', 'string', 'Variant'],
    ],
    'site_partner_strip' => [
      ['field_component_admin_label', 'string', 'Admin label'],
      ['field_component_key', 'string', 'Component key'],
      ['field_component_eyebrow', 'string', 'Eyebrow / label'],
      ['field_component_title', 'string', 'Title'],
      ['field_component_summary', 'string_long', 'Intro / summary'],
      ['field_component_items', 'entity_reference_revisions', 'Partners / logos'],
      ['field_component_variant', 'string', 'Variant'],
    ],
    'site_partner_item' => [
      ['field_component_admin_label', 'string', 'Admin label'],
      ['field_component_key', 'string', 'Item key'],
      ['field_component_media', 'entity_reference', 'Logo / image'],
      ['field_component_title', 'string', 'Partner / label'],
      ['field_component_summary', 'string_long', 'Subtitle / description'],
      ['field_component_button_path', 'string', 'Link path'],
    ],
    'site_stats_grid' => [
      ['field_component_admin_label', 'string', 'Admin label'],
      ['field_component_key', 'string', 'Component key'],
      ['field_component_title', 'string', 'Title'],
      ['field_component_summary', 'string_long', 'Intro / summary'],
      ['field_component_items', 'entity_reference_revisions', 'Stats'],
      ['field_component_variant', 'string', 'Variant'],
    ],
    'site_stat_item' => [
      ['field_component_admin_label', 'string', 'Admin label'],
      ['field_component_key', 'string', 'Item key'],
      ['field_stat_value', 'string', 'Value'],
      ['field_stat_label', 'string', 'Label'],
      ['field_component_summary', 'string_long', 'Description'],
    ],
    'site_image_text' => [
      ['field_component_admin_label', 'string', 'Admin label'],
      ['field_component_key', 'string', 'Component key'],
      ['field_component_media', 'entity_reference', 'Image / media'],
      ['field_component_eyebrow', 'string', 'Eyebrow / label'],
      ['field_component_title', 'string', 'Title'],
      ['field_component_body', 'string_long', 'Body'],
      ['field_component_button_label', 'string', 'Button label'],
      ['field_component_button_path', 'string', 'Button path'],
      ['field_component_variant', 'string', 'Variant'],
    ],
    'site_process_steps' => [
      ['field_component_admin_label', 'string', 'Admin label'],
      ['field_component_key', 'string', 'Component key'],
      ['field_component_eyebrow', 'string', 'Eyebrow / label'],
      ['field_component_title', 'string', 'Title'],
      ['field_component_summary', 'string_long', 'Intro / summary'],
      ['field_component_items', 'entity_reference_revisions', 'Steps'],
    ],
    'site_step_item' => [
      ['field_component_admin_label', 'string', 'Admin label'],
      ['field_component_key', 'string', 'Step key'],
      ['field_step_number', 'string', 'Step number'],
      ['field_component_title', 'string', 'Title'],
      ['field_component_summary', 'string_long', 'Description'],
    ],
    'site_testimonials' => [
      ['field_component_admin_label', 'string', 'Admin label'],
      ['field_component_key', 'string', 'Component key'],
      ['field_component_title', 'string', 'Title'],
      ['field_component_summary', 'string_long', 'Intro / summary'],
      ['field_component_items', 'entity_reference_revisions', 'Testimonials'],
    ],
    'site_testimonial_item' => [
      ['field_component_admin_label', 'string', 'Admin label'],
      ['field_component_key', 'string', 'Item key'],
      ['field_component_body', 'string_long', 'Quote'],
      ['field_person_name', 'string', 'Name'],
      ['field_person_title', 'string', 'Title / company'],
      ['field_component_media', 'entity_reference', 'Photo / logo'],
    ],
    'site_faq' => [
      ['field_component_admin_label', 'string', 'Admin label'],
      ['field_component_key', 'string', 'Component key'],
      ['field_component_title', 'string', 'Title'],
      ['field_component_summary', 'string_long', 'Intro / summary'],
      ['field_component_items', 'entity_reference_revisions', 'Questions'],
    ],
    'site_faq_item' => [
      ['field_component_admin_label', 'string', 'Admin label'],
      ['field_component_key', 'string', 'Question key'],
      ['field_component_title', 'string', 'Question'],
      ['field_component_body', 'string_long', 'Answer'],
    ],
    'site_contact_block' => [
      ['field_component_admin_label', 'string', 'Admin label'],
      ['field_component_key', 'string', 'Component key'],
      ['field_component_title', 'string', 'Title'],
      ['field_component_summary', 'string_long', 'Intro / summary'],
      ['field_contact_phone', 'string', 'Phone'],
      ['field_contact_email', 'email', 'Email'],
      ['field_contact_address', 'string_long', 'Address'],
      ['field_component_embed', 'string_long', 'Map / embed / notes'],
      ['field_component_button_label', 'string', 'Button label'],
      ['field_component_button_path', 'string', 'Button path'],
    ],
    'site_form_embed' => [
      ['field_component_admin_label', 'string', 'Admin label'],
      ['field_component_key', 'string', 'Component key'],
      ['field_component_title', 'string', 'Title'],
      ['field_component_summary', 'string_long', 'Intro / summary'],
      ['field_component_form', 'entity_reference', 'Webform'],
    ],
  ];
}

/**
 * Creates all missing fields on paragraph bundles.
 */
function site_platform_pb_ensure_common_fields(array &$changed): void {
  foreach (site_platform_pb_paragraph_field_map() as $bundle => $fields) {
    foreach ($fields as [$field_name, $type, $label]) {
      site_platform_pb_ensure_field('paragraph', $bundle, $field_name, $type, $label, $changed);
    }
  }
}

/**
 * Creates a field storage/config if missing.
 */
function site_platform_pb_ensure_field(string $entity_type, string $bundle, string $field_name, string $type, string $label, array &$changed): void {
  if (!FieldStorageConfig::loadByName($entity_type, $field_name)) {
    $storage = [
      'entity_type' => $entity_type,
      'field_name' => $field_name,
      'type' => $type,
      'cardinality' => site_platform_pb_cardinality($field_name, $type),
      'settings' => site_platform_pb_storage_settings($type),
    ];
    FieldStorageConfig::create($storage)->save();
    $changed[] = 'field.storage.' . $entity_type . '.' . $field_name;
  }

  if (!FieldConfig::loadByName($entity_type, $bundle, $field_name)) {
    FieldConfig::create([
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'field_name' => $field_name,
      'label' => $label,
      'required' => site_platform_pb_required($field_name, $bundle),
      'description' => site_platform_pb_field_description($field_name, $bundle),
      'settings' => site_platform_pb_field_settings($type, $field_name, $bundle),
    ])->save();
    $changed[] = 'field.field.' . $entity_type . '.' . $bundle . '.' . $field_name;
  }
}

/**
 * Field cardinality.
 */
function site_platform_pb_cardinality(string $field_name, string $type): int {
  if ($field_name === 'field_component_items') {
    return -1;
  }
  return 1;
}

/**
 * Field storage settings.
 */
function site_platform_pb_storage_settings(string $type): array {
  return match ($type) {
    'entity_reference' => ['target_type' => 'media'],
    'entity_reference_revisions' => ['target_type' => 'paragraph'],
    'list_string' => ['allowed_values' => [], 'allowed_values_function' => ''],
    default => [],
  };
}

/**
 * Bundle field settings.
 */
function site_platform_pb_field_settings(string $type, string $field_name, string $bundle): array {
  if ($field_name === 'field_component_form') {
    return [
      'handler' => 'default:webform',
      'handler_settings' => [],
    ];
  }
  if ($type === 'entity_reference') {
    return [
      'handler' => 'default:media',
      'handler_settings' => [
        'target_bundles' => ['image' => 'image'],
        'auto_create' => FALSE,
      ],
    ];
  }
  if ($type === 'entity_reference_revisions') {
    return [
      'handler' => 'default:paragraph',
      'handler_settings' => [
        'target_bundles' => site_platform_pb_child_bundles($bundle),
        'negate' => 0,
        'target_bundles_drag_drop' => [],
      ],
    ];
  }
  if ($type === 'list_string') {
    $values = $field_name === 'field_component_columns'
      ? ['2' => '2 columns', '3' => '3 columns', '4' => '4 columns']
      : ['left' => 'Left', 'center' => 'Center', 'right' => 'Right'];
    return ['allowed_values' => $values, 'allowed_values_function' => ''];
  }
  return [];
}

/**
 * Child bundles allowed under a parent component.
 */
function site_platform_pb_child_bundles(string $bundle): array {
  return match ($bundle) {
    'site_card_grid' => ['site_card_item' => 'site_card_item'],
    'site_partner_strip' => ['site_partner_item' => 'site_partner_item'],
    'site_stats_grid' => ['site_stat_item' => 'site_stat_item'],
    'site_process_steps' => ['site_step_item' => 'site_step_item'],
    'site_testimonials' => ['site_testimonial_item' => 'site_testimonial_item'],
    'site_faq' => ['site_faq_item' => 'site_faq_item'],
    default => [],
  };
}

/**
 * Required fields.
 */
function site_platform_pb_required(string $field_name, string $bundle): bool {
  return in_array($field_name, ['field_component_key', 'field_component_title'], TRUE)
    && !in_array($bundle, ['site_stat_item'], TRUE);
}

/**
 * Field descriptions.
 */
function site_platform_pb_field_description(string $field_name, string $bundle): string {
  return match ($field_name) {
    'field_component_key' => 'Stable key used by the frontend/API. Example: services, partners, web-development.',
    'field_component_admin_label' => 'Internal label to make the page builder easy to scan.',
    'field_component_variant' => 'Optional frontend style variant. Example: default, dark, light, compact.',
    'field_component_items' => 'Add and reorder child items for this section.',
    'field_component_media' => 'Select image/logo/media from the Drupal Media Library.',
    default => '',
  };
}

/**
 * Updates Landing Page component field target bundles.
 */
function site_platform_pb_update_page_component_targets(array &$changed): void {
  $field = FieldConfig::loadByName('node', 'site_page', 'field_page_components');
  if (!$field) {
    return;
  }

  $target_bundles = [
    'site_hero' => 'site_hero',
    'site_section_header' => 'site_section_header',
    'site_card_grid' => 'site_card_grid',
    'site_partner_strip' => 'site_partner_strip',
    'site_stats_grid' => 'site_stats_grid',
    'site_image_text' => 'site_image_text',
    'site_rich_text' => 'site_rich_text',
    'site_cta' => 'site_cta',
    'site_process_steps' => 'site_process_steps',
    'site_testimonials' => 'site_testimonials',
    'site_faq' => 'site_faq',
    'site_contact_block' => 'site_contact_block',
    'site_form_embed' => 'site_form_embed',
  ];

  $settings = $field->getSetting('handler_settings') ?: [];
  $settings['target_bundles'] = $target_bundles;
  $settings['negate'] = 0;
  $settings['target_bundles_drag_drop'] = [];
  foreach ($target_bundles as $bundle => $value) {
    $settings['target_bundles_drag_drop'][$bundle] = [
      'enabled' => TRUE,
      'weight' => array_search($bundle, array_keys($target_bundles), TRUE) ?: 0,
    ];
  }
  $field->setSetting('handler_settings', $settings);
  $field->save();
  $changed[] = 'field.field.node.site_page.field_page_components targets';
}

/**
 * Applies form and view displays.
 */
function site_platform_pb_apply_displays(array &$changed): void {
  foreach (site_platform_pb_paragraph_field_map() as $bundle => $fields) {
    site_platform_pb_apply_form_display('paragraph', $bundle, $fields);
    site_platform_pb_apply_view_display('paragraph', $bundle, $fields);
    $changed[] = 'display.paragraph.' . $bundle;
  }

  site_platform_pb_apply_landing_page_display($changed);
}

/**
 * Applies Landing Page form display with the full component picker.
 */
function site_platform_pb_apply_landing_page_display(array &$changed): void {
  $id = 'node.site_page.default';
  $display = EntityFormDisplay::load($id);
  if (!$display) {
    return;
  }
  $display->setComponent('field_page_components', [
    'type' => 'paragraphs',
    'weight' => 40,
    'region' => 'content',
    'settings' => [
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
    'third_party_settings' => [],
  ]);
  $display->save();
  $changed[] = 'display.node.site_page full component picker';
}

/**
 * Applies paragraph form display.
 */
function site_platform_pb_apply_form_display(string $entity_type, string $bundle, array $fields): void {
  $id = $entity_type . '.' . $bundle . '.default';
  $display = EntityFormDisplay::load($id) ?: EntityFormDisplay::create([
    'targetEntityType' => $entity_type,
    'bundle' => $bundle,
    'mode' => 'default',
    'status' => TRUE,
  ]);

  $weight = 0;
  foreach ($fields as [$field_name, $type]) {
    $display->setComponent($field_name, [
      'type' => site_platform_pb_widget($type, $field_name),
      'weight' => $weight,
      'region' => 'content',
      'settings' => site_platform_pb_widget_settings($type, $field_name),
      'third_party_settings' => [],
    ]);
    $weight++;
  }
  $display->save();
}

/**
 * Applies paragraph view display.
 */
function site_platform_pb_apply_view_display(string $entity_type, string $bundle, array $fields): void {
  $id = $entity_type . '.' . $bundle . '.default';
  $display = EntityViewDisplay::load($id) ?: EntityViewDisplay::create([
    'targetEntityType' => $entity_type,
    'bundle' => $bundle,
    'mode' => 'default',
    'status' => TRUE,
  ]);

  $weight = 0;
  foreach ($fields as [$field_name, $type]) {
    $display->setComponent($field_name, [
      'type' => site_platform_pb_formatter($type, $field_name),
      'label' => 'above',
      'weight' => $weight,
      'region' => 'content',
      'settings' => site_platform_pb_formatter_settings($type, $field_name),
      'third_party_settings' => [],
    ]);
    $weight++;
  }
  $display->save();
}

/**
 * Widget type for a field.
 */
function site_platform_pb_widget(string $type, string $field_name): string {
  if ($type === 'entity_reference_revisions') {
    return 'paragraphs';
  }
  if ($field_name === 'field_component_media') {
    return 'media_library_widget';
  }
  if ($field_name === 'field_component_form') {
    return 'options_select';
  }
  return match ($type) {
    'string' => 'string_textfield',
    'string_long' => 'string_textarea',
    'email' => 'email_default',
    'entity_reference' => 'entity_reference_autocomplete',
    'list_string' => 'options_select',
    default => 'string_textfield',
  };
}

/**
 * Widget settings.
 */
function site_platform_pb_widget_settings(string $type, string $field_name): array {
  if ($type === 'entity_reference_revisions') {
    return [
      'title' => 'Item',
      'title_plural' => 'Items',
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
    ];
  }
  if ($field_name === 'field_component_media') {
    return ['media_types' => ['image']];
  }
  return match ($type) {
    'string' => ['size' => 60, 'placeholder' => ''],
    'string_long' => ['rows' => 4, 'placeholder' => ''],
    'entity_reference' => ['match_operator' => 'CONTAINS', 'match_limit' => 10, 'size' => 60, 'placeholder' => ''],
    'email' => ['size' => 60, 'placeholder' => ''],
    default => [],
  };
}

/**
 * View formatter type.
 */
function site_platform_pb_formatter(string $type, string $field_name): string {
  if ($type === 'entity_reference_revisions') {
    return 'entity_reference_revisions_entity_view';
  }
  if ($type === 'entity_reference') {
    return $field_name === 'field_component_form' ? 'entity_reference_label' : 'entity_reference_entity_view';
  }
  if ($type === 'email') {
    return 'basic_string';
  }
  if ($type === 'list_string') {
    return 'list_default';
  }
  return 'basic_string';
}

/**
 * View formatter settings.
 */
function site_platform_pb_formatter_settings(string $type, string $field_name): array {
  if ($type === 'entity_reference_revisions') {
    return ['view_mode' => 'default', 'link' => FALSE];
  }
  if ($type === 'entity_reference') {
    return ['view_mode' => 'default', 'link' => FALSE];
  }
  return [];
}
