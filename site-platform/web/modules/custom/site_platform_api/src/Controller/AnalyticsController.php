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
  public function config(): JsonResponse {
    $enabled = $this->getBooleanEnv('GOOGLE_ANALYTICS_ENABLED');
    $measurement_id = trim((string) getenv('GOOGLE_ANALYTICS_MEASUREMENT_ID'));

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
   * Gets a boolean environment value.
   */
  private function getBooleanEnv(string $name): bool {
    $value = getenv($name);

    if ($value === FALSE) {
      return FALSE;
    }

    return in_array(strtolower(trim((string) $value)), [
      '1',
      'true',
      'yes',
      'on',
    ], TRUE);
  }

}
