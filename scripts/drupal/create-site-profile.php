<?php

declare(strict_types=1);

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;

/**
 * Creates the Site Profile content type and fields.
 */

$bundle = 'site_profile';

if (!NodeType::load($bundle)) {
  $type = NodeType::create([
    'type' => $bundle,
    'name' => 'Site Profile',
    'description' => 'Stores global settings for one website, workspace, or domain.',
    'new_revision' => TRUE,
    'display_submitted' => FALSE,
  ]);
  $type->save();

  print "Created content type: {$bundle}\n";
}
else {
  print "Content type already exists: {$bundle}\n";
}

/**
 * Creates a field storage and field instance if missing.
 */
$create_field = static function (
  string $field_name,
  string $label,
  string $type,
  array $storage_settings = [],
  array $field_settings = [],
  bool $required = FALSE,
): void {
  $entity_type = 'node';
  $bundle = 'site_profile';

  if (!FieldStorageConfig::loadByName($entity_type, $field_name)) {
    FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => $entity_type,
      'type' => $type,
      'settings' => $storage_settings,
      'cardinality' => 1,
    ])->save();

    print "Created field storage: {$field_name}\n";
  }

  if (!FieldConfig::loadByName($entity_type, $bundle, $field_name)) {
    FieldConfig::create([
      'field_name' => $field_name,
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'label' => $label,
      'required' => $required,
      'settings' => $field_settings,
    ])->save();

    print "Created field: {$field_name}\n";
  }
};

$link_settings = [
  'link_type' => 17,
  'title' => 0,
];

$create_field('field_site_key', 'Site key', 'string', [
  'max_length' => 64,
], [], TRUE);

$create_field('field_site_short_name', 'Short name', 'string', [
  'max_length' => 255,
]);

$create_field('field_primary_domain', 'Primary domain', 'link', [], $link_settings);
$create_field('field_ui_domain', 'UI domain', 'link', [], $link_settings);
$create_field('field_admin_domain', 'Admin domain', 'link', [], $link_settings);
$create_field('field_api_domain', 'API domain', 'link', [], $link_settings);

$media_storage_settings = [
  'target_type' => 'media',
];

$media_field_settings = [
  'handler' => 'default:media',
  'handler_settings' => [
    'target_bundles' => [
      'image' => 'image',
    ],
  ],
];

$create_field('field_logo', 'Logo', 'entity_reference', $media_storage_settings, $media_field_settings);
$create_field('field_favicon', 'Favicon', 'entity_reference', $media_storage_settings, $media_field_settings);
$create_field('field_default_social_image', 'Default social image', 'entity_reference', $media_storage_settings, $media_field_settings);

$create_field('field_contact_email', 'Contact email', 'email');

$create_field('field_contact_phone', 'Contact phone', 'string', [
  'max_length' => 64,
]);

$create_field('field_contact_address', 'Contact address', 'string_long');

$create_field('field_footer_copyright', 'Footer copyright', 'string', [
  'max_length' => 255,
]);

$create_field('field_default_meta_title', 'Default meta title', 'string', [
  'max_length' => 255,
]);

$create_field('field_default_meta_description', 'Default meta description', 'string_long');

$create_field('field_theme_color', 'Theme color', 'string', [
  'max_length' => 32,
]);

$create_field('field_is_default', 'Is default', 'boolean');
$create_field('field_is_active', 'Is active', 'boolean');

/**
 * Configure default form display.
 */
$form_display = EntityFormDisplay::load("node.{$bundle}.default")
  ?: EntityFormDisplay::create([
    'targetEntityType' => 'node',
    'bundle' => $bundle,
    'mode' => 'default',
    'status' => TRUE,
  ]);

$form_display
  ->setComponent('title', [
    'type' => 'string_textfield',
    'weight' => -21,
  ])
  ->setComponent('field_site_key', [
    'type' => 'string_textfield',
    'weight' => -20,
  ])
  ->setComponent('field_site_short_name', [
    'type' => 'string_textfield',
    'weight' => -19,
  ])
  ->setComponent('field_primary_domain', [
    'type' => 'link_default',
    'weight' => -18,
  ])
  ->setComponent('field_ui_domain', [
    'type' => 'link_default',
    'weight' => -17,
  ])
  ->setComponent('field_admin_domain', [
    'type' => 'link_default',
    'weight' => -16,
  ])
  ->setComponent('field_api_domain', [
    'type' => 'link_default',
    'weight' => -15,
  ])
  ->setComponent('field_logo', [
    'type' => 'media_library_widget',
    'weight' => -14,
  ])
  ->setComponent('field_favicon', [
    'type' => 'media_library_widget',
    'weight' => -13,
  ])
  ->setComponent('field_default_social_image', [
    'type' => 'media_library_widget',
    'weight' => -12,
  ])
  ->setComponent('field_contact_email', [
    'type' => 'email_default',
    'weight' => -11,
  ])
  ->setComponent('field_contact_phone', [
    'type' => 'string_textfield',
    'weight' => -10,
  ])
  ->setComponent('field_contact_address', [
    'type' => 'string_textarea',
    'weight' => -9,
  ])
  ->setComponent('field_footer_copyright', [
    'type' => 'string_textfield',
    'weight' => -8,
  ])
  ->setComponent('field_default_meta_title', [
    'type' => 'string_textfield',
    'weight' => -7,
  ])
  ->setComponent('field_default_meta_description', [
    'type' => 'string_textarea',
    'weight' => -6,
  ])
  ->setComponent('field_theme_color', [
    'type' => 'string_textfield',
    'weight' => -5,
  ])
  ->setComponent('field_is_default', [
    'type' => 'boolean_checkbox',
    'weight' => -4,
  ])
  ->setComponent('field_is_active', [
    'type' => 'boolean_checkbox',
    'weight' => -3,
  ])
  ->save();

print "Updated form display for {$bundle}\n";

/**
 * Configure default view display.
 */
$view_display = EntityViewDisplay::load("node.{$bundle}.default")
  ?: EntityViewDisplay::create([
    'targetEntityType' => 'node',
    'bundle' => $bundle,
    'mode' => 'default',
    'status' => TRUE,
  ]);

foreach ([
  'field_site_key',
  'field_site_short_name',
  'field_primary_domain',
  'field_ui_domain',
  'field_admin_domain',
  'field_api_domain',
  'field_logo',
  'field_favicon',
  'field_default_social_image',
  'field_contact_email',
  'field_contact_phone',
  'field_contact_address',
  'field_footer_copyright',
  'field_default_meta_title',
  'field_default_meta_description',
  'field_theme_color',
  'field_is_default',
  'field_is_active',
] as $field_name) {
  $view_display->setComponent($field_name, [
    'label' => 'above',
    'type' => 'string',
  ]);
}

$view_display->save();

print "Updated view display for {$bundle}\n";
print "Site Profile setup complete.\n";
