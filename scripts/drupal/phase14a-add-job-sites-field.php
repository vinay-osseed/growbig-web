<?php

declare(strict_types=1);

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

$field_name = 'field_sites';
$entity_type = 'node';
$bundle = 'job';

if (!FieldStorageConfig::loadByName($entity_type, $field_name)) {
  FieldStorageConfig::create([
    'field_name' => $field_name,
    'entity_type' => $entity_type,
    'type' => 'entity_reference',
    'cardinality' => -1,
    'settings' => [
      'target_type' => 'node',
    ],
  ])->save();
}

if (!FieldConfig::loadByName($entity_type, $bundle, $field_name)) {
  FieldConfig::create([
    'field_name' => $field_name,
    'entity_type' => $entity_type,
    'bundle' => $bundle,
    'label' => 'Sites',
    'required' => TRUE,
    'settings' => [
      'handler' => 'default:node',
      'handler_settings' => [
        'target_bundles' => [
          'site_profile' => 'site_profile',
        ],
      ],
    ],
  ])->save();
}

$entity_display_repository = \Drupal::service('entity_display.repository');

$form_display = $entity_display_repository->getFormDisplay($entity_type, $bundle, 'default');
$form_display->setComponent($field_name, [
  'type' => 'entity_reference_autocomplete',
  'weight' => 120,
]);
$form_display->save();

$view_display = $entity_display_repository->getViewDisplay($entity_type, $bundle, 'default');
$view_display->setComponent($field_name, [
  'type' => 'entity_reference_label',
  'weight' => 120,
]);
$view_display->save();

$storage = \Drupal::entityTypeManager()->getStorage('node');

$site_ids = $storage->getQuery()
  ->accessCheck(FALSE)
  ->condition('type', 'site_profile')
  ->condition('field_site_key', 'growbig')
  ->range(0, 1)
  ->execute();

if (!$site_ids) {
  throw new RuntimeException('Default GrowBig site profile not found.');
}

$site_id = (int) reset($site_ids);

$job_ids = $storage->getQuery()
  ->accessCheck(FALSE)
  ->condition('type', 'job')
  ->execute();

foreach ($storage->loadMultiple($job_ids) as $job) {
  if (!$job->hasField($field_name)) {
    continue;
  }

  if ($job->get($field_name)->isEmpty()) {
    $job->set($field_name, [
      ['target_id' => $site_id],
    ]);
    $job->save();
  }
}

echo "Job Sites field is ready and existing jobs are assigned to GrowBig.\n";
