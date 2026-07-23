<?php

declare(strict_types=1);

use Drupal\node\NodeInterface;

/**
 * Loads a Site Profile by key.
 */
function sp_content_load_site(string $site_key): NodeInterface {
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
 * Creates or updates a Site Content Block.
 */
function sp_content_save_block(NodeInterface $site, array $values): NodeInterface {
  $storage = \Drupal::entityTypeManager()->getStorage('node');
  $ids = $storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', 'site_content_block')
    ->condition('field_site_profile.target_id', $site->id())
    ->condition('field_content_source', $values['source'])
    ->condition('field_content_key', $values['key'])
    ->range(0, 1)
    ->execute();

  $node = NULL;
  if ($ids) {
    $loaded = $storage->load(reset($ids));
    if ($loaded instanceof NodeInterface) {
      $node = $loaded;
    }
  }

  if (!$node instanceof NodeInterface) {
    $node = $storage->create([
      'type' => 'site_content_block',
      'title' => $values['title'],
      'status' => 1,
      'uid' => 1,
    ]);
  }

  $node->setTitle($values['title']);
  $node->set('field_site_profile', ['target_id' => $site->id()]);
  $node->set('field_content_source', $values['source']);
  $node->set('field_content_key', $values['key']);
  $node->set('field_content_label', $values['label']);
  $node->set('field_content_variant', $values['variant'] ?? 'default');
  $node->set('field_content_summary', $values['summary'] ?? '');
  $node->set('field_content_body', $values['body'] ?? '');
  $node->set('field_content_is_active', TRUE);
  $node->set('field_content_weight', $values['weight'] ?? 0);
  $node->set('field_is_demo', TRUE);
  $node->set('field_demo_source', 'phase_10_verify');
  $node->save();

  return $node;
}

$growbig = sp_content_load_site('growbig');
$osseed = sp_content_load_site('osseed');

sp_content_save_block($growbig, [
  'title' => 'GrowBig Footer CTA',
  'source' => 'global',
  'key' => 'footer_cta',
  'label' => 'Footer CTA',
  'variant' => 'dark',
  'summary' => 'Ready to build your next digital platform?',
  'body' => 'Contact GrowBig to plan your next implementation.',
  'weight' => 0,
]);

sp_content_save_block($growbig, [
  'title' => 'GrowBig Announcement',
  'source' => 'global',
  'key' => 'announcement',
  'label' => 'Announcement',
  'variant' => 'banner',
  'summary' => 'New platform foundation is live.',
  'body' => 'Reusable site data, pages, menus, forms, and content are now available through APIs.',
  'weight' => 10,
]);

sp_content_save_block($growbig, [
  'title' => 'GrowBig Office',
  'source' => 'contact',
  'key' => 'office',
  'label' => 'Office',
  'variant' => 'default',
  'summary' => 'GrowBig Technologies LLP',
  'body' => 'India',
  'weight' => 0,
]);

sp_content_save_block($osseed, [
  'title' => 'OSSeed Footer CTA',
  'source' => 'global',
  'key' => 'footer_cta',
  'label' => 'Footer CTA',
  'variant' => 'default',
  'summary' => 'Need open source engineering help?',
  'body' => 'Contact OSSeed for implementation support.',
  'weight' => 0,
]);

drupal_flush_all_caches();

echo "Reusable content API demo data prepared.\n";
