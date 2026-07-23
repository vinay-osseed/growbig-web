<?php

/**
 * Deletes local Site Platform demo/test data after writing a backup summary.
 *
 * Run with:
 *   ddev drush scr scripts/setup/site_platform_delete_demo_data.php --execute
 */

use Drupal\node\NodeInterface;

$execute = in_array('--execute', $_SERVER['argv'] ?? [], TRUE);
if (!$execute) {
  print "Dry run only. Re-run with --execute to delete demo/test data.\n";
}

$node_storage = \Drupal::entityTypeManager()->getStorage('node');
$webform_storage = \Drupal::entityTypeManager()->hasDefinition('webform') ? \Drupal::entityTypeManager()->getStorage('webform') : NULL;
$bundles = ['site_profile', 'site_page', 'site_content_block', 'site_media_asset', 'site_menu', 'site_menu_item', 'site_form', 'site_form_field', 'site_form_submission'];
$delete_ids = [];
$demo_site_ids = [];
$backup = [
  'created_at' => gmdate('c'),
  'execute' => $execute,
  'nodes' => [],
  'webforms' => [],
];

function site_platform_node_is_demo(NodeInterface $node): bool {
  if ($node->hasField('field_is_demo') && !$node->get('field_is_demo')->isEmpty() && (bool) $node->get('field_is_demo')->value) {
    return TRUE;
  }

  if ($node->hasField('field_demo_source') && trim((string) $node->get('field_demo_source')->getString()) !== '') {
    return TRUE;
  }

  if ($node->bundle() === 'site_profile' && $node->hasField('field_site_key')) {
    $key = strtolower(trim((string) $node->get('field_site_key')->getString()));
    if (in_array($key, ['yamltest', 'test', 'demo'], TRUE) || str_contains($key, 'test') || str_contains($key, 'demo')) {
      return TRUE;
    }
  }

  $title = strtolower((string) $node->label());
  if (str_contains($title, 'yaml test') || str_contains($title, 'yamltest')) {
    return TRUE;
  }

  return FALSE;
}

foreach ($bundles as $bundle) {
  $ids = $node_storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', $bundle)
    ->execute();

  foreach ($node_storage->loadMultiple($ids) as $node) {
    if (!$node instanceof NodeInterface) {
      continue;
    }
    if (site_platform_node_is_demo($node)) {
      $delete_ids[(int) $node->id()] = (int) $node->id();
      if ($bundle === 'site_profile') {
        $demo_site_ids[(int) $node->id()] = (int) $node->id();
      }
    }
  }
}

if ($demo_site_ids) {
  foreach ($bundles as $bundle) {
    if ($bundle === 'site_profile') {
      continue;
    }
    $ids = $node_storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', $bundle)
      ->execute();
    foreach ($node_storage->loadMultiple($ids) as $node) {
      if (!$node instanceof NodeInterface || !$node->hasField('field_site_profile') || $node->get('field_site_profile')->isEmpty()) {
        continue;
      }
      foreach ($node->get('field_site_profile')->getValue() as $value) {
        $target_id = isset($value['target_id']) ? (int) $value['target_id'] : 0;
        if ($target_id && isset($demo_site_ids[$target_id])) {
          $delete_ids[(int) $node->id()] = (int) $node->id();
        }
      }
    }
  }
}

foreach ($node_storage->loadMultiple($delete_ids) as $node) {
  if (!$node instanceof NodeInterface) {
    continue;
  }
  $backup['nodes'][] = [
    'id' => (int) $node->id(),
    'bundle' => $node->bundle(),
    'title' => (string) $node->label(),
  ];
}

$webforms_to_delete = [];
if ($webform_storage) {
  foreach ($webform_storage->loadMultiple() as $webform) {
    $id = method_exists($webform, 'id') ? strtolower((string) $webform->id()) : '';
    $label = method_exists($webform, 'label') ? strtolower((string) $webform->label()) : '';
    if (str_contains($id, 'yaml') || str_contains($id, 'test') || str_contains($label, 'yaml') || str_contains($label, 'test')) {
      $webforms_to_delete[] = $webform;
      $backup['webforms'][] = [
        'id' => method_exists($webform, 'id') ? (string) $webform->id() : '',
        'label' => method_exists($webform, 'label') ? (string) $webform->label() : '',
      ];
    }
  }
}

$backup_dir = getcwd() . '/var';
if (!is_dir($backup_dir)) {
  mkdir($backup_dir, 0775, TRUE);
}
$backup_path = $backup_dir . '/site-platform-demo-cleanup-' . date('Ymd-His') . '.json';
file_put_contents($backup_path, json_encode($backup, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL);

print "Demo/test cleanup backup summary: {$backup_path}\n";
print "Demo/test nodes matched: " . count($backup['nodes']) . "\n";
print "Demo/test webforms matched: " . count($backup['webforms']) . "\n";

if ($execute) {
  $nodes = $node_storage->loadMultiple($delete_ids);
  if ($nodes) {
    $node_storage->delete($nodes);
  }
  if ($webforms_to_delete) {
    $webform_storage->delete($webforms_to_delete);
  }
  print "Deleted demo/test data.\n";
}
