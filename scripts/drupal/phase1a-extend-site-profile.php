<?php

declare(strict_types=1);

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\paragraphs\Entity\ParagraphsType;

/**
 * Phase 1A: Extends Site Profile for reusable multi-site settings.
 *
 * This script is idempotent and safe to re-run.
 */

$node_entity_type = 'node';
$site_profile_bundle = 'site_profile';
$paragraph_entity_type = 'paragraph';
$social_link_bundle = 'social_link';

/**
 * Creates field storage and field instance if missing.
 */
$create_field = static function (
  string $entity_type,
  string $bundle,
  string $field_name,
  string $label,
  string $type,
  array $storage_settings = [],
  array $field_settings = [],
  int $cardinality = 1,
  bool $required = FALSE,
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

/**
 * Creates paragraph type if missing.
 */
if (!ParagraphsType::load($social_link_bundle)) {
  ParagraphsType::create([
    'id' => $social_link_bundle,
    'label' => 'Social Link',
    'description' => 'Stores a social media profile link for a site profile.',
  ])->save();

  print "Created paragraph type: {$social_link_bundle}\n";
}
else {
  print "Paragraph type already exists: {$social_link_bundle}\n";
}

$link_settings = [
  'link_type' => 17,
  'title' => 0,
];

/**
 * Social Link paragraph fields.
 */
$create_field($paragraph_entity_type, $social_link_bundle, 'field_platform_name', 'Platform name', 'string', [
  'max_length' => 128,
], [], 1, TRUE);

$create_field($paragraph_entity_type, $social_link_bundle, 'field_profile_url', 'Profile URL', 'link', [], $link_settings, 1, TRUE);

$create_field($paragraph_entity_type, $social_link_bundle, 'field_icon', 'Icon', 'string', [
  'max_length' => 128,
]);

$create_field($paragraph_entity_type, $social_link_bundle, 'field_display_order', 'Display order', 'integer');

$create_field($paragraph_entity_type, $social_link_bundle, 'field_is_active', 'Is active', 'boolean');

/**
 * Site Profile extra global settings.
 */
$create_field($node_entity_type, $site_profile_bundle, 'field_tagline', 'Tagline', 'string', [
  'max_length' => 255,
]);

$create_field($node_entity_type, $site_profile_bundle, 'field_description', 'Description', 'string_long');

$create_field($node_entity_type, $site_profile_bundle, 'field_alternate_phone', 'Alternate phone', 'string', [
  'max_length' => 64,
]);

$create_field($node_entity_type, $site_profile_bundle, 'field_website', 'Website', 'link', [], $link_settings);

$create_field($node_entity_type, $site_profile_bundle, 'field_city', 'City', 'string', [
  'max_length' => 128,
]);

$create_field($node_entity_type, $site_profile_bundle, 'field_state', 'State', 'string', [
  'max_length' => 128,
]);

$create_field($node_entity_type, $site_profile_bundle, 'field_country', 'Country', 'string', [
  'max_length' => 128,
]);

$create_field($node_entity_type, $site_profile_bundle, 'field_postal_code', 'Postal code', 'string', [
  'max_length' => 32,
]);

$create_field($node_entity_type, $site_profile_bundle, 'field_working_hours', 'Working hours', 'string_long');

$create_field($node_entity_type, $site_profile_bundle, 'field_google_map_link', 'Google map link', 'link', [], $link_settings);

$create_field($node_entity_type, $site_profile_bundle, 'field_map_latitude', 'Map latitude', 'decimal', [
  'precision' => 10,
  'scale' => 7,
]);

$create_field($node_entity_type, $site_profile_bundle, 'field_map_longitude', 'Map longitude', 'decimal', [
  'precision' => 10,
  'scale' => 7,
]);

$create_field($node_entity_type, $site_profile_bundle, 'field_footer_description', 'Footer description', 'string_long');

$create_field($node_entity_type, $site_profile_bundle, 'field_main_menu', 'Main menu machine name', 'string', [
  'max_length' => 128,
]);

$create_field($node_entity_type, $site_profile_bundle, 'field_footer_quick_links_menu', 'Footer quick links menu machine name', 'string', [
  'max_length' => 128,
]);

$create_field($node_entity_type, $site_profile_bundle, 'field_footer_services_menu', 'Footer services menu machine name', 'string', [
  'max_length' => 128,
]);

/**
 * Site Profile social links paragraph reference.
 */
$create_field($node_entity_type, $site_profile_bundle, 'field_social_links', 'Social links', 'entity_reference_revisions', [
  'target_type' => 'paragraph',
], [
  'handler' => 'default:paragraph',
  'handler_settings' => [
    'target_bundles' => [
      $social_link_bundle => $social_link_bundle,
    ],
  ],
], -1);

/**
 * Configure Social Link form display.
 */
$paragraph_form_display = EntityFormDisplay::load("paragraph.{$social_link_bundle}.default")
  ?: EntityFormDisplay::create([
    'targetEntityType' => 'paragraph',
    'bundle' => $social_link_bundle,
    'mode' => 'default',
    'status' => TRUE,
  ]);

$paragraph_form_display
  ->setComponent('field_platform_name', [
    'type' => 'string_textfield',
    'weight' => 0,
  ])
  ->setComponent('field_profile_url', [
    'type' => 'link_default',
    'weight' => 1,
  ])
  ->setComponent('field_icon', [
    'type' => 'string_textfield',
    'weight' => 2,
  ])
  ->setComponent('field_display_order', [
    'type' => 'number',
    'weight' => 3,
  ])
  ->setComponent('field_is_active', [
    'type' => 'boolean_checkbox',
    'weight' => 4,
  ])
  ->save();

print "Updated Social Link paragraph form display.\n";

/**
 * Configure Social Link view display.
 */
$paragraph_view_display = EntityViewDisplay::load("paragraph.{$social_link_bundle}.default")
  ?: EntityViewDisplay::create([
    'targetEntityType' => 'paragraph',
    'bundle' => $social_link_bundle,
    'mode' => 'default',
    'status' => TRUE,
  ]);

foreach ([
  'field_platform_name',
  'field_profile_url',
  'field_icon',
  'field_display_order',
  'field_is_active',
] as $field_name) {
  $paragraph_view_display->setComponent($field_name, [
    'label' => 'above',
    'type' => 'string',
  ]);
}

$paragraph_view_display->save();

print "Updated Social Link paragraph view display.\n";

/**
 * Update Site Profile form display.
 */
$site_form_display = EntityFormDisplay::load("node.{$site_profile_bundle}.default");

if ($site_form_display) {
  $weight = 20;

  foreach ([
    'field_tagline' => 'string_textfield',
    'field_description' => 'string_textarea',
    'field_alternate_phone' => 'string_textfield',
    'field_website' => 'link_default',
    'field_city' => 'string_textfield',
    'field_state' => 'string_textfield',
    'field_country' => 'string_textfield',
    'field_postal_code' => 'string_textfield',
    'field_working_hours' => 'string_textarea',
    'field_google_map_link' => 'link_default',
    'field_map_latitude' => 'number',
    'field_map_longitude' => 'number',
    'field_footer_description' => 'string_textarea',
    'field_main_menu' => 'string_textfield',
    'field_footer_quick_links_menu' => 'string_textfield',
    'field_footer_services_menu' => 'string_textfield',
  ] as $field_name => $widget) {
    $site_form_display->setComponent($field_name, [
      'type' => $widget,
      'weight' => $weight++,
    ]);
  }

  $site_form_display->setComponent('field_social_links', [
    'type' => 'entity_reference_paragraphs',
    'weight' => $weight,
    'settings' => [
      'title' => 'Social link',
      'title_plural' => 'Social links',
      'edit_mode' => 'open',
      'add_mode' => 'dropdown',
      'form_display_mode' => 'default',
      'default_paragraph_type' => $social_link_bundle,
    ],
  ]);

  $site_form_display->save();

  print "Updated Site Profile form display.\n";
}

/**
 * Update Site Profile view display.
 */
$site_view_display = EntityViewDisplay::load("node.{$site_profile_bundle}.default");

if ($site_view_display) {
  foreach ([
    'field_tagline',
    'field_description',
    'field_alternate_phone',
    'field_website',
    'field_city',
    'field_state',
    'field_country',
    'field_postal_code',
    'field_working_hours',
    'field_google_map_link',
    'field_map_latitude',
    'field_map_longitude',
    'field_footer_description',
    'field_main_menu',
    'field_footer_quick_links_menu',
    'field_footer_services_menu',
  ] as $field_name) {
    $site_view_display->setComponent($field_name, [
      'label' => 'above',
      'type' => 'string',
    ]);
  }

  $site_view_display->setComponent('field_social_links', [
    'label' => 'above',
    'type' => 'entity_reference_revisions_entity_view',
    'settings' => [
      'view_mode' => 'default',
      'link' => '',
    ],
  ]);

  $site_view_display->save();

  print "Updated Site Profile view display.\n";
}

print "Phase 1A Site Profile extension complete.\n";
