<?php

declare(strict_types=1);

use Drupal\node\NodeInterface;

/**
 * Creates or updates a Site Profile.
 */
function site_platform_api_save_profile(array $values): NodeInterface {
  $storage = \Drupal::entityTypeManager()->getStorage('node');
  $ids = $storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', 'site_profile')
    ->condition('field_site_key', $values['site_key'])
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
      'type' => 'site_profile',
      'title' => $values['title'],
      'status' => 1,
      'uid' => 1,
    ]);
  }

  $node->setTitle($values['title']);
  $node->set('field_site_key', $values['site_key']);
  $node->set('field_is_active', TRUE);
  $node->set('field_is_default', $values['is_default']);
  $node->set('field_default_language', 'en');
  $node->set('field_enabled_languages', [['value' => 'en']]);
  $node->set('field_timezone', 'Asia/Kolkata');
  $node->set('field_frontend_domains', array_map(static fn(string $domain): array => ['value' => $domain], $values['frontend_domains']));
  $node->set('field_api_domains', array_map(static fn(string $domain): array => ['value' => $domain], $values['api_domains']));
  $node->set('field_admin_domains', array_map(static fn(string $domain): array => ['value' => $domain], $values['admin_domains']));
  $node->save();

  return $node;
}

/**
 * Creates or updates a Site Page.
 */
function site_platform_api_save_page(NodeInterface $site, array $values): NodeInterface {
  $storage = \Drupal::entityTypeManager()->getStorage('node');
  $ids = $storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', 'site_page')
    ->condition('field_site_profile.target_id', $site->id())
    ->condition('field_page_key', $values['page_key'])
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
      'type' => 'site_page',
      'title' => $values['title'],
      'status' => 1,
      'uid' => 1,
    ]);
  }

  $node->setTitle($values['title']);
  $node->set('field_site_profile', ['target_id' => $site->id()]);
  $node->set('field_page_key', $values['page_key']);
  $node->set('field_page_slug', $values['slug']);
  $node->set('field_page_path', $values['path']);
  $node->set('field_page_template', $values['template']);
  $node->set('field_seo_title', $values['seo_title']);
  $node->set('field_seo_description', $values['seo_description']);
  $node->set('field_is_demo', TRUE);
  $node->set('field_demo_source', 'phase_05_verify');
  $node->set('field_page_weight', $values['weight']);
  $node->save();

  return $node;
}

$growbig = site_platform_api_save_profile([
  'title' => 'GrowBig Technologies LLP',
  'site_key' => 'growbig',
  'is_default' => TRUE,
  'frontend_domains' => ['www.growbig-web.ddev.site', 'growbig-web.ddev.site'],
  'api_domains' => ['api.growbig-web.ddev.site'],
  'admin_domains' => ['admin.growbig-web.ddev.site'],
]);

$osseed = site_platform_api_save_profile([
  'title' => 'OSSeed Technologies LLP',
  'site_key' => 'osseed',
  'is_default' => FALSE,
  'frontend_domains' => ['www.osseed-web.ddev.site', 'osseed-web.ddev.site'],
  'api_domains' => ['api.osseed-web.ddev.site'],
  'admin_domains' => ['admin.osseed-web.ddev.site'],
]);

site_platform_api_save_page($growbig, [
  'title' => 'GrowBig Home',
  'page_key' => 'home',
  'slug' => 'home',
  'path' => '/',
  'template' => 'landing',
  'seo_title' => 'GrowBig Technologies LLP',
  'seo_description' => 'GrowBig home page.',
  'weight' => 0,
]);

site_platform_api_save_page($growbig, [
  'title' => 'GrowBig About',
  'page_key' => 'about',
  'slug' => 'about',
  'path' => '/about',
  'template' => 'default',
  'seo_title' => 'About GrowBig',
  'seo_description' => 'About GrowBig.',
  'weight' => 10,
]);

site_platform_api_save_page($osseed, [
  'title' => 'OSSeed Home',
  'page_key' => 'home',
  'slug' => 'home',
  'path' => '/',
  'template' => 'landing',
  'seo_title' => 'OSSeed Technologies LLP',
  'seo_description' => 'OSSeed home page.',
  'weight' => 0,
]);

drupal_flush_all_caches();

echo "Basic Site Page API demo data prepared.\n";
