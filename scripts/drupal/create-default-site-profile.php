<?php

declare(strict_types=1);

use Drupal\node\Entity\Node;

/**
 * Creates the default Site Profile content item if it does not exist.
 */

$storage = \Drupal::entityTypeManager()->getStorage('node');

$existing = $storage->getQuery()
  ->accessCheck(FALSE)
  ->condition('type', 'site_profile')
  ->condition('field_site_key', 'growbig')
  ->range(0, 1)
  ->execute();

if ($existing) {
  print "Default Site Profile already exists.\n";
  return;
}

$node = Node::create([
  'type' => 'site_profile',
  'title' => 'Site Platform',
  'status' => TRUE,
  'field_site_key' => 'growbig',
  'field_site_short_name' => 'GrowBig',
  'field_ui_domain' => [
    'uri' => 'https://ui.growbig-web.ddev.site',
  ],
  'field_admin_domain' => [
    'uri' => 'https://admin.growbig-web.ddev.site',
  ],
  'field_api_domain' => [
    'uri' => 'https://api.growbig-web.ddev.site',
  ],
  'field_contact_email' => 'admin@example.com',
  'field_default_meta_title' => 'Site Platform',
  'field_theme_color' => '#0f172a',
  'field_is_default' => TRUE,
  'field_is_active' => TRUE,
]);

$node->save();

print "Created default Site Profile node: {$node->id()}\n";
