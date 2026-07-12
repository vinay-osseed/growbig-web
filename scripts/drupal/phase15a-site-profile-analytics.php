<?php

declare(strict_types=1);

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\NodeInterface;

$field_name = 'field_ga_measurement_id';

if (!FieldStorageConfig::loadByName('node', $field_name)) {
  FieldStorageConfig::create([
    'field_name' => $field_name,
    'entity_type' => 'node',
    'type' => 'string',
    'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
    'settings' => [
      'max_length' => 64,
      'is_ascii' => TRUE,
      'case_sensitive' => FALSE,
    ],
  ])->save();
}

if (!FieldConfig::loadByName('node', 'site_profile', $field_name)) {
  FieldConfig::create([
    'field_name' => $field_name,
    'entity_type' => 'node',
    'bundle' => 'site_profile',
    'label' => 'Google Analytics Measurement ID',
    'description' => 'Per-site GA4 Measurement ID exposed through /api/v1/analytics/config. Example: G-XXXXXXXXXX.',
    'required' => FALSE,
  ])->save();
}

$display = \Drupal::service('entity_display.repository')->getFormDisplay('node', 'site_profile', 'default');
$display->setComponent($field_name, [
  'type' => 'string_textfield',
  'weight' => 260,
  'region' => 'content',
  'settings' => [
    'size' => 32,
    'placeholder' => 'G-XXXXXXXXXX',
  ],
  'third_party_settings' => [],
]);
$display->save();

$view_display = \Drupal::service('entity_display.repository')->getViewDisplay('node', 'site_profile', 'default');
$view_display->removeComponent($field_name);
$view_display->save();

$config = \Drupal::config('site_platform_api.analytics');
$global_measurement_id = trim((string) $config->get('measurement_id'));

if ($global_measurement_id !== '') {
  $storage = \Drupal::entityTypeManager()->getStorage('node');

  $ids = $storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', 'site_profile')
    ->condition('field_is_default', 1)
    ->range(0, 1)
    ->execute();

  if ($ids) {
    $profile = $storage->load(reset($ids));

    if ($profile instanceof NodeInterface && $profile->hasField($field_name) && $profile->get($field_name)->isEmpty()) {
      $profile->set($field_name, $global_measurement_id);
      $profile->save();
    }
  }
}

echo "Site Profile analytics field verified.\n";
