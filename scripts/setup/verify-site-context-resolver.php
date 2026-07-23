<?php

declare(strict_types=1);

use Drupal\node\NodeInterface;

$storage = \Drupal::entityTypeManager()->getStorage('node');

/**
 * Creates or updates a Site Profile.
 */
function site_platform_save_profile(array $values): NodeInterface {
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
  $node->set('field_ga_measurement_id', $values['ga']);
  $node->save();

  return $node;
}

$growbig = site_platform_save_profile([
  'title' => 'GrowBig Technologies LLP',
  'site_key' => 'growbig',
  'is_default' => TRUE,
  'frontend_domains' => ['www.growbig-web.ddev.site', 'growbig-web.ddev.site'],
  'api_domains' => ['api.growbig-web.ddev.site'],
  'admin_domains' => ['admin.growbig-web.ddev.site'],
  'ga' => 'G-L2ML42EZZ7',
]);

$osseed = site_platform_save_profile([
  'title' => 'OSSeed Technologies LLP',
  'site_key' => 'osseed',
  'is_default' => FALSE,
  'frontend_domains' => ['www.osseed-web.ddev.site', 'osseed-web.ddev.site'],
  'api_domains' => ['api.osseed-web.ddev.site'],
  'admin_domains' => ['admin.osseed-web.ddev.site'],
  'ga' => 'G-OSSEEDTEST',
]);

\Drupal::service('cache_tags.invalidator')->invalidateTags(['node_list', 'node:' . $growbig->id(), 'node:' . $osseed->id()]);
drupal_flush_all_caches();

$resolver = \Drupal::service('site_platform_core.site_context_resolver');

$tests = [
  'api.growbig-web.ddev.site' => ['growbig', 'api_domain'],
  'admin.growbig-web.ddev.site' => ['growbig', 'admin_domain'],
  'www.growbig-web.ddev.site' => ['growbig', 'frontend_domain'],
  'api.osseed-web.ddev.site' => ['osseed', 'api_domain'],
  'admin.osseed-web.ddev.site' => ['osseed', 'admin_domain'],
  'www.osseed-web.ddev.site' => ['osseed', 'frontend_domain'],
];

foreach ($tests as $host => [$expected_key, $expected_source]) {
  $context = $resolver->resolveFromHost($host);
  $data = $context->toArray();

  if (!$context->isResolved()) {
    throw new RuntimeException("Host $host did not resolve.");
  }

  if ($context->getSiteKey() !== $expected_key) {
    throw new RuntimeException("Host $host resolved to {$context->getSiteKey()}, expected $expected_key.");
  }

  if ($context->getResolvedBy() !== $expected_source) {
    throw new RuntimeException("Host $host resolved by {$context->getResolvedBy()}, expected $expected_source.");
  }

  if (($data['debug']['source'] ?? '') !== 'site_profile') {
    throw new RuntimeException("Host $host did not resolve from site_profile.");
  }

  echo "OK: $host => {$context->getSiteKey()} via {$context->getResolvedBy()}\n";
}

$site_key_context = $resolver->resolveFromSiteKey('growbig');
if (!$site_key_context->isResolved() || $site_key_context->getSiteKey() !== 'growbig') {
  throw new RuntimeException('resolveFromSiteKey(growbig) failed.');
}

echo "OK: site key growbig => {$site_key_context->getSiteName()}\n";
echo "Site context resolver verification passed.\n";
