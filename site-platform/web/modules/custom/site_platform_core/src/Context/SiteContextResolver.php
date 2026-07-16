<?php

declare(strict_types=1);

namespace Drupal\site_platform_core\Context;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\TypedData\Plugin\DataType\ItemList;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Resolves the active Site Platform context.
 *
 * The resolver now prefers real Site Profile nodes when available.
 * Environment-domain resolution remains as a bootstrap fallback.
 */
final class SiteContextResolver implements SiteContextResolverInterface {

  /**
   * Constructs the resolver.
   */
  public function __construct(
    private readonly RequestStack $requestStack,
    private readonly LanguageManagerInterface $languageManager,
    private readonly EntityTypeManagerInterface $entityTypeManager,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function resolve(?Request $request = NULL): SiteContext {
    $request ??= $this->requestStack->getCurrentRequest();

    if ($request instanceof Request && !$this->isProduction($this->getEnvironment())) {
      $site_key = trim((string) $request->query->get('site', ''));
      if ($site_key !== '') {
        $context = $this->resolveFromSiteKey($site_key);
        if ($context->isResolved()) {
          return $context;
        }
      }
    }

    return $this->resolveFromHost($request ? $request->getHost() : '');
  }

  /**
   * {@inheritdoc}
   */
  public function resolveFromHost(string $host): SiteContext {
    $host = $this->normalizeHost($host);
    $environment = $this->getEnvironment();
    $language_id = $this->languageManager->getCurrentLanguage()->getId();

    $profile_context = $this->resolveSiteProfileFromHost($host, $environment, $language_id);
    if ($profile_context->isResolved()) {
      return $profile_context;
    }

    $map = $this->getBootstrapDomainMap();

    foreach ($map['domains'] as $type => $domains) {
      if (in_array($host, $domains, TRUE)) {
        return SiteContext::resolved(
          $map['siteKey'],
          $map['siteName'],
          $host,
          $environment,
          $type,
          $language_id,
          $map['domains'],
          ['source' => 'environment'],
        );
      }
    }

    if (!$this->isProduction($environment) && $host !== '') {
      return SiteContext::resolved(
        $map['siteKey'],
        $map['siteName'],
        $host,
        $environment,
        'local_bootstrap',
        $language_id,
        $map['domains'],
        ['source' => 'local_fallback'],
      );
    }

    return SiteContext::unresolved(
      $host,
      $environment,
      $language_id,
      [
        'knownDomains' => $map['domains'],
        'reason' => 'No active Site Profile or bootstrap domain matched this host.',
      ],
    );
  }

  /**
   * {@inheritdoc}
   */
  public function resolveFromSiteKey(string $siteKey): SiteContext {
    $site_key = trim($siteKey);
    $environment = $this->getEnvironment();
    $language_id = $this->languageManager->getCurrentLanguage()->getId();

    if ($site_key === '' || !$this->siteProfileModelExists()) {
      return SiteContext::unresolved('', $environment, $language_id, ['reason' => 'Site key is empty or Site Profile model is unavailable.']);
    }

    $storage = $this->entityTypeManager->getStorage('node');
    $query = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'site_profile')
      ->condition('status', 1)
      ->condition('field_site_key', $site_key)
      ->range(0, 1);

    if ($this->fieldExists('field_is_active')) {
      $query->condition('field_is_active', 1);
    }

    $ids = $query->execute();

    if (!$ids) {
      return SiteContext::unresolved('', $environment, $language_id, ['reason' => 'No active Site Profile matched this site key.']);
    }

    $profile = $storage->load(reset($ids));

    if (!$profile instanceof NodeInterface) {
      return SiteContext::unresolved('', $environment, $language_id, ['reason' => 'Matched Site Profile could not be loaded.']);
    }

    return $this->buildContextFromSiteProfile($profile, '', $environment, 'site_key', $language_id);
  }

  /**
   * Resolves a Site Profile node from the request host.
   */
  private function resolveSiteProfileFromHost(string $host, string $environment, string $languageId): SiteContext {
    if ($host === '' || !$this->siteProfileModelExists()) {
      return SiteContext::unresolved($host, $environment, $languageId);
    }

    foreach ($this->loadActiveSiteProfiles() as $profile) {
      $domains = $this->getSiteProfileDomains($profile);

      foreach ($domains as $type => $values) {
        if (in_array($host, $values, TRUE)) {
          return $this->buildContextFromSiteProfile($profile, $host, $environment, $type, $languageId);
        }
      }
    }

    return SiteContext::unresolved($host, $environment, $languageId);
  }

  /**
   * Builds a context from a Site Profile node.
   */
  private function buildContextFromSiteProfile(
    NodeInterface $profile,
    string $host,
    string $environment,
    string $resolvedBy,
    string $languageId,
  ): SiteContext {
    $site_key = $this->getStringFieldValue($profile, 'field_site_key') ?: 'site-' . $profile->id();
    $domains = $this->getSiteProfileDomains($profile);

    return SiteContext::resolved(
      $site_key,
      $profile->label(),
      $host,
      $environment,
      $resolvedBy,
      $languageId,
      $domains,
      ['source' => 'site_profile'],
      (int) $profile->id(),
    );
  }

  /**
   * Loads active Site Profile nodes.
   *
   * @return array<int, \Drupal\node\NodeInterface>
   *   Active Site Profile nodes.
   */
  private function loadActiveSiteProfiles(): array {
    $storage = $this->entityTypeManager->getStorage('node');
    $query = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'site_profile')
      ->condition('status', 1)
      ->sort('nid', 'ASC');

    if ($this->fieldExists('field_is_active')) {
      $query->condition('field_is_active', 1);
    }

    $ids = $query->execute();

    if (!$ids) {
      return [];
    }

    return array_filter(
      $storage->loadMultiple($ids),
      static fn($entity): bool => $entity instanceof NodeInterface,
    );
  }

  /**
   * Gets normalized domains from a Site Profile.
   *
   * @return array<string, array<int, string>>
   *   Domains grouped by resolution type.
   */
  private function getSiteProfileDomains(NodeInterface $profile): array {
    return [
      'api_domain' => $this->getNormalizedMultiFieldValues($profile, 'field_api_domains'),
      'admin_domain' => $this->getNormalizedMultiFieldValues($profile, 'field_admin_domains'),
      'frontend_domain' => $this->getNormalizedMultiFieldValues($profile, 'field_frontend_domains'),
    ];
  }

  /**
   * Gets a single string field value.
   */
  private function getStringFieldValue(NodeInterface $node, string $fieldName): string {
    if (!$node->hasField($fieldName) || $node->get($fieldName)->isEmpty()) {
      return '';
    }

    return trim((string) $node->get($fieldName)->value);
  }

  /**
   * Gets normalized multi-value string field values.
   *
   * @return array<int, string>
   *   Normalized values.
   */
  private function getNormalizedMultiFieldValues(NodeInterface $node, string $fieldName): array {
    if (!$node->hasField($fieldName) || $node->get($fieldName)->isEmpty()) {
      return [];
    }

    $values = [];
    foreach ($node->get($fieldName) as $item) {
      if ($item instanceof ItemList) {
        continue;
      }

      $value = trim((string) ($item->value ?? ''));
      if ($value !== '') {
        $values[] = $this->normalizeHost($value);
      }
    }

    return array_values(array_unique(array_filter($values)));
  }

  /**
   * Returns TRUE when the Site Profile content model exists.
   */
  private function siteProfileModelExists(): bool {
    try {
      return (bool) $this->entityTypeManager->getStorage('node_type')->load('site_profile');
    }
    catch (\Throwable) {
      return FALSE;
    }
  }

  /**
   * Returns TRUE when a Site Profile field exists.
   */
  private function fieldExists(string $fieldName): bool {
    try {
      return (bool) $this->entityTypeManager
        ->getStorage('field_config')
        ->load('node.site_profile.' . $fieldName);
    }
    catch (\Throwable) {
      return FALSE;
    }
  }

  /**
   * Builds a bootstrap domain map from environment variables.
   *
   * @return array<string, mixed>
   *   Bootstrap domain map.
   */
  private function getBootstrapDomainMap(): array {
    $site_key = $this->env('DRUPAL_SITE_KEY', 'default');
    $site_name = $this->env('DRUPAL_SITE_NAME', 'Site Platform');

    return [
      'siteKey' => $site_key,
      'siteName' => $site_name,
      'domains' => [
        'api_domain' => $this->splitDomains($this->env('DRUPAL_API_DOMAIN', '')),
        'admin_domain' => $this->splitDomains($this->env('DRUPAL_ADMIN_DOMAIN', '')),
        'ui_domain' => $this->splitDomains($this->env('DRUPAL_UI_DOMAIN', '')),
        'primary_domain' => $this->splitDomains($this->env('DRUPAL_PRIMARY_DOMAIN', '')),
        'extra_domain' => $this->splitDomains($this->env('DRUPAL_EXTRA_TRUSTED_HOSTS', '')),
      ],
    ];
  }

  /**
   * Returns an environment variable value.
   */
  private function env(string $name, string $default = ''): string {
    $value = getenv($name);

    return is_string($value) && $value !== '' ? $value : $default;
  }

  /**
   * Returns the current runtime environment.
   */
  private function getEnvironment(): string {
    return strtolower($this->env('DRUPAL_ENV', 'local'));
  }

  /**
   * Returns TRUE when the environment is production.
   */
  private function isProduction(string $environment): bool {
    return in_array($environment, ['prod', 'production'], TRUE);
  }

  /**
   * Splits a comma-separated domain list.
   *
   * @return array<int, string>
   *   Normalized domains.
   */
  private function splitDomains(string $domains): array {
    $items = array_map('trim', explode(',', $domains));
    $items = array_filter($items, static fn(string $domain): bool => $domain !== '');

    return array_values(array_unique(array_map([$this, 'normalizeHost'], $items)));
  }

  /**
   * Normalizes a host or URL string.
   */
  private function normalizeHost(string $host): string {
    $host = strtolower(trim($host));

    if ($host === '') {
      return '';
    }

    if (str_contains($host, '://')) {
      $parsed = parse_url($host, PHP_URL_HOST);
      $host = is_string($parsed) ? $parsed : $host;
    }

    $host = preg_replace('/[\/].*$/', '', $host) ?: $host;
    $host = preg_replace('/:\d+$/', '', $host) ?: $host;

    return trim($host);
  }

}
