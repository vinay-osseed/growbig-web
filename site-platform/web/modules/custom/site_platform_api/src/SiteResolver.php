<?php

declare(strict_types=1);

namespace Drupal\site_platform_api;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Resolves the current public site profile from host/domain.
 */
final class SiteResolver {

  /**
   * Constructs the site resolver.
   */
  public function __construct(
    private readonly RequestStack $requestStack,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly EntityFieldManagerInterface $entityFieldManager,
  ) {}

  /**
   * Resolves the current active Site Profile.
   */
  public function resolve(): ?NodeInterface {
    $request = $this->requestStack->getCurrentRequest();

    if ($request instanceof Request && $this->isLocalDevRequest($request)) {
      $site_key = trim((string) $request->query->get('site', ''));
      if ($site_key !== '') {
        $profile = $this->loadActiveProfileByKey($site_key);
        if ($profile instanceof NodeInterface) {
          return $profile;
        }
      }
    }

    if ($request instanceof Request) {
      $profile = $this->loadActiveProfileByHost($request->getHost());
      if ($profile instanceof NodeInterface) {
        return $profile;
      }
    }

    return $this->loadDefaultActiveProfile();
  }

  /**
   * Gets the resolved site key.
   */
  public function getSiteKey(): string {
    $profile = $this->resolve();

    if (!$profile instanceof NodeInterface) {
      return '';
    }

    return $this->getFieldValue($profile, 'field_site_key');
  }

  /**
   * Checks whether a node is assigned to the current site.
   */
  public function nodeBelongsToCurrentSite(NodeInterface $node): bool {
    $profile = $this->resolve();

    if (!$profile instanceof NodeInterface) {
      return FALSE;
    }

    return $this->nodeBelongsToSite($node, $profile);
  }

  /**
   * Checks whether a node is assigned to a specific site.
   */
  public function nodeBelongsToSite(NodeInterface $node, NodeInterface $profile): bool {
    if (!$node->hasField('field_sites')) {
      return $this->isDefaultProfile($profile);
    }

    if ($node->get('field_sites')->isEmpty()) {
      return $this->isDefaultProfile($profile);
    }

    foreach ($node->get('field_sites')->referencedEntities() as $site) {
      if ($site instanceof NodeInterface && (int) $site->id() === (int) $profile->id()) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Applies site filtering to a node query when the bundle has field_sites.
   */
  public function applyCurrentSiteFilter(object $query, string $bundle): void {
    $profile = $this->resolve();

    if (!$profile instanceof NodeInterface) {
      $query->condition('nid', 0);
      return;
    }

    if ($this->bundleHasFieldSites($bundle)) {
      $query->condition('field_sites.target_id', (int) $profile->id());
    }
  }

  /**
   * Loads one active profile by site key.
   */
  private function loadActiveProfileByKey(string $site_key): ?NodeInterface {
    $ids = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'site_profile')
      ->condition('status', 1)
      ->condition('field_is_active', 1)
      ->condition('field_site_key', $site_key)
      ->range(0, 1)
      ->execute();

    if (!$ids) {
      return NULL;
    }

    $node = $this->entityTypeManager->getStorage('node')->load(reset($ids));

    return $node instanceof NodeInterface ? $node : NULL;
  }

  /**
   * Loads one active profile by request host.
   */
  private function loadActiveProfileByHost(string $host): ?NodeInterface {
    $host = $this->normalizeHost($host);

    if ($host === '') {
      return NULL;
    }

    $profiles = $this->entityTypeManager
      ->getStorage('node')
      ->loadByProperties([
        'type' => 'site_profile',
        'status' => 1,
        'field_is_active' => 1,
      ]);

    foreach ($profiles as $profile) {
      if (!$profile instanceof NodeInterface) {
        continue;
      }

      foreach ([
        'field_primary_domain',
        'field_ui_domain',
        'field_admin_domain',
        'field_api_domain',
        'field_website',
      ] as $field_name) {
        $domain_host = $this->normalizeHost($this->getLinkUri($profile, $field_name));

        if ($domain_host !== '' && $domain_host === $host) {
          return $profile;
        }
      }
    }

    return NULL;
  }

  /**
   * Loads the default active site profile.
   */
  private function loadDefaultActiveProfile(): ?NodeInterface {
    $ids = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'site_profile')
      ->condition('status', 1)
      ->condition('field_is_active', 1)
      ->condition('field_is_default', 1)
      ->sort('changed', 'DESC')
      ->range(0, 1)
      ->execute();

    if (!$ids) {
      return NULL;
    }

    $node = $this->entityTypeManager->getStorage('node')->load(reset($ids));

    return $node instanceof NodeInterface ? $node : NULL;
  }

  /**
   * Determines whether ?site override is allowed.
   */
  private function isLocalDevRequest(Request $request): bool {
    $host = $this->normalizeHost($request->getHost());

    return $host === 'localhost'
      || $host === '127.0.0.1'
      || $host === '::1'
      || str_ends_with($host, '.ddev.site');
  }

  /**
   * Checks whether a bundle has the shared field_sites field.
   */
  private function bundleHasFieldSites(string $bundle): bool {
    $definitions = $this->entityFieldManager
      ->getFieldDefinitions('node', $bundle);

    return isset($definitions['field_sites']);
  }

  /**
   * Checks whether the profile is the default site.
   */
  private function isDefaultProfile(NodeInterface $profile): bool {
    return $profile->hasField('field_is_default')
      && !$profile->get('field_is_default')->isEmpty()
      && (bool) $profile->get('field_is_default')->value;
  }

  /**
   * Gets string field value.
   */
  private function getFieldValue(NodeInterface $node, string $field_name): string {
    if (!$node->hasField($field_name) || $node->get($field_name)->isEmpty()) {
      return '';
    }

    return trim((string) $node->get($field_name)->value);
  }

  /**
   * Gets link URI field value.
   */
  private function getLinkUri(NodeInterface $node, string $field_name): string {
    if (!$node->hasField($field_name) || $node->get($field_name)->isEmpty()) {
      return '';
    }

    return trim((string) $node->get($field_name)->uri);
  }

  /**
   * Normalizes URL/domain/host to host only.
   */
  private function normalizeHost(string $value): string {
    $value = strtolower(trim($value));

    if ($value === '') {
      return '';
    }

    if (str_starts_with($value, 'internal:')) {
      return '';
    }

    if (!str_contains($value, '://')) {
      $value = 'https://' . $value;
    }

    $host = parse_url($value, PHP_URL_HOST);

    if (!is_string($host)) {
      return '';
    }

    return preg_replace('/^www\./', '', strtolower($host)) ?: '';
  }

}
