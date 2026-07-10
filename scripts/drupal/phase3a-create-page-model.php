<?php

declare(strict_types=1);

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\NodeType;
use Drupal\paragraphs\Entity\ParagraphsType;

/**
 * Phase 3A: Creates reusable Page content model and section paragraphs.
 *
 * This script is idempotent and safe to re-run.
 */

$node_entity_type = 'node';
$paragraph_entity_type = 'paragraph';
$page_bundle = 'site_page';

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
 * Creates a paragraph type if missing.
 */
$create_paragraph_type = static function (
  string $id,
  string $label,
  string $description,
): void {
  if (!ParagraphsType::load($id)) {
    ParagraphsType::create([
      'id' => $id,
      'label' => $label,
      'description' => $description,
    ])->save();

    print "Created paragraph type: {$id}\n";
  }
  else {
    print "Paragraph type already exists: {$id}\n";
  }
};

/**
 * Creates Site Page content type.
 */
if (!NodeType::load($page_bundle)) {
  NodeType::create([
    'type' => $page_bundle,
    'name' => 'Site Page',
    'description' => 'Reusable frontend page composed from structured sections.',
    'title_label' => 'Page title',
  ])->save();

  print "Created content type: {$page_bundle}\n";
}
else {
  print "Content type already exists: {$page_bundle}\n";
}

/**
 * Create paragraph bundles.
 */
$paragraph_types = [
  'hero_section' => [
    'label' => 'Hero Section',
    'description' => 'Hero/banner section with heading, highlight text, buttons, image, and style variants.',
  ],
  'cta_section' => [
    'label' => 'CTA Section',
    'description' => 'Call-to-action section with heading, description, and buttons.',
  ],
  'stats_section' => [
    'label' => 'Stats Section',
    'description' => 'Container for statistic items.',
  ],
  'stats_item' => [
    'label' => 'Stats Item',
    'description' => 'Single statistic item with count, label, prefix, suffix, and icon.',
  ],
  'card_grid_section' => [
    'label' => 'Card Grid Section',
    'description' => 'Section containing reusable cards.',
  ],
  'card_item' => [
    'label' => 'Card Item',
    'description' => 'Single card item with icon, title, description, color, and link.',
  ],
  'image_text_section' => [
    'label' => 'Image Text Section',
    'description' => 'Section with image and text content.',
  ],
  'content_list_section' => [
    'label' => 'Content List Section',
    'description' => 'Dynamic section that pulls reusable content like services, partners, or team members.',
  ],
];

foreach ($paragraph_types as $id => $info) {
  $create_paragraph_type($id, $info['label'], $info['description']);
}

$link_settings = [
  'link_type' => 17,
  'title' => 0,
];

/**
 * Shared paragraph fields.
 */
foreach ([
  'hero_section',
  'cta_section',
  'card_grid_section',
  'image_text_section',
  'content_list_section',
] as $bundle) {
  $create_field($paragraph_entity_type, $bundle, 'field_badge', 'Badge', 'string', ['max_length' => 255]);
  $create_field($paragraph_entity_type, $bundle, 'field_heading', 'Heading', 'string', ['max_length' => 255]);
  $create_field($paragraph_entity_type, $bundle, 'field_description', 'Description', 'string_long');
  $create_field($paragraph_entity_type, $bundle, 'field_primary_button_text', 'Primary button text', 'string', ['max_length' => 128]);
  $create_field($paragraph_entity_type, $bundle, 'field_primary_button_link', 'Primary button link', 'link', [], $link_settings);
  $create_field($paragraph_entity_type, $bundle, 'field_secondary_button_text', 'Secondary button text', 'string', ['max_length' => 128]);
  $create_field($paragraph_entity_type, $bundle, 'field_secondary_button_link', 'Secondary button link', 'link', [], $link_settings);
  $create_field($paragraph_entity_type, $bundle, 'field_layout_variant', 'Layout variant', 'list_string', [], [
    'allowed_values' => [
      'default' => 'Default',
      'centered' => 'Centered',
      'split_image_right' => 'Split image right',
      'split_image_left' => 'Split image left',
    ],
  ]);
}

/**
 * Hero fields.
 */
$create_field($paragraph_entity_type, 'hero_section', 'field_highlight_text', 'Highlight text', 'string', ['max_length' => 255]);
$create_field($paragraph_entity_type, 'hero_section', 'field_image', 'Image', 'entity_reference', [
  'target_type' => 'media',
], [
  'handler' => 'default:media',
  'handler_settings' => [
    'target_bundles' => [
      'image' => 'image',
    ],
  ],
]);
$create_field($paragraph_entity_type, 'hero_section', 'field_image_alt_text', 'Image alt text', 'string', ['max_length' => 255]);
$create_field($paragraph_entity_type, 'hero_section', 'field_background_style', 'Background style', 'list_string', [], [
  'allowed_values' => [
    'default' => 'Default',
    'dark_grid' => 'Dark grid',
    'light' => 'Light',
    'brand_gradient' => 'Brand gradient',
  ],
]);

/**
 * CTA fields.
 */
$create_field($paragraph_entity_type, 'cta_section', 'field_background_style', 'Background style', 'list_string', [], [
  'allowed_values' => [
    'default' => 'Default',
    'dark' => 'Dark',
    'brand' => 'Brand',
    'light' => 'Light',
  ],
]);

/**
 * Stats fields.
 */
$create_field($paragraph_entity_type, 'stats_section', 'field_stats_items', 'Stats items', 'entity_reference_revisions', [
  'target_type' => 'paragraph',
], [
  'handler' => 'default:paragraph',
  'handler_settings' => [
    'target_bundles' => [
      'stats_item' => 'stats_item',
    ],
  ],
], -1);

$create_field($paragraph_entity_type, 'stats_item', 'field_title', 'Title', 'string', ['max_length' => 255], [], 1, TRUE);
$create_field($paragraph_entity_type, 'stats_item', 'field_count', 'Count', 'integer', [], [], 1, TRUE);
$create_field($paragraph_entity_type, 'stats_item', 'field_prefix', 'Prefix', 'string', ['max_length' => 32]);
$create_field($paragraph_entity_type, 'stats_item', 'field_suffix', 'Suffix', 'string', ['max_length' => 32]);
$create_field($paragraph_entity_type, 'stats_item', 'field_icon', 'Icon', 'string', ['max_length' => 128]);
$create_field($paragraph_entity_type, 'stats_item', 'field_display_order', 'Display order', 'integer');

/**
 * Card fields.
 */
$create_field($paragraph_entity_type, 'card_grid_section', 'field_cards', 'Cards', 'entity_reference_revisions', [
  'target_type' => 'paragraph',
], [
  'handler' => 'default:paragraph',
  'handler_settings' => [
    'target_bundles' => [
      'card_item' => 'card_item',
    ],
  ],
], -1);

$create_field($paragraph_entity_type, 'card_item', 'field_title', 'Title', 'string', ['max_length' => 255], [], 1, TRUE);
$create_field($paragraph_entity_type, 'card_item', 'field_description', 'Description', 'string_long');
$create_field($paragraph_entity_type, 'card_item', 'field_icon', 'Icon', 'string', ['max_length' => 128]);
$create_field($paragraph_entity_type, 'card_item', 'field_image', 'Image', 'entity_reference', [
  'target_type' => 'media',
], [
  'handler' => 'default:media',
  'handler_settings' => [
    'target_bundles' => [
      'image' => 'image',
    ],
  ],
]);
$create_field($paragraph_entity_type, 'card_item', 'field_accent_color', 'Accent color', 'string', ['max_length' => 32]);
$create_field($paragraph_entity_type, 'card_item', 'field_link_text', 'Link text', 'string', ['max_length' => 128]);
$create_field($paragraph_entity_type, 'card_item', 'field_link_url', 'Link URL', 'link', [], $link_settings);
$create_field($paragraph_entity_type, 'card_item', 'field_display_order', 'Display order', 'integer');
$create_field($paragraph_entity_type, 'card_item', 'field_is_active', 'Is active', 'boolean');

/**
 * Image text fields.
 */
$create_field($paragraph_entity_type, 'image_text_section', 'field_image', 'Image', 'entity_reference', [
  'target_type' => 'media',
], [
  'handler' => 'default:media',
  'handler_settings' => [
    'target_bundles' => [
      'image' => 'image',
    ],
  ],
]);
$create_field($paragraph_entity_type, 'image_text_section', 'field_image_position', 'Image position', 'list_string', [], [
  'allowed_values' => [
    'left' => 'Left',
    'right' => 'Right',
  ],
]);

/**
 * Content list fields.
 */
$create_field($paragraph_entity_type, 'content_list_section', 'field_content_source', 'Content source', 'list_string', [], [
  'allowed_values' => [
    'services' => 'Services',
    'partners' => 'Partners',
    'team' => 'Team',
    'jobs' => 'Jobs',
  ],
]);
$create_field($paragraph_entity_type, 'content_list_section', 'field_limit', 'Limit', 'integer');
$create_field($paragraph_entity_type, 'content_list_section', 'field_featured_only', 'Featured only', 'boolean');

/**
 * Site Page fields.
 */
$create_field($node_entity_type, $page_bundle, 'field_page_key', 'Page key', 'string', ['max_length' => 128], [], 1, TRUE);
$create_field($node_entity_type, $page_bundle, 'field_page_type', 'Page type', 'list_string', [], [
  'allowed_values' => [
    'home' => 'Home',
    'about' => 'About',
    'services' => 'Services',
    'careers' => 'Careers',
    'contact' => 'Contact',
    'standard' => 'Standard',
    'landing' => 'Landing',
  ],
], 1, TRUE);
$create_field($node_entity_type, $page_bundle, 'field_summary', 'Summary', 'string_long');
$create_field($node_entity_type, $page_bundle, 'field_sites', 'Sites', 'entity_reference', [
  'target_type' => 'node',
], [
  'handler' => 'default:node',
  'handler_settings' => [
    'target_bundles' => [
      'site_profile' => 'site_profile',
    ],
  ],
], -1);
$create_field($node_entity_type, $page_bundle, 'field_sections', 'Sections', 'entity_reference_revisions', [
  'target_type' => 'paragraph',
], [
  'handler' => 'default:paragraph',
  'handler_settings' => [
    'target_bundles' => [
      'hero_section' => 'hero_section',
      'cta_section' => 'cta_section',
      'stats_section' => 'stats_section',
      'card_grid_section' => 'card_grid_section',
      'image_text_section' => 'image_text_section',
      'content_list_section' => 'content_list_section',
    ],
  ],
], -1);

/**
 * SEO fields.
 */
$create_field($node_entity_type, $page_bundle, 'field_meta_title', 'Meta title', 'string', ['max_length' => 255]);
$create_field($node_entity_type, $page_bundle, 'field_meta_description', 'Meta description', 'string_long');
$create_field($node_entity_type, $page_bundle, 'field_meta_keywords', 'Meta keywords', 'string', ['max_length' => 255]);
$create_field($node_entity_type, $page_bundle, 'field_canonical_url', 'Canonical URL', 'link', [], $link_settings);
$create_field($node_entity_type, $page_bundle, 'field_og_title', 'Open Graph title', 'string', ['max_length' => 255]);
$create_field($node_entity_type, $page_bundle, 'field_og_description', 'Open Graph description', 'string_long');
$create_field($node_entity_type, $page_bundle, 'field_og_image', 'Open Graph image', 'entity_reference', [
  'target_type' => 'media',
], [
  'handler' => 'default:media',
  'handler_settings' => [
    'target_bundles' => [
      'image' => 'image',
    ],
  ],
]);
$create_field($node_entity_type, $page_bundle, 'field_robots', 'Robots setting', 'list_string', [], [
  'allowed_values' => [
    'index_follow' => 'Index, follow',
    'noindex_follow' => 'No index, follow',
    'noindex_nofollow' => 'No index, no follow',
  ],
]);

print "Phase 3A Page model setup complete.\n";
