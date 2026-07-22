<?php

/**
 * Cleans the editor workflow after moving Site Platform to Drupal-native tools.
 *
 * Run with:
 *   ddev drush scr scripts/setup/site_platform_cleanup_editor_workflow.php
 */

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;
use Drupal\user\Entity\Role;

$changed = [];
$legacy_bundles = ['site_menu', 'site_menu_item', 'site_form', 'site_form_field', 'site_form_submission'];
$node_storage = \Drupal::entityTypeManager()->getStorage('node');
$backup = [
  'created_at' => gmdate('c'),
  'legacy_nodes_deleted' => [],
  'legacy_content_types_deleted' => [],
];

foreach ($legacy_bundles as $bundle) {
  $ids = $node_storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', $bundle)
    ->execute();
  $nodes = $ids ? $node_storage->loadMultiple($ids) : [];
  foreach ($nodes as $node) {
    $backup['legacy_nodes_deleted'][] = [
      'id' => (int) $node->id(),
      'bundle' => $bundle,
      'title' => (string) $node->label(),
    ];
  }
  if ($nodes) {
    $node_storage->delete($nodes);
    $changed[] = 'deleted legacy nodes: ' . $bundle . ' (' . count($nodes) . ')';
  }

  $type = NodeType::load($bundle);
  if ($type) {
    $type->delete();
    $backup['legacy_content_types_deleted'][] = $bundle;
    $changed[] = 'deleted legacy content type: ' . $bundle;
  }
}

$backup_dir = getcwd() . '/var';
if (!is_dir($backup_dir)) {
  mkdir($backup_dir, 0775, TRUE);
}
$backup_path = $backup_dir . '/site-platform-legacy-cleanup-' . date('Ymd-His') . '.json';
file_put_contents($backup_path, json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);
$changed[] = 'legacy cleanup backup: ' . $backup_path;

// Fix dataset type dropdown by setting allowed values on field storage.
$dataset_values = [
  'list' => 'List',
  'cards' => 'Cards',
  'table' => 'Table',
  'pricing' => 'Pricing / plans',
  'locations' => 'Locations / areas',
  'careers' => 'Careers',
  'custom' => 'Custom',
];
$storage = FieldStorageConfig::loadByName('node', 'field_dataset_type');
if ($storage) {
  $storage->setSetting('allowed_values', $dataset_values);
  $storage->setSetting('allowed_values_function', '');
  $storage->save();
  $changed[] = 'fixed field.storage.node.field_dataset_type allowed values';
}
$field = FieldConfig::loadByName('node', 'site_data_set', 'field_dataset_type');
if ($field) {
  $field->set('required', TRUE);
  $field->save();
  $changed[] = 'required field.field.node.site_data_set.field_dataset_type';
}

// Keep Domain visible on Site Profile editor form when the field exists.
$form = \Drupal\Core\Entity\Entity\EntityFormDisplay::load('node.site_profile.default');
if ($form && FieldConfig::loadByName('node', 'site_profile', 'field_site_domain')) {
  $form->setComponent('field_site_domain', [
    'type' => 'options_select',
    'weight' => -5,
    'region' => 'content',
    'settings' => [],
    'third_party_settings' => [],
  ]);
  $form->save();
  $changed[] = 'show field_site_domain on Site Profile editor form';
}

// Create/refresh active Tour config for the Start Here route.
if (\Drupal::moduleHandler()->moduleExists('tour')) {
  $config = \Drupal::configFactory()->getEditable('tour.tour.site-platform-start');
  $config
    ->set('id', 'site-platform-start')
    ->set('label', 'Site Platform start tour')
    ->set('module', 'site_platform_native')
    ->set('langcode', 'en')
    ->set('routes', [['route_name' => 'site_platform_native.start']])
    ->set('tips', [
      'start' => [
        'id' => 'start',
        'plugin' => 'text',
        'label' => 'Start here',
        'body' => 'Follow the workflow from Sites and Domains to Media, Webforms, Pages, Menus, Data Sets, and API handoff.',
        'weight' => 1,
        'location' => 'bottom',
      ],
      'apis' => [
        'id' => 'apis',
        'plugin' => 'text',
        'label' => 'Use fewer frontend API calls',
        'body' => 'Use /api/v2/bootstrap, /api/v2/pages, and detail endpoints only when needed.',
        'weight' => 2,
        'location' => 'bottom',
      ],
    ])
    ->save();
  $changed[] = 'active tour.tour.site-platform-start';
}

// Grant native admin guide access to roles that already look like editor/admin roles.
foreach (Role::loadMultiple() as $role) {
  $id = $role->id();
  if ($id === 'anonymous') {
    continue;
  }
  if (
    $role->hasPermission('access administration pages')
    || $role->hasPermission('administer site platform')
    || $role->hasPermission('create site_page content')
    || in_array($id, ['administrator', 'site_admin', 'editor', 'content_editor'], TRUE)
  ) {
    $role->grantPermission('access site platform native admin');
    $role->save();
    $changed[] = 'granted native admin guide access to role: ' . $id;
  }
}

\Drupal::service('cache.discovery')->deleteAll();
\Drupal::service('router.builder')->rebuild();

print "Applied editor workflow cleanup for:\n";
foreach ($changed as $item) {
  print "- {$item}\n";
}
