<?php

declare(strict_types=1);

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

$bundle = 'site_page';

function ensure_field_storage(string $field_name, string $type, array $settings = [], int $cardinality = 1): void {
  if (FieldStorageConfig::loadByName('node', $field_name)) {
    print "Field storage exists: {$field_name}\n";
    return;
  }

  FieldStorageConfig::create([
    'field_name' => $field_name,
    'entity_type' => 'node',
    'type' => $type,
    'settings' => $settings,
    'cardinality' => $cardinality,
  ])->save();

  print "Created field storage: {$field_name}\n";
}

function ensure_field_config(string $bundle, string $field_name, string $label, bool $required = FALSE): void {
  if (FieldConfig::loadByName('node', $bundle, $field_name)) {
    print "Field config exists: {$field_name}\n";
    return;
  }

  FieldConfig::create([
    'field_name' => $field_name,
    'entity_type' => 'node',
    'bundle' => $bundle,
    'label' => $label,
    'required' => $required,
  ])->save();

  print "Created field config: {$field_name}\n";
}

ensure_field_storage('field_show_in_header', 'boolean');
ensure_field_config($bundle, 'field_show_in_header', 'Show In Header');

ensure_field_storage('field_show_in_footer', 'boolean');
ensure_field_config($bundle, 'field_show_in_footer', 'Show In Footer');

ensure_field_storage('field_menu_title', 'string', ['max_length' => 255]);
ensure_field_config($bundle, 'field_menu_title', 'Menu Title');

ensure_field_storage('field_menu_weight', 'integer');
ensure_field_config($bundle, 'field_menu_weight', 'Menu Weight');

$defaults = [
  'home' => [
    'title' => 'Home',
    'weight' => 0,
  ],
  'about' => [
    'title' => 'About',
    'weight' => 10,
  ],
  'careers' => [
    'title' => 'Careers',
    'weight' => 20,
  ],
  'contact' => [
    'title' => 'Contact',
    'weight' => 30,
  ],
];

$storage = \Drupal::entityTypeManager()->getStorage('node');

foreach ($defaults as $key => $settings) {
  $ids = $storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', 'site_page')
    ->condition('field_page_key', $key)
    ->range(0, 1)
    ->execute();

  if (!$ids) {
    print "Page not found: {$key}\n";
    continue;
  }

  $page = $storage->load(reset($ids));

  if (!$page) {
    print "Could not load page: {$key}\n";
    continue;
  }

  $page->set('field_show_in_header', TRUE);
  $page->set('field_show_in_footer', TRUE);
  $page->set('field_menu_title', $settings['title']);
  $page->set('field_menu_weight', $settings['weight']);
  $page->save();

  print "Updated menu settings for page: {$key}\n";
}

print "Page menu fields setup complete.\n";
