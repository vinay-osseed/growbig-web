<?php

declare(strict_types=1);

namespace Drupal\site_platform_core\Context;

/**
 * Immutable site context value object.
 */
final class SiteContext {

  /**
   * Constructs a site context.
   *
   * @param bool $resolved
   *   TRUE when a site was resolved.
   * @param string $siteKey
   *   Stable site key.
   * @param string $siteName
   *   Human-readable site name.
   * @param string $host
   *   Request host used for resolution.
   * @param string $environment
   *   Runtime environment.
   * @param string $resolvedBy
   *   Resolution source.
   * @param string $languageId
   *   Current language ID.
   * @param array<string, array<int, string>> $domains
   *   Known domains grouped by type.
   * @param array<string, mixed> $debug
   *   Optional debug data.
   */
  public function __construct(
    private readonly bool $resolved,
    private readonly string $siteKey,
    private readonly string $siteName,
    private readonly string $host,
    private readonly string $environment,
    private readonly string $resolvedBy,
    private readonly string $languageId,
    private readonly array $domains = [],
    private readonly array $debug = [],
  ) {
  }

  /**
   * Creates a resolved context.
   *
   * @param array<string, array<int, string>> $domains
   *   Known domains grouped by type.
   * @param array<string, mixed> $debug
   *   Optional debug data.
   */
  public static function resolved(
    string $siteKey,
    string $siteName,
    string $host,
    string $environment,
    string $resolvedBy,
    string $languageId,
    array $domains = [],
    array $debug = [],
  ): self {
    return new self(TRUE, $siteKey, $siteName, $host, $environment, $resolvedBy, $languageId, $domains, $debug);
  }

  /**
   * Creates an unresolved context.
   *
   * @param array<string, mixed> $debug
   *   Optional debug data.
   */
  public static function unresolved(string $host, string $environment, string $languageId, array $debug = []): self {
    return new self(FALSE, '', '', $host, $environment, 'unresolved', $languageId, [], $debug);
  }

  /**
   * Returns TRUE when a site was resolved.
   */
  public function isResolved(): bool {
    return $this->resolved;
  }

  /**
   * Returns the stable site key.
   */
  public function getSiteKey(): string {
    return $this->siteKey;
  }

  /**
   * Returns the site name.
   */
  public function getSiteName(): string {
    return $this->siteName;
  }

  /**
   * Returns the request host.
   */
  public function getHost(): string {
    return $this->host;
  }

  /**
   * Returns the environment.
   */
  public function getEnvironment(): string {
    return $this->environment;
  }

  /**
   * Returns the resolution source.
   */
  public function getResolvedBy(): string {
    return $this->resolvedBy;
  }

  /**
   * Returns the language ID.
   */
  public function getLanguageId(): string {
    return $this->languageId;
  }

  /**
   * Returns known domains grouped by type.
   *
   * @return array<string, array<int, string>>
   *   Known domains grouped by type.
   */
  public function getDomains(): array {
    return $this->domains;
  }

  /**
   * Returns optional debug data.
   *
   * @return array<string, mixed>
   *   Debug data.
   */
  public function getDebug(): array {
    return $this->debug;
  }

  /**
   * Returns an API-safe array representation.
   *
   * @return array<string, mixed>
   *   Context array.
   */
  public function toArray(): array {
    return [
      'resolved' => $this->resolved,
      'siteKey' => $this->siteKey,
      'siteName' => $this->siteName,
      'host' => $this->host,
      'environment' => $this->environment,
      'resolvedBy' => $this->resolvedBy,
      'language' => $this->languageId,
      'domains' => $this->domains,
      'debug' => $this->debug,
    ];
  }

}
