<?php

declare(strict_types=1);

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

function set_allowed_values(string $entity_type, string $field_name, array $values): void {
  $storage = FieldStorageConfig::loadByName($entity_type, $field_name);

  if (!$storage) {
    return;
  }

  $settings = $storage->getSettings();
  $settings['allowed_values'] = $values;
  $storage->set('settings', $settings);
  $storage->save();
}

function set_field_default(string $entity_type, string $bundle, string $field_name, string $default, bool $required = TRUE): void {
  $field = FieldConfig::loadByName($entity_type, $bundle, $field_name);

  if (!$field) {
    return;
  }

  $field->setRequired($required);
  $field->setDefaultValue($default);
  $field->save();
}

function hide_form_field(string $entity_type, string $bundle, string $field_name): void {
  $display = \Drupal::service('entity_display.repository')->getFormDisplay($entity_type, $bundle, 'default');

  if ($display->getComponent($field_name) !== NULL) {
    $display->removeComponent($field_name);
    $display->save();
  }
}

set_allowed_values('paragraph', 'field_content_source', [
  'services' => 'Services',
  'partners' => 'Partners',
  'team' => 'Team members',
  'jobs' => 'Jobs',
]);

set_allowed_values('paragraph', 'field_background_style', [
  'default' => 'Default',
  'light' => 'Light',
  'dark' => 'Dark',
  'dark_grid' => 'Dark grid',
  'gradient' => 'Gradient',
  'image' => 'Image background',
]);

set_allowed_values('paragraph', 'field_layout_variant', [
  'default' => 'Default',
  'centered' => 'Centered',
  'split' => 'Split layout',
  'compact' => 'Compact',
  'featured' => 'Featured',
  'dark_grid' => 'Dark grid',
]);

set_allowed_values('paragraph', 'field_image_position', [
  'left' => 'Image left',
  'right' => 'Image right',
  'top' => 'Image top',
  'background' => 'Background image',
]);

set_field_default('paragraph', 'content_list_section', 'field_content_source', 'services');
set_field_default('paragraph', 'hero_section', 'field_background_style', 'dark_grid');
set_field_default('paragraph', 'cta_section', 'field_background_style', 'default');
set_field_default('paragraph', 'hero_section', 'field_layout_variant', 'centered');
set_field_default('paragraph', 'card_grid_section', 'field_layout_variant', 'default');
set_field_default('paragraph', 'content_list_section', 'field_layout_variant', 'default');
set_field_default('paragraph', 'cta_section', 'field_layout_variant', 'default');
set_field_default('paragraph', 'image_text_section', 'field_layout_variant', 'split');
set_field_default('paragraph', 'image_text_section', 'field_image_position', 'right');

hide_form_field('node', 'service', 'field_accent_color');
hide_form_field('paragraph', 'card_item', 'field_accent_color');

echo "Editor dropdown defaults fixed and accent color hidden.\n";
