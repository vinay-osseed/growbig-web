<?php

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

$node_storage = \Drupal::entityTypeManager()->getStorage('node');

function field_exists(string $entity_type, string $bundle, string $field_name): bool {
  $definitions = \Drupal::service('entity_field.manager')->getFieldDefinitions($entity_type, $bundle);
  return isset($definitions[$field_name]);
}

function create_or_get_paragraph(string $type, array $values): Paragraph {
  $paragraph = Paragraph::create([
    'type' => $type,
  ] + $values);
  $paragraph->save();

  return $paragraph;
}

function create_page(string $key, string $title, string $summary, string $page_type, array $sections): void {
  $storage = \Drupal::entityTypeManager()->getStorage('node');

  $existing = $storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', 'site_page')
    ->condition('field_page_key', $key)
    ->range(0, 1)
    ->execute();

  if ($existing) {
    print "Page already exists: {$key}\n";
    return;
  }

  $node_values = [
    'type' => 'site_page',
    'title' => $title,
    'status' => 1,
    'field_page_key' => $key,
    'field_page_type' => $page_type,
    'field_summary' => $summary,
    'field_sections' => array_map(static fn (Paragraph $paragraph): array => [
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    ], $sections),
  ];

  if (field_exists('node', 'site_page', 'field_meta_title')) {
    $node_values['field_meta_title'] = $title . ' | GrowBig Technologies LLP';
  }

  if (field_exists('node', 'site_page', 'field_meta_description')) {
    $node_values['field_meta_description'] = $summary;
  }

  if (field_exists('node', 'site_page', 'field_robots')) {
    $node_values['field_robots'] = 'index_follow';
  }

  $node = Node::create($node_values);
  $node->save();

  print "Created page: {$key}\n";
}

$careers_hero = create_or_get_paragraph('hero_section', [
  'field_badge' => 'Careers',
  'field_heading' => 'Build Your Career With GrowBig',
  'field_highlight_text' => 'Career',
  'field_description' => 'Join a growing team building websites, apps, platforms, and digital products for businesses.',
  'field_primary_cta_text' => 'View Open Positions',
  'field_primary_cta_url' => ['uri' => 'internal:/careers#open-positions'],
]);

$careers_jobs = create_or_get_paragraph('content_list_section', [
  'field_heading' => 'Open Positions',
  'field_description' => 'Explore current openings and apply for roles that match your skills.',
  'field_source' => 'jobs',
  'field_limit' => 0,
  'field_featured_only' => 0,
]);

$contact_hero = create_or_get_paragraph('hero_section', [
  'field_badge' => 'Contact Us',
  'field_heading' => 'Let’s Build Something Together',
  'field_highlight_text' => 'Together',
  'field_description' => 'Tell us about your project, product, or business goal. Our team will get back to you.',
  'field_primary_cta_text' => 'Send Message',
  'field_primary_cta_url' => ['uri' => 'internal:/contact#contact-form'],
]);

$contact_cta = create_or_get_paragraph('cta_section', [
  'field_heading' => 'Start a Conversation',
  'field_description' => 'Use the Contact Us form to share your requirement. Budget range is optional and can be shown only where needed.',
  'field_primary_cta_text' => 'Contact Form',
  'field_primary_cta_url' => ['uri' => 'internal:/api/v1/forms/contact-us'],
]);

create_page(
  'careers',
  'Careers',
  'Open roles and career opportunities at GrowBig Technologies LLP.',
  'careers',
  [$careers_hero, $careers_jobs]
);

create_page(
  'contact',
  'Contact',
  'Contact GrowBig Technologies LLP for project enquiries and business conversations.',
  'contact',
  [$contact_hero, $contact_cta]
);

print "Careers and Contact page setup complete.\n";
