<?php

declare(strict_types=1);

namespace Drupal\site_platform_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Returns clean frontend site API responses.
 */
final class SiteController extends ControllerBase {

  /**
   * Returns the active site profile response.
   */
  public function site(): JsonResponse {
    $site_config = $this->config('system.site');

    $admin_domain = getenv('DRUPAL_ADMIN_DOMAIN') ?: '';
    $api_domain = getenv('DRUPAL_API_DOMAIN') ?: '';
    $ui_domain = getenv('DRUPAL_UI_DOMAIN') ?: '';
    $primary_domain = getenv('DRUPAL_PRIMARY_DOMAIN') ?: '';

    $site_name = $site_config->get('name') ?: 'Site Platform';

    $data = [
      'id' => 'default',
      'type' => 'site',
      'name' => $site_name,
      'shortName' => $site_name,
      'domains' => [
        'primary' => $this->normalizeDomain($primary_domain),
        'ui' => $this->normalizeDomain($ui_domain),
        'admin' => $this->normalizeDomain($admin_domain),
        'api' => $this->normalizeDomain($api_domain),
      ],
      'branding' => [
        'logo' => NULL,
        'favicon' => NULL,
        'defaultImage' => NULL,
      ],
      'contact' => [
        'email' => $site_config->get('mail') ?: '',
        'phone' => '',
        'address' => '',
      ],
      'seo' => [
        'title' => $site_name,
        'description' => '',
        'image' => NULL,
      ],
      'social' => [],
      'meta' => [
        'source' => 'system.site',
        'isDefault' => TRUE,
      ],
    ];

    return new JsonResponse($data);
  }

  /**
   * Normalizes a domain value to an absolute HTTPS URL.
   */
  private function normalizeDomain(string $domain): string {
    if ($domain === '') {
      return '';
    }

    if (str_starts_with($domain, 'http://') || str_starts_with($domain, 'https://')) {
      return $domain;
    }

    return 'https://' . $domain;
  }

}
