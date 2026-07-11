<?php

declare(strict_types=1);

namespace Drupal\site_platform_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides public analytics configuration for the frontend UI.
 */
final class AnalyticsController extends ControllerBase {

  /**
   * Returns safe public analytics config.
   */
  public function analyticsConfig(): JsonResponse {
    $config = $this->config('site_platform_api.analytics');

    $env_enabled = getenv('GOOGLE_ANALYTICS_ENABLED');
    $env_measurement_id = getenv('GOOGLE_ANALYTICS_MEASUREMENT_ID');

    $measurement_id = $env_measurement_id !== FALSE
      ? trim((string) $env_measurement_id)
      : trim((string) $config->get('measurement_id'));

    $enabled = $env_enabled !== FALSE
      ? $this->parseBoolean((string) $env_enabled)
      : (bool) $config->get('enabled');

    $provider = $measurement_id !== '' ? 'google_analytics' : 'none';

    if ($measurement_id === '') {
      $enabled = FALSE;
    }

    return new JsonResponse([
      'enabled' => $enabled,
      'provider' => $provider,
      'measurementId' => $measurement_id,
    ]);
  }

  /**
   * Parses a boolean-like value.
   */
  private function parseBoolean(string $value): bool {
    return in_array(strtolower(trim($value)), [
      '1',
      'true',
      'yes',
      'on',
    ], TRUE);
  }

}
