<?php

declare(strict_types=1);

use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Loads a Site Profile by key.
 */
function sp_component_load_site(string $site_key): NodeInterface {
  $storage = \Drupal::entityTypeManager()->getStorage('node');
  $ids = $storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', 'site_profile')
    ->condition('field_site_key', $site_key)
    ->range(0, 1)
    ->execute();

  if (!$ids) {
    throw new RuntimeException("Missing Site Profile $site_key.");
  }

  $node = $storage->load(reset($ids));
  if (!$node instanceof NodeInterface) {
    throw new RuntimeException("Could not load Site Profile $site_key.");
  }

  return $node;
}

/**
 * Loads a Site Page by site and key.
 */
function sp_component_load_page(NodeInterface $site, string $page_key): NodeInterface {
  $storage = \Drupal::entityTypeManager()->getStorage('node');
  $ids = $storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', 'site_page')
    ->condition('field_site_profile.target_id', $site->id())
    ->condition('field_page_key', $page_key)
    ->range(0, 1)
    ->execute();

  if (!$ids) {
    throw new RuntimeException("Missing Site Page $page_key.");
  }

  $node = $storage->load(reset($ids));
  if (!$node instanceof NodeInterface) {
    throw new RuntimeException("Could not load Site Page $page_key.");
  }

  return $node;
}

/**
 * Creates or updates a paragraph component.
 */
function sp_component_save_paragraph(string $type, string $key, array $values): ParagraphInterface {
  $storage = \Drupal::entityTypeManager()->getStorage('paragraph');
  $ids = $storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', $type)
    ->condition('field_component_key', $key)
    ->range(0, 1)
    ->execute();

  $paragraph = NULL;
  if ($ids) {
    $loaded = $storage->load(reset($ids));
    if ($loaded instanceof ParagraphInterface) {
      $paragraph = $loaded;
    }
  }

  if (!$paragraph instanceof ParagraphInterface) {
    $paragraph = $storage->create(['type' => $type]);
  }

  $paragraph->set('field_component_key', $key);
  $paragraph->set('field_component_variant', $values['variant'] ?? 'default');
  $paragraph->set('field_component_admin_label', $values['admin_label'] ?? $key);

  foreach ([
    'field_component_title',
    'field_component_summary',
    'field_component_body',
    'field_component_media_url',
    'field_component_button_label',
    'field_component_button_path',
  ] as $field_name) {
    if ($paragraph->hasField($field_name)) {
      $paragraph->set($field_name, $values[$field_name] ?? '');
    }
  }

  $paragraph->save();

  return $paragraph;
}

/**
 * Assigns components to a page.
 */
function sp_component_assign_components(NodeInterface $page, array $components): void {
  $items = [];
  foreach ($components as $component) {
    if (!$component instanceof ParagraphInterface) {
      continue;
    }
    $items[] = [
      'target_id' => $component->id(),
      'target_revision_id' => $component->getRevisionId(),
    ];
  }

  $page->set('field_page_components', $items);
  $page->save();
}

$growbig = sp_component_load_site('growbig');
$osseed = sp_component_load_site('osseed');

$growbig_home = sp_component_load_page($growbig, 'home');
$growbig_about = sp_component_load_page($growbig, 'about');
$osseed_home = sp_component_load_page($osseed, 'home');

$growbig_hero = sp_component_save_paragraph('site_hero', 'growbig-home-hero', [
  'variant' => 'split',
  'admin_label' => 'GrowBig home hero',
  'field_component_title' => 'Grow your business with a modern digital platform',
  'field_component_summary' => 'A reusable Drupal backend for multi-site frontend delivery.',
  'field_component_media_url' => '/assets/demo/growbig-hero.png',
  'field_component_button_label' => 'Get Started',
  'field_component_button_path' => '/contact',
]);

$growbig_intro = sp_component_save_paragraph('site_rich_text', 'growbig-home-intro', [
  'variant' => 'default',
  'admin_label' => 'GrowBig intro text',
  'field_component_title' => 'Built for scalable websites',
  'field_component_body' => 'Site Platform separates site identity, page data, menus, and components for framework-neutral frontends.',
]);

$growbig_cta = sp_component_save_paragraph('site_cta', 'growbig-home-cta', [
  'variant' => 'dark',
  'admin_label' => 'GrowBig home CTA',
  'field_component_title' => 'Ready to launch faster?',
  'field_component_summary' => 'Use one backend platform for many sites.',
  'field_component_button_label' => 'Contact us',
  'field_component_button_path' => '/contact',
]);

$growbig_about_text = sp_component_save_paragraph('site_rich_text', 'growbig-about-text', [
  'variant' => 'default',
  'admin_label' => 'GrowBig about text',
  'field_component_title' => 'About GrowBig',
  'field_component_body' => 'GrowBig helps teams build reliable digital platforms.',
]);

$osseed_hero = sp_component_save_paragraph('site_hero', 'osseed-home-hero', [
  'variant' => 'default',
  'admin_label' => 'OSSeed home hero',
  'field_component_title' => 'OSSeed Technologies LLP',
  'field_component_summary' => 'Open source engineering and implementation services.',
  'field_component_button_label' => 'Learn more',
  'field_component_button_path' => '/',
]);

sp_component_assign_components($growbig_home, [$growbig_hero, $growbig_intro, $growbig_cta]);
sp_component_assign_components($growbig_about, [$growbig_about_text]);
sp_component_assign_components($osseed_home, [$osseed_hero]);

drupal_flush_all_caches();

echo "Component API demo data prepared.\n";
