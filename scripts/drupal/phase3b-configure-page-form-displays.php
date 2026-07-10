<?php

declare(strict_types=1);

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;

/**
 * Phase 3B: Configures Site Page and section paragraph form displays.
 *
 * This script is idempotent and safe to re-run.
 */

$set_form_components = static function (
  string $entity_type,
  string $bundle,
  array $components,
): void {
  $display = EntityFormDisplay::load("{$entity_type}.{$bundle}.default")
    ?: EntityFormDisplay::create([
      'targetEntityType' => $entity_type,
      'bundle' => $bundle,
      'mode' => 'default',
      'status' => TRUE,
    ]);

  $weight = 0;

  foreach ($components as $field_name => $widget) {
    $component = [
      'type' => $widget,
      'weight' => $weight++,
    ];

    if ($widget === 'entity_reference_paragraphs') {
      $component['settings'] = [
        'title' => 'Section',
        'title_plural' => 'Sections',
        'edit_mode' => 'open',
        'add_mode' => 'dropdown',
        'form_display_mode' => 'default',
        'default_paragraph_type' => '',
      ];
    }

    $display->setComponent($field_name, $component);
  }

  $display->save();

  print "Updated form display: {$entity_type}.{$bundle}.default\n";
};

$set_view_components = static function (
  string $entity_type,
  string $bundle,
  array $fields,
): void {
  $display = EntityViewDisplay::load("{$entity_type}.{$bundle}.default")
    ?: EntityViewDisplay::create([
      'targetEntityType' => $entity_type,
      'bundle' => $bundle,
      'mode' => 'default',
      'status' => TRUE,
    ]);

  $weight = 0;

  foreach ($fields as $field_name) {
    $display->setComponent($field_name, [
      'label' => 'above',
      'type' => 'string',
      'weight' => $weight++,
    ]);
  }

  $display->save();

  print "Updated view display: {$entity_type}.{$bundle}.default\n";
};

/**
 * Site Page form display.
 */
$set_form_components('node', 'site_page', [
  'field_page_key' => 'string_textfield',
  'field_page_type' => 'options_select',
  'field_summary' => 'string_textarea',
  'field_sites' => 'entity_reference_autocomplete',
  'field_sections' => 'entity_reference_paragraphs',
  'field_meta_title' => 'string_textfield',
  'field_meta_description' => 'string_textarea',
  'field_meta_keywords' => 'string_textfield',
  'field_canonical_url' => 'link_default',
  'field_og_title' => 'string_textfield',
  'field_og_description' => 'string_textarea',
  'field_og_image' => 'entity_reference_autocomplete',
  'field_robots' => 'options_select',
]);

$set_view_components('node', 'site_page', [
  'field_page_key',
  'field_page_type',
  'field_summary',
  'field_sites',
  'field_sections',
  'field_meta_title',
  'field_meta_description',
  'field_meta_keywords',
  'field_canonical_url',
  'field_og_title',
  'field_og_description',
  'field_og_image',
  'field_robots',
]);

/**
 * Hero Section.
 */
$set_form_components('paragraph', 'hero_section', [
  'field_badge' => 'string_textfield',
  'field_heading' => 'string_textfield',
  'field_highlight_text' => 'string_textfield',
  'field_description' => 'string_textarea',
  'field_primary_button_text' => 'string_textfield',
  'field_primary_button_link' => 'link_default',
  'field_secondary_button_text' => 'string_textfield',
  'field_secondary_button_link' => 'link_default',
  'field_image' => 'entity_reference_autocomplete',
  'field_image_alt_text' => 'string_textfield',
  'field_layout_variant' => 'options_select',
  'field_background_style' => 'options_select',
]);

/**
 * CTA Section.
 */
$set_form_components('paragraph', 'cta_section', [
  'field_badge' => 'string_textfield',
  'field_heading' => 'string_textfield',
  'field_description' => 'string_textarea',
  'field_primary_button_text' => 'string_textfield',
  'field_primary_button_link' => 'link_default',
  'field_secondary_button_text' => 'string_textfield',
  'field_secondary_button_link' => 'link_default',
  'field_layout_variant' => 'options_select',
  'field_background_style' => 'options_select',
]);

/**
 * Stats Section.
 */
$set_form_components('paragraph', 'stats_section', [
  'field_stats_items' => 'entity_reference_paragraphs',
]);

/**
 * Stats Item.
 */
$set_form_components('paragraph', 'stats_item', [
  'field_title' => 'string_textfield',
  'field_count' => 'number',
  'field_prefix' => 'string_textfield',
  'field_suffix' => 'string_textfield',
  'field_icon' => 'string_textfield',
  'field_display_order' => 'number',
]);

/**
 * Card Grid Section.
 */
$set_form_components('paragraph', 'card_grid_section', [
  'field_badge' => 'string_textfield',
  'field_heading' => 'string_textfield',
  'field_description' => 'string_textarea',
  'field_cards' => 'entity_reference_paragraphs',
  'field_primary_button_text' => 'string_textfield',
  'field_primary_button_link' => 'link_default',
  'field_secondary_button_text' => 'string_textfield',
  'field_secondary_button_link' => 'link_default',
  'field_layout_variant' => 'options_select',
]);

/**
 * Card Item.
 */
$set_form_components('paragraph', 'card_item', [
  'field_title' => 'string_textfield',
  'field_description' => 'string_textarea',
  'field_icon' => 'string_textfield',
  'field_image' => 'entity_reference_autocomplete',
  'field_accent_color' => 'string_textfield',
  'field_link_text' => 'string_textfield',
  'field_link_url' => 'link_default',
  'field_display_order' => 'number',
  'field_is_active' => 'boolean_checkbox',
]);

/**
 * Image Text Section.
 */
$set_form_components('paragraph', 'image_text_section', [
  'field_badge' => 'string_textfield',
  'field_heading' => 'string_textfield',
  'field_description' => 'string_textarea',
  'field_image' => 'entity_reference_autocomplete',
  'field_image_position' => 'options_select',
  'field_primary_button_text' => 'string_textfield',
  'field_primary_button_link' => 'link_default',
  'field_secondary_button_text' => 'string_textfield',
  'field_secondary_button_link' => 'link_default',
  'field_layout_variant' => 'options_select',
]);

/**
 * Content List Section.
 */
$set_form_components('paragraph', 'content_list_section', [
  'field_badge' => 'string_textfield',
  'field_heading' => 'string_textfield',
  'field_description' => 'string_textarea',
  'field_content_source' => 'options_select',
  'field_limit' => 'number',
  'field_featured_only' => 'boolean_checkbox',
  'field_primary_button_text' => 'string_textfield',
  'field_primary_button_link' => 'link_default',
  'field_secondary_button_text' => 'string_textfield',
  'field_secondary_button_link' => 'link_default',
  'field_layout_variant' => 'options_select',
]);

foreach ([
  'hero_section',
  'cta_section',
  'stats_section',
  'stats_item',
  'card_grid_section',
  'card_item',
  'image_text_section',
  'content_list_section',
] as $bundle) {
  $fields = array_keys(\Drupal::service('entity_field.manager')->getFieldDefinitions('paragraph', $bundle));
  $custom_fields = array_filter($fields, static fn(string $field_name): bool => str_starts_with($field_name, 'field_'));
  $set_view_components('paragraph', $bundle, array_values($custom_fields));
}

print "Phase 3B form display setup complete.\n";
