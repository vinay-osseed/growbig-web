<?php

declare(strict_types=1);

namespace Drupal\site_platform_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Drupal\site_platform_api\SiteResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides public analytics configuration for the frontend UI.
 */
final class AnalyticsController extends ControllerBase {

  /**
   * Constructs the analytics controller.
   */
  public function __construct(
    private readonly SiteResolver $siteResolver,
  ) {}

  /**
   * Creates the controller.
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('site_platform_api.site_resolver'),
    );
  }

  /**
   * Returns safe public analytics config for the resolved site.
   */
  public function analyticsConfig(): JsonResponse {
    $config = $this->config('site_platform_api.analytics');
    $profile = $this->siteResolver->resolve();

    $site_key = '';
    $measurement_id = '';

    if ($profile instanceof NodeInterface) {
      $site_key = $this->getFieldValue($profile, 'field_site_key');
      $measurement_id = trim($this->getFieldValue($profile, 'field_ga_measurement_id'));
    }

    if ($measurement_id === '') {
      $env_measurement_id = getenv('GOOGLE_ANALYTICS_MEASUREMENT_ID');

      if ($env_measurement_id !== FALSE) {
        $measurement_id = trim((string) $env_measurement_id);
      }
    }

    if ($measurement_id === '') {
      $measurement_id = trim((string) $config->get('measurement_id'));
    }

    $enabled = (bool) $config->get('enabled');

    if ($measurement_id === '') {
      $enabled = FALSE;
    }

    return new JsonResponse([
      'siteKey' => $site_key,
      'enabled' => $enabled,
      'provider' => $measurement_id !== '' ? 'google_analytics' : 'none',
      'measurementId' => $measurement_id,
    ]);
  }

  /**
   * Gets a field string value.
   */
  private function getFieldValue(NodeInterface $node, string $field_name): string {
    if (!$node->hasField($field_name) || $node->get($field_name)->isEmpty()) {
      return '';
    }

    return (string) $node->get($field_name)->value;
  }

}
