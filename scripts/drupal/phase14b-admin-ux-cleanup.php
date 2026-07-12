<?php

declare(strict_types=1);

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;

$entity_display_repository = \Drupal::service('entity_display.repository');

function update_form_component(string $entity_type, string $bundle, string $field_name, array $component): void {
  $display = \Drupal::service('entity_display.repository')->getFormDisplay($entity_type, $bundle, 'default');
  $display->setComponent($field_name, $component);
  $display->save();
}

function update_field_description(string $entity_type, string $bundle, string $field_name, string $description, ?string $label = NULL): void {
  $field = FieldConfig::loadByName($entity_type, $bundle, $field_name);

  if (!$field) {
    return;
  }

  if ($label !== NULL) {
    $field->setLabel($label);
  }

  $field->setDescription($description);
  $field->save();
}

function update_allowed_values(string $entity_type, string $field_name, array $allowed_values): void {
  $storage = FieldStorageConfig::loadByName($entity_type, $field_name);

  if (!$storage) {
    return;
  }

  $settings = $storage->getSettings();
  $settings['allowed_values'] = $allowed_values;
  $storage->set('settings', $settings);
  $storage->save();
}

$node_bundles_with_sites = [
  'site_page',
  'service',
  'partner',
  'team_member',
  'job',
];

foreach ($node_bundles_with_sites as $bundle) {
  if (FieldConfig::loadByName('node', $bundle, 'field_sites')) {
    update_form_component('node', $bundle, 'field_sites', [
      'type' => 'options_select',
      'weight' => 5,
      'region' => 'content',
      'settings' => [],
      'third_party_settings' => [],
    ]);
    update_field_description('node', $bundle, 'field_sites', 'Choose the site where this content should appear. Shared content can be assigned to more than one site.', 'Sites');
  }
}

$media_fields = [
  ['node', 'service', 'field_image'],
  ['node', 'partner', 'field_logo'],
  ['node', 'site_page', 'field_og_image'],
  ['node', 'site_profile', 'field_logo'],
  ['node', 'site_profile', 'field_favicon'],
  ['node', 'site_profile', 'field_default_social_image'],
  ['node', 'team_member', 'field_photo'],
  ['paragraph', 'card_item', 'field_image'],
  ['paragraph', 'hero_section', 'field_image'],
  ['paragraph', 'image_text_section', 'field_image'],
];

foreach ($media_fields as [$entity_type, $bundle, $field_name]) {
  if (!FieldConfig::loadByName($entity_type, $bundle, $field_name)) {
    continue;
  }

  update_form_component($entity_type, $bundle, $field_name, [
    'type' => 'media_library_widget',
    'weight' => 50,
    'region' => 'content',
    'settings' => [
      'media_types' => [],
    ],
    'third_party_settings' => [],
  ]);
  update_field_description($entity_type, $bundle, $field_name, 'Choose an existing media item from the media library or add a new one.');
}

update_allowed_values('node', 'field_page_type', [
  'home' => 'Home Page',
  'standard' => 'Standard Page',
  'landing' => 'Landing Page',
]);

update_allowed_values('node', 'field_robots', [
  'index_follow' => 'Index and follow',
  'noindex_follow' => 'Do not index, follow links',
  'noindex_nofollow' => 'Do not index, do not follow links',
]);

update_allowed_values('paragraph', 'field_layout_variant', [
  'default' => 'Default',
  'centered' => 'Centered',
  'split' => 'Split layout',
  'compact' => 'Compact',
  'featured' => 'Featured',
  'dark_grid' => 'Dark grid',
]);

update_allowed_values('paragraph', 'field_image_position', [
  'left' => 'Image left',
  'right' => 'Image right',
  'top' => 'Image top',
  'background' => 'Background image',
]);

update_field_description('node', 'site_page', 'field_page_type', 'Choose the purpose of this page. Most pages should use Standard Page.');
update_field_description('node', 'site_page', 'field_robots', 'SEO search engine behavior. Use Index and follow unless this page should not appear in search results.');
update_field_description('paragraph', 'hero_section', 'field_layout_variant', 'Choose how this section should be displayed on the frontend.');
update_field_description('paragraph', 'card_grid_section', 'field_layout_variant', 'Choose the card layout style.');
update_field_description('paragraph', 'content_list_section', 'field_layout_variant', 'Choose the content list layout style.');
update_field_description('paragraph', 'cta_section', 'field_layout_variant', 'Choose the call-to-action layout style.');
update_field_description('paragraph', 'image_text_section', 'field_layout_variant', 'Choose how text and image should be arranged.');
update_field_description('paragraph', 'image_text_section', 'field_image_position', 'Choose where the image should appear in this section.');

echo "Admin UX field displays and list options updated.\n";
