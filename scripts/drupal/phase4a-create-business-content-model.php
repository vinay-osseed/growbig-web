<?php

declare(strict_types=1);

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;

/**
 * Phase 4A: Creates reusable business content types.
 *
 * This script is idempotent and safe to re-run.
 */

$node_entity_type = 'node';

$create_node_type = static function (
  string $type,
  string $label,
  string $description,
  string $title_label
): void {
  if (!NodeType::load($type)) {
    NodeType::create([
      'type' => $type,
      'name' => $label,
      'description' => $description,
      'title_label' => $title_label,
    ])->save();

    print "Created content type: {$type}\n";
  }
  else {
    print "Content type already exists: {$type}\n";
  }
};

$create_field = static function (
  string $entity_type,
  string $bundle,
  string $field_name,
  string $label,
  string $type,
  array $storage_settings = [],
  array $field_settings = [],
  int $cardinality = 1,
  bool $required = FALSE
): void {
  if (!FieldStorageConfig::loadByName($entity_type, $field_name)) {
    FieldStorageConfig::create([
      'field_name' => $field_name,
      'entity_type' => $entity_type,
      'type' => $type,
      'settings' => $storage_settings,
      'cardinality' => $cardinality,
    ])->save();

    print "Created field storage: {$entity_type}.{$field_name}\n";
  }
  else {
    print "Field storage already exists: {$entity_type}.{$field_name}\n";
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

    print "Created field: {$entity_type}.{$bundle}.{$field_name}\n";
  }
  else {
    print "Field already exists: {$entity_type}.{$bundle}.{$field_name}\n";
  }
};

$set_form_display = static function (string $bundle, array $components): void {
  $display = EntityFormDisplay::load("node.{$bundle}.default")
    ?: EntityFormDisplay::create([
      'targetEntityType' => 'node',
      'bundle' => $bundle,
      'mode' => 'default',
      'status' => TRUE,
    ]);

  $weight = 0;

  foreach ($components as $field_name => $widget) {
    $display->setComponent($field_name, [
      'type' => $widget,
      'weight' => $weight,
    ]);

    $weight++;
  }

  $display->save();

  print "Updated form display: node.{$bundle}.default\n";
};

$set_view_display = static function (string $bundle, array $fields): void {
  $display = EntityViewDisplay::load("node.{$bundle}.default")
    ?: EntityViewDisplay::create([
      'targetEntityType' => 'node',
      'bundle' => $bundle,
      'mode' => 'default',
      'status' => TRUE,
    ]);

  $weight = 0;

  foreach ($fields as $field_name) {
    $display->setComponent($field_name, [
      'label' => 'above',
      'type' => 'string',
      'weight' => $weight,
    ]);

    $weight++;
  }

  $display->save();

  print "Updated view display: node.{$bundle}.default\n";
};

$link_settings = [
  'link_type' => 17,
  'title' => 0,
];

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

$site_storage_settings = [
  'target_type' => 'node',
];

$site_field_settings = [
  'handler' => 'default:node',
  'handler_settings' => [
    'target_bundles' => [
      'site_profile' => 'site_profile',
    ],
  ],
];

$create_node_type(
  'service',
  'Service',
  'Reusable service content for frontend service sections and service pages.',
  'Service Name'
);

$create_node_type(
  'partner',
  'Partner',
  'Reusable partner content for frontend partner sections.',
  'Partner Name'
);

$create_node_type(
  'team_member',
  'Team Member',
  'Reusable leadership and team member content.',
  'Member Name'
);

foreach (['service', 'partner', 'team_member'] as $bundle) {
  $create_field($node_entity_type, $bundle, 'field_sites', 'Sites', 'entity_reference', $site_storage_settings, $site_field_settings, -1);
  $create_field($node_entity_type, $bundle, 'field_summary', 'Summary', 'string_long');
  $create_field($node_entity_type, $bundle, 'field_display_order', 'Display Order', 'integer');
  $create_field($node_entity_type, $bundle, 'field_is_featured', 'Is Featured', 'boolean');
  $create_field($node_entity_type, $bundle, 'field_is_active', 'Is Active', 'boolean');
}

$create_field($node_entity_type, 'service', 'field_service_key', 'Service Key', 'string', ['max_length' => 128], [], 1, TRUE);
$create_field($node_entity_type, 'service', 'field_icon', 'Icon', 'string', ['max_length' => 128]);
$create_field($node_entity_type, 'service', 'field_accent_color', 'Accent Color', 'string', ['max_length' => 32]);
$create_field($node_entity_type, 'service', 'field_image', 'Image', 'entity_reference', $media_storage_settings, $media_field_settings);
$create_field($node_entity_type, 'service', 'field_link_url', 'Link URL', 'link', [], $link_settings);
$create_field($node_entity_type, 'service', 'field_meta_title', 'Meta Title', 'string', ['max_length' => 255]);
$create_field($node_entity_type, 'service', 'field_meta_description', 'Meta Description', 'string_long');

$create_field($node_entity_type, 'partner', 'field_partner_key', 'Partner Key', 'string', ['max_length' => 128], [], 1, TRUE);
$create_field($node_entity_type, 'partner', 'field_logo', 'Logo', 'entity_reference', $media_storage_settings, $media_field_settings);
$create_field($node_entity_type, 'partner', 'field_website', 'Website', 'link', [], $link_settings);

$create_field($node_entity_type, 'team_member', 'field_member_key', 'Member Key', 'string', ['max_length' => 128], [], 1, TRUE);
$create_field($node_entity_type, 'team_member', 'field_role', 'Role', 'string', ['max_length' => 255]);
$create_field($node_entity_type, 'team_member', 'field_bio', 'Bio', 'string_long');
$create_field($node_entity_type, 'team_member', 'field_photo', 'Photo', 'entity_reference', $media_storage_settings, $media_field_settings);
$create_field($node_entity_type, 'team_member', 'field_email', 'Email', 'email');
$create_field($node_entity_type, 'team_member', 'field_linkedin_url', 'LinkedIn URL', 'link', [], $link_settings);

$set_form_display('service', [
  'field_service_key' => 'string_textfield',
  'field_sites' => 'entity_reference_autocomplete',
  'field_summary' => 'string_textarea',
  'field_icon' => 'string_textfield',
  'field_accent_color' => 'string_textfield',
  'field_image' => 'entity_reference_autocomplete',
  'field_link_url' => 'link_default',
  'field_display_order' => 'number',
  'field_is_featured' => 'boolean_checkbox',
  'field_is_active' => 'boolean_checkbox',
  'field_meta_title' => 'string_textfield',
  'field_meta_description' => 'string_textarea',
]);

$set_form_display('partner', [
  'field_partner_key' => 'string_textfield',
  'field_sites' => 'entity_reference_autocomplete',
  'field_summary' => 'string_textarea',
  'field_logo' => 'entity_reference_autocomplete',
  'field_website' => 'link_default',
  'field_display_order' => 'number',
  'field_is_featured' => 'boolean_checkbox',
  'field_is_active' => 'boolean_checkbox',
]);

$set_form_display('team_member', [
  'field_member_key' => 'string_textfield',
  'field_sites' => 'entity_reference_autocomplete',
  'field_role' => 'string_textfield',
  'field_summary' => 'string_textarea',
  'field_bio' => 'string_textarea',
  'field_photo' => 'entity_reference_autocomplete',
  'field_email' => 'email_default',
  'field_linkedin_url' => 'link_default',
  'field_display_order' => 'number',
  'field_is_featured' => 'boolean_checkbox',
  'field_is_active' => 'boolean_checkbox',
]);

$set_view_display('service', [
  'field_service_key',
  'field_sites',
  'field_summary',
  'field_icon',
  'field_accent_color',
  'field_image',
  'field_link_url',
  'field_display_order',
  'field_is_featured',
  'field_is_active',
  'field_meta_title',
  'field_meta_description',
]);

$set_view_display('partner', [
  'field_partner_key',
  'field_sites',
  'field_summary',
  'field_logo',
  'field_website',
  'field_display_order',
  'field_is_featured',
  'field_is_active',
]);

$set_view_display('team_member', [
  'field_member_key',
  'field_sites',
  'field_role',
  'field_summary',
  'field_bio',
  'field_photo',
  'field_email',
  'field_linkedin_url',
  'field_display_order',
  'field_is_featured',
  'field_is_active',
]);

print "Phase 4A business content model setup complete.\n";
