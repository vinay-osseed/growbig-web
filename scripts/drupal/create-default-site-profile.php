<?php

declare(strict_types=1);

use Drupal\node\Entity\Node;

/**
 * Creates or updates the default Site Profile content item.
 */

$storage = \Drupal::entityTypeManager()->getStorage('node');

$existing = $storage->getQuery()
  ->accessCheck(FALSE)
  ->condition('type', 'site_profile')
  ->condition('field_site_key', 'growbig')
  ->range(0, 1)
  ->execute();

$values = [
  'type' => 'site_profile',
  'title' => 'GrowBig Technologies LLP',
  'status' => TRUE,
  'field_site_key' => 'growbig',
  'field_site_short_name' => 'GrowBig',
  'field_primary_domain' => [
    'uri' => 'https://growbig-web.ddev.site',
  ],
  'field_ui_domain' => [
    'uri' => 'https://ui.growbig-web.ddev.site',
  ],
  'field_admin_domain' => [
    'uri' => 'https://admin.growbig-web.ddev.site',
  ],
  'field_api_domain' => [
    'uri' => 'https://api.growbig-web.ddev.site',
  ],
  'field_contact_email' => 'vinay@osseed.com',
  'field_default_meta_title' => 'GrowBig Technologies LLP',
  'field_theme_color' => '#0f172a',
  'field_is_default' => TRUE,
  'field_is_active' => TRUE,
];

if ($existing) {
  $node = $storage->load(reset($existing));

  if ($node instanceof Node) {
    foreach ($values as $field_name => $value) {
      $node->set($field_name, $value);
    }

    $node->save();

    print "Updated default Site Profile node: {$node->id()}\n";
  }

  return;
}

$node = Node::create($values);
$node->save();

print "Created default Site Profile node: {$node->id()}\n";
