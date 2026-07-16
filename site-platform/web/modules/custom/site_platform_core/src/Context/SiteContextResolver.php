<?php

declare(strict_types=1);

namespace Drupal\site_platform_core\Context;

use Drupal\Core\Language\LanguageManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Bootstrap site context resolver.
 *
 * This resolver intentionally uses only environment/domain values.
 * Later phases will replace this with Site Profile entity lookup.
 */
final class SiteContextResolver implements SiteContextResolverInterface {

  /**
   * Constructs the resolver.
   */
  public function __construct(
    private readonly RequestStack $requestStack,
    private readonly LanguageManagerInterface $languageManager,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function resolve(?Request $request = NULL): SiteContext {
    $request ??= $this->requestStack->getCurrentRequest();

    return $this->resolveFromHost($request ? $request->getHost() : '');
  }

  /**
   * {@inheritdoc}
   */
  public function resolveFromHost(string $host): SiteContext {
    $host = $this->normalizeHost($host);
    $environment = $this->getEnvironment();
    $language_id = $this->languageManager->getCurrentLanguage()->getId();
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
        'reason' => 'No domain matched this request host.',
      ],
    );
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
