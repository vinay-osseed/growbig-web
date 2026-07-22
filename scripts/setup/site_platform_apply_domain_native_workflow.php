<?php

/**
 * Applies Drupal-native Site Platform workflow: Domain mapping, datasets, menus.
 *
 * Run with:
 *   ddev drush scr scripts/setup/site_platform_apply_domain_native_workflow.php
 */

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;
use Drupal\paragraphs\Entity\ParagraphsType;

$changed = [];

site_platform_native_ensure_dataset_model($changed);
site_platform_native_ensure_domain_field($changed);
site_platform_native_update_site_page_menu_ui($changed);
site_platform_native_try_create_domains_from_site_profiles($changed);
site_platform_native_hide_legacy_content_types($changed);

\Drupal::service('cache.discovery')->deleteAll();
\Drupal::service('router.builder')->rebuild();

print "Applied Drupal-native workflow for:\n";
foreach ($changed as $item) {
  print "- {$item}\n";
}

/**
 * Ensure flexible dataset content model.
 */
function site_platform_native_ensure_dataset_model(array &$changed): void {
  if (!NodeType::load('site_data_set')) {
    NodeType::create([
      'type' => 'site_data_set',
      'name' => 'Site Data Set',
      'description' => 'Flexible site-specific structured data such as plans, locations, coverage areas, pricing, benefits, or careers.',
      'help' => 'Use this for structured data that differs between site types. Example keys: plans, locations, coverage_areas, pricing, careers.',
      'new_revision' => TRUE,
      'display_submitted' => FALSE,
    ])->save();
    $changed[] = 'node.type.site_data_set';
  }
  if (!ParagraphsType::load('site_data_item')) {
    ParagraphsType::create([
      'id' => 'site_data_item',
      'label' => 'Site Data Item',
      'description' => 'One generic item inside a Site Data Set.',
    ])->save();
    $changed[] = 'paragraphs.type.site_data_item';
  }

  $node_fields = [
    ['field_site_profile', 'entity_reference', 'Site Profile'],
    ['field_dataset_key', 'string', 'Dataset key'],
    ['field_dataset_label', 'string', 'Dataset label'],
    ['field_dataset_type', 'list_string', 'Dataset type'],
    ['field_dataset_items', 'entity_reference_revisions', 'Items'],
  ];
  foreach ($node_fields as [$field, $type, $label]) {
    site_platform_native_ensure_field('node', 'site_data_set', $field, $type, $label, $changed);
  }

  $item_fields = [
    ['field_component_key', 'string', 'Item key'],
    ['field_component_title', 'string', 'Title'],
    ['field_component_summary', 'string_long', 'Summary'],
    ['field_component_body', 'string_long', 'Body / notes'],
    ['field_component_media', 'entity_reference', 'Media'],
    ['field_stat_value', 'string', 'Value'],
    ['field_component_button_label', 'string', 'Link label'],
    ['field_component_button_path', 'string', 'Link path'],
    ['field_component_variant', 'string', 'Variant'],
  ];
  foreach ($item_fields as [$field, $type, $label]) {
    site_platform_native_ensure_field('paragraph', 'site_data_item', $field, $type, $label, $changed);
  }

  site_platform_native_apply_dataset_displays($changed);
}

/**
 * Ensure a field exists.
 */
function site_platform_native_ensure_field(string $entity_type, string $bundle, string $field_name, string $type, string $label, array &$changed): void {
  if (!FieldStorageConfig::loadByName($entity_type, $field_name)) {
    FieldStorageConfig::create([
      'entity_type' => $entity_type,
      'field_name' => $field_name,
      'type' => $type,
      'cardinality' => $field_name === 'field_dataset_items' ? -1 : 1,
      'settings' => site_platform_native_storage_settings($type, $field_name),
    ])->save();
    $changed[] = 'field.storage.' . $entity_type . '.' . $field_name;
  }
  if (!FieldConfig::loadByName($entity_type, $bundle, $field_name)) {
    FieldConfig::create([
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'field_name' => $field_name,
      'label' => $label,
      'required' => in_array($field_name, ['field_site_profile', 'field_dataset_key'], TRUE),
      'settings' => site_platform_native_field_settings($type, $field_name),
    ])->save();
    $changed[] = 'field.field.' . $entity_type . '.' . $bundle . '.' . $field_name;
  }
}

/**
 * Storage settings.
 */
function site_platform_native_storage_settings(string $type, string $field_name): array {
  if ($type === 'entity_reference') {
    return ['target_type' => $field_name === 'field_site_profile' ? 'node' : 'media'];
  }
  if ($type === 'entity_reference_revisions') {
    return ['target_type' => 'paragraph'];
  }
  if ($type === 'list_string') {
    return ['allowed_values' => [], 'allowed_values_function' => ''];
  }
  return [];
}

/**
 * Field settings.
 */
function site_platform_native_field_settings(string $type, string $field_name): array {
  if ($field_name === 'field_site_profile') {
    return [
      'handler' => 'default:node',
      'handler_settings' => ['target_bundles' => ['site_profile' => 'site_profile']],
    ];
  }
  if ($field_name === 'field_dataset_items') {
    return [
      'handler' => 'default:paragraph',
      'handler_settings' => ['target_bundles' => ['site_data_item' => 'site_data_item'], 'negate' => 0],
    ];
  }
  if ($field_name === 'field_dataset_type') {
    return [
      'allowed_values' => [
        'list' => 'List',
        'cards' => 'Cards',
        'table' => 'Table',
        'pricing' => 'Pricing / plans',
        'locations' => 'Locations / areas',
        'careers' => 'Careers',
        'custom' => 'Custom',
      ],
      'allowed_values_function' => '',
    ];
  }
  if ($type === 'entity_reference') {
    return [
      'handler' => 'default:media',
      'handler_settings' => ['target_bundles' => ['image' => 'image'], 'auto_create' => FALSE],
    ];
  }
  return [];
}

/**
 * Apply dataset form/view displays.
 */
function site_platform_native_apply_dataset_displays(array &$changed): void {
  $form = \Drupal\Core\Entity\Entity\EntityFormDisplay::load('node.site_data_set.default') ?: \Drupal\Core\Entity\Entity\EntityFormDisplay::create(['targetEntityType' => 'node', 'bundle' => 'site_data_set', 'mode' => 'default', 'status' => TRUE]);
  $components = [
    'title' => 'string_textfield',
    'field_site_profile' => 'options_select',
    'field_dataset_key' => 'string_textfield',
    'field_dataset_label' => 'string_textfield',
    'field_dataset_type' => 'options_select',
    'field_dataset_items' => 'paragraphs',
  ];
  $weight = 0;
  foreach ($components as $field => $widget) {
    $form->setComponent($field, ['type' => $widget, 'weight' => $weight++, 'region' => 'content', 'settings' => site_platform_native_widget_settings($widget), 'third_party_settings' => []]);
  }
  $form->save();

  $item_form = \Drupal\Core\Entity\Entity\EntityFormDisplay::load('paragraph.site_data_item.default') ?: \Drupal\Core\Entity\Entity\EntityFormDisplay::create(['targetEntityType' => 'paragraph', 'bundle' => 'site_data_item', 'mode' => 'default', 'status' => TRUE]);
  foreach (['field_component_key', 'field_component_title', 'field_component_summary', 'field_component_body', 'field_component_media', 'field_stat_value', 'field_component_button_label', 'field_component_button_path', 'field_component_variant'] as $i => $field) {
    $item_form->setComponent($field, ['type' => $field === 'field_component_media' ? 'media_library_widget' : 'string_textfield', 'weight' => $i, 'region' => 'content', 'settings' => $field === 'field_component_media' ? ['media_types' => ['image']] : [], 'third_party_settings' => []]);
  }
  $item_form->save();
  $changed[] = 'display.site_data_set';
}

/**
 * Widget settings.
 */
function site_platform_native_widget_settings(string $widget): array {
  if ($widget === 'paragraphs') {
    return ['title' => 'Item', 'title_plural' => 'Items', 'edit_mode' => 'open', 'closed_mode' => 'summary', 'autocollapse' => 'none', 'closed_mode_threshold' => 0, 'add_mode' => 'button', 'form_display_mode' => 'default', 'default_paragraph_type' => '', 'features' => ['add_above' => TRUE, 'collapse_edit_all' => TRUE, 'duplicate' => TRUE]];
  }
  return [];
}

/**
 * Add Domain entity reference field to Site Profile when Domain is installed.
 */
function site_platform_native_ensure_domain_field(array &$changed): void {
  if (!\Drupal::entityTypeManager()->hasDefinition('domain')) {
    return;
  }
  if (!FieldStorageConfig::loadByName('node', 'field_site_domain')) {
    FieldStorageConfig::create(['entity_type' => 'node', 'field_name' => 'field_site_domain', 'type' => 'entity_reference', 'cardinality' => 1, 'settings' => ['target_type' => 'domain']])->save();
    $changed[] = 'field.storage.node.field_site_domain';
  }
  if (!FieldConfig::loadByName('node', 'site_profile', 'field_site_domain')) {
    FieldConfig::create(['entity_type' => 'node', 'bundle' => 'site_profile', 'field_name' => 'field_site_domain', 'label' => 'Domain', 'description' => 'Domain Access site mapping for this Site Profile.', 'settings' => ['handler' => 'default:domain', 'handler_settings' => []]])->save();
    $changed[] = 'field.field.node.site_profile.field_site_domain';
  }
}

/**
 * Enable Drupal Menu UI settings on Landing Page.
 */
function site_platform_native_update_site_page_menu_ui(array &$changed): void {
  $type = NodeType::load('site_page');
  if ($type) {
    $type->setThirdPartySetting('menu_ui', 'available_menus', ['main', 'footer']);
    $type->setThirdPartySetting('menu_ui', 'parent', 'main:');
    $type->save();
    $changed[] = 'node.type.site_page menu_ui';
  }
}

/**
 * Try creating Domain entities from Site Profile frontend domains.
 */
function site_platform_native_try_create_domains_from_site_profiles(array &$changed): void {
  if (!\Drupal::entityTypeManager()->hasDefinition('domain')) {
    return;
  }
  $storage = \Drupal::entityTypeManager()->getStorage('domain');
  $node_storage = \Drupal::entityTypeManager()->getStorage('node');
  $ids = $node_storage->getQuery()->accessCheck(FALSE)->condition('type', 'site_profile')->execute();
  foreach ($node_storage->loadMultiple($ids) as $site) {
    if (!$site->hasField('field_frontend_domains') || $site->get('field_frontend_domains')->isEmpty()) {
      continue;
    }
    $raw = preg_split('/[\r\n,]+/', $site->get('field_frontend_domains')->getString());
    $host = trim((string) reset($raw));
    $host = preg_replace('#^https?://#', '', $host);
    $host = trim($host, '/ ');
    if ($host === '') {
      continue;
    }
    $domain_id = preg_replace('/[^a-z0-9_]+/', '_', strtolower($host));
    $domain_id = trim($domain_id, '_') ?: 'domain_' . $site->id();
    $domain = $storage->load($domain_id);
    if (!$domain && class_exists('Drupal\\domain\\Entity\\Domain')) {
      $domain = \Drupal\domain\Entity\Domain::create([
        'id' => $domain_id,
        'hostname' => $host,
        'name' => $site->label(),
        'scheme' => 'https',
        'status' => TRUE,
        'weight' => 0,
      ]);
      try {
        $domain->save();
        $changed[] = 'domain.' . $domain_id;
      }
      catch (\Throwable $e) {
        continue;
      }
    }
    if ($domain && $site->hasField('field_site_domain')) {
      $site->set('field_site_domain', $domain->id());
      $site->save();
      $changed[] = 'site_profile.domain.' . $site->id();
    }
  }
}

/**
 * Hide old compatibility bundles from normal node add links where possible.
 */
function site_platform_native_hide_legacy_content_types(array &$changed): void {
  foreach (['site_menu', 'site_menu_item', 'site_form', 'site_form_field', 'site_form_submission'] as $bundle) {
    $type = NodeType::load($bundle);
    if ($type) {
      $type->set('help', 'Compatibility-only record. Editors should use Drupal Menu UI or Webform instead.');
      $type->save();
      $changed[] = 'legacy-help.' . $bundle;
    }
  }
}
