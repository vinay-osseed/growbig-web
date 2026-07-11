<?php

declare(strict_types=1);

use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;

$entity_type_manager = \Drupal::entityTypeManager();
$field_manager = \Drupal::service('entity_field.manager');

$node_storage = $entity_type_manager->getStorage('node');

$page_ids = $node_storage->getQuery()
  ->accessCheck(FALSE)
  ->condition('type', 'site_page')
  ->condition('field_page_key', 'about')
  ->range(0, 1)
  ->execute();

if (!$page_ids) {
  $page_ids = $node_storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', 'site_page')
    ->condition('title', 'About')
    ->range(0, 1)
    ->execute();
}

if (!$page_ids) {
  throw new RuntimeException('About page not found.');
}

/** @var \Drupal\node\NodeInterface $about */
$about = $node_storage->load(reset($page_ids));
if (!$about instanceof NodeInterface) {
  throw new RuntimeException('About page could not be loaded.');
}

if (!$about->hasField('field_sections')) {
  throw new RuntimeException('About page has no field_sections field.');
}

function paragraph_bundle_exists(string $bundle): bool {
  return \Drupal::entityTypeManager()
    ->getStorage('paragraphs_type')
    ->load($bundle) !== NULL;
}

function set_first_available_field(object $entity, array $field_names, mixed $value): void {
  if (!method_exists($entity, 'hasField')) {
    return;
  }

  foreach ($field_names as $field_name) {
    if ($entity->hasField($field_name)) {
      $entity->set($field_name, $value);
      return;
    }
  }
}

function set_child_paragraphs(object $entity, array $children): void {
  if (!method_exists($entity, 'getFieldDefinitions')) {
    return;
  }

  foreach ($entity->getFieldDefinitions() as $field_name => $definition) {
    $settings = $definition->getSettings();
    $type = $definition->getType();

    if (
      in_array($type, ['entity_reference_revisions', 'entity_reference'], TRUE)
      && ($settings['target_type'] ?? '') === 'paragraph'
    ) {
      $items = [];
      foreach ($children as $child) {
        $items[] = [
          'target_id' => $child->id(),
          'target_revision_id' => $child->getRevisionId(),
        ];
      }
      $entity->set($field_name, $items);
      return;
    }
  }
}

function create_mockup_paragraph(string $preferred_bundle, array $fields, array $children = []): ?Paragraph {
  $bundle = paragraph_bundle_exists($preferred_bundle) ? $preferred_bundle : NULL;

  if ($bundle === NULL && paragraph_bundle_exists('card_item')) {
    $bundle = 'card_item';
  }

  if ($bundle === NULL) {
    return NULL;
  }

  $paragraph = Paragraph::create(['type' => $bundle]);

  foreach ($fields as $field_names => $value) {
    set_first_available_field($paragraph, explode('|', $field_names), $value);
  }

  if ($children !== []) {
    set_child_paragraphs($paragraph, $children);
  }

  $paragraph->save();

  return $paragraph;
}

function page_contains_text(NodeInterface $page, string $needle): bool {
  $json = json_encode($page->toArray());
  if (is_string($json) && str_contains($json, $needle)) {
    return TRUE;
  }

  if (!$page->hasField('field_sections')) {
    return FALSE;
  }

  foreach ($page->get('field_sections')->referencedEntities() as $paragraph) {
    $payload = json_encode($paragraph->toArray());
    if (is_string($payload) && str_contains($payload, $needle)) {
      return TRUE;
    }

    if (method_exists($paragraph, 'getFieldDefinitions')) {
      foreach ($paragraph->getFieldDefinitions() as $field_name => $definition) {
        $settings = $definition->getSettings();
        if (($settings['target_type'] ?? '') !== 'paragraph') {
          continue;
        }

        foreach ($paragraph->get($field_name)->referencedEntities() as $child) {
          $child_payload = json_encode($child->toArray());
          if (is_string($child_payload) && str_contains($child_payload, $needle)) {
            return TRUE;
          }
        }
      }
    }
  }

  return FALSE;
}

$sections_to_append = [];

if (!page_contains_text($about, '150+')) {
  $stats_children = [];

  foreach ([
    ['150+ Projects', 'Projects delivered for startups, SMEs, and enterprises.'],
    ['50+ Clients', 'Trusted by growing businesses and teams.'],
    ['30+ Team Members', 'Designers, developers, strategists, and consultants.'],
    ['6+ Years', 'Experience building practical digital solutions.'],
  ] as [$title, $summary]) {
    $child = create_mockup_paragraph('stats_item', [
      'field_title|field_heading|field_label' => $title,
      'field_description|field_summary' => $summary,
    ]);
    if ($child) {
      $stats_children[] = $child;
    }
  }

  $stats_section = create_mockup_paragraph('stats_section', [
    'field_title|field_heading' => 'Growth Snapshot',
    'field_description|field_summary' => '150+ Projects, 50+ Clients, 30+ Team Members, and 6+ Years of experience.',
  ], $stats_children);

  if ($stats_section) {
    $sections_to_append[] = $stats_section;
  }
}

if (!page_contains_text($about, 'Rahul Sawant')) {
  $leader_children = [];

  foreach ([
    ['Rahul Sawant', 'Founder and CEO'],
    ['Priya Desai', 'Chief Technology Officer'],
    ['Arjun Naik', 'Head of Design'],
    ['Sneha Patil', 'Head of Delivery'],
  ] as [$name, $role]) {
    $child = create_mockup_paragraph('card_item', [
      'field_title|field_heading' => $name,
      'field_description|field_summary' => $role,
    ]);
    if ($child) {
      $leader_children[] = $child;
    }
  }

  $leadership_section = create_mockup_paragraph('card_grid_section', [
    'field_badge' => 'The Team',
    'field_title|field_heading' => 'Leadership',
    'field_description|field_summary' => 'Rahul Sawant, Priya Desai, Arjun Naik, and Sneha Patil lead strategy, technology, design, and delivery at GrowBig.',
  ], $leader_children);

  if ($leadership_section) {
    $sections_to_append[] = $leadership_section;
  }
}

if ($sections_to_append === []) {
  echo "About mockup content already exists.\n";
  return;
}

$current = [];
foreach ($about->get('field_sections')->getValue() as $item) {
  $current[] = $item;
}

foreach ($sections_to_append as $section) {
  $current[] = [
    'target_id' => $section->id(),
    'target_revision_id' => $section->getRevisionId(),
  ];
}

$about->set('field_sections', $current);
$about->save();

echo "Added " . count($sections_to_append) . " missing About mockup sections.\n";
