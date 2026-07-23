<?php

declare(strict_types=1);

use Drupal\node\NodeInterface;

/**
 * Loads a Site Profile by key.
 */
function sp_menu_load_site(string $site_key): NodeInterface {
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
function sp_menu_load_page(NodeInterface $site, string $page_key): NodeInterface {
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
 * Creates or updates a Site Menu.
 */
function sp_menu_save_menu(NodeInterface $site, array $values): NodeInterface {
  $storage = \Drupal::entityTypeManager()->getStorage('node');
  $ids = $storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', 'site_menu')
    ->condition('field_site_profile.target_id', $site->id())
    ->condition('field_menu_key', $values['key'])
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
      'type' => 'site_menu',
      'title' => $values['title'],
      'status' => 1,
      'uid' => 1,
    ]);
  }

  $node->setTitle($values['title']);
  $node->set('field_site_profile', ['target_id' => $site->id()]);
  $node->set('field_menu_key', $values['key']);
  $node->set('field_menu_label', $values['label']);
  $node->set('field_menu_is_active', TRUE);
  $node->set('field_menu_weight', $values['weight']);
  $node->set('field_is_demo', TRUE);
  $node->set('field_demo_source', 'phase_07_verify');
  $node->save();

  return $node;
}

/**
 * Creates or updates a Site Menu Item.
 */
function sp_menu_save_item(NodeInterface $site, NodeInterface $menu, array $values): NodeInterface {
  $storage = \Drupal::entityTypeManager()->getStorage('node');
  $ids = $storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', 'site_menu_item')
    ->condition('field_site_menu.target_id', $menu->id())
    ->condition('field_menu_item_key', $values['key'])
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
      'type' => 'site_menu_item',
      'title' => $values['title'],
      'status' => 1,
      'uid' => 1,
    ]);
  }

  $node->setTitle($values['title']);
  $node->set('field_site_profile', ['target_id' => $site->id()]);
  $node->set('field_site_menu', ['target_id' => $menu->id()]);
  $node->set('field_menu_item_key', $values['key']);
  $node->set('field_menu_title', $values['title']);
  $node->set('field_menu_link_type', $values['link_type']);
  $node->set('field_menu_path', $values['path'] ?? '');
  $node->set('field_menu_external_url', $values['external_url'] ?? '');
  $node->set('field_menu_anchor', $values['anchor'] ?? '');
  $node->set('field_menu_target', $values['target'] ?? '_self');
  $node->set('field_menu_is_enabled', TRUE);
  $node->set('field_menu_is_button', $values['is_button'] ?? FALSE);
  $node->set('field_menu_weight', $values['weight']);
  $node->set('field_is_demo', TRUE);
  $node->set('field_demo_source', 'phase_07_verify');

  if (!empty($values['page']) && $values['page'] instanceof NodeInterface) {
    $node->set('field_menu_page', ['target_id' => $values['page']->id()]);
  }
  else {
    $node->set('field_menu_page', NULL);
  }

  if (!empty($values['parent']) && $values['parent'] instanceof NodeInterface) {
    $node->set('field_menu_parent', ['target_id' => $values['parent']->id()]);
  }
  else {
    $node->set('field_menu_parent', NULL);
  }

  $node->save();

  return $node;
}

$growbig = sp_menu_load_site('growbig');
$osseed = sp_menu_load_site('osseed');

$growbig_home = sp_menu_load_page($growbig, 'home');
$growbig_about = sp_menu_load_page($growbig, 'about');
$osseed_home = sp_menu_load_page($osseed, 'home');

$growbig_main = sp_menu_save_menu($growbig, [
  'title' => 'GrowBig Main Menu',
  'key' => 'main',
  'label' => 'Main menu',
  'weight' => 0,
]);

$osseed_main = sp_menu_save_menu($osseed, [
  'title' => 'OSSeed Main Menu',
  'key' => 'main',
  'label' => 'Main menu',
  'weight' => 0,
]);

sp_menu_save_item($growbig, $growbig_main, [
  'key' => 'home',
  'title' => 'Home',
  'link_type' => 'page',
  'page' => $growbig_home,
  'weight' => 0,
]);

sp_menu_save_item($growbig, $growbig_main, [
  'key' => 'about',
  'title' => 'About',
  'link_type' => 'page',
  'page' => $growbig_about,
  'weight' => 10,
]);

sp_menu_save_item($growbig, $growbig_main, [
  'key' => 'contact',
  'title' => 'Contact',
  'link_type' => 'path',
  'path' => '/contact',
  'weight' => 20,
  'is_button' => TRUE,
]);

sp_menu_save_item($osseed, $osseed_main, [
  'key' => 'home',
  'title' => 'Home',
  'link_type' => 'page',
  'page' => $osseed_home,
  'weight' => 0,
]);

drupal_flush_all_caches();

echo "Menu API demo data prepared.\n";
