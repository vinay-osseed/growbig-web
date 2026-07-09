<?php

declare(strict_types=1);

use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;

$node_ids = \Drupal::entityTypeManager()
  ->getStorage('node')
  ->getQuery()
  ->accessCheck(FALSE)
  ->condition('type', 'site_page')
  ->condition('field_page_key', 'careers')
  ->range(0, 1)
  ->execute();

if (!$node_ids) {
  throw new RuntimeException('Careers page not found.');
}

$page = \Drupal::entityTypeManager()
  ->getStorage('node')
  ->load(reset($node_ids));

if (!$page instanceof NodeInterface) {
  throw new RuntimeException('Careers page could not be loaded.');
}

if (!$page->hasField('field_sections') || $page->get('field_sections')->isEmpty()) {
  throw new RuntimeException('Careers page has no sections.');
}

$updated = FALSE;

foreach ($page->get('field_sections')->referencedEntities() as $section) {
  if (!$section instanceof ParagraphInterface || $section->bundle() !== 'content_list_section') {
    continue;
  }

  $fields = array_keys($section->getFieldDefinitions());

  echo "Found content_list_section paragraph ID " . $section->id() . PHP_EOL;
  echo "Available custom fields:" . PHP_EOL;

  foreach ($fields as $field_name) {
    if (str_starts_with($field_name, 'field_')) {
      echo "- " . $field_name . PHP_EOL;
    }
  }

  $source_fields = [
    'field_source',
    'field_content_source',
    'field_list_source',
    'field_content_list_source',
  ];

  foreach ($source_fields as $field_name) {
    if ($section->hasField($field_name)) {
      $section->set($field_name, 'jobs');
      echo "Set {$field_name} = jobs" . PHP_EOL;
      $updated = TRUE;
      break;
    }
  }

  $limit_fields = [
    'field_limit',
    'field_item_limit',
    'field_content_limit',
  ];

  foreach ($limit_fields as $field_name) {
    if ($section->hasField($field_name)) {
      $section->set($field_name, 0);
      echo "Set {$field_name} = 0" . PHP_EOL;
      break;
    }
  }

  $featured_fields = [
    'field_featured_only',
    'field_show_featured_only',
    'field_content_featured_only',
  ];

  foreach ($featured_fields as $field_name) {
    if ($section->hasField($field_name)) {
      $section->set($field_name, 0);
      echo "Set {$field_name} = 0" . PHP_EOL;
      break;
    }
  }

  $section->save();
}

if (!$updated) {
  throw new RuntimeException('Could not find source field on content_list_section.');
}

$page->save();

echo "Careers jobs section fixed." . PHP_EOL;
