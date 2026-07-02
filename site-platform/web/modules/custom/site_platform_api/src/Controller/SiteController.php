<?php

declare(strict_types=1);

namespace Drupal\site_platform_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Returns clean frontend site API responses.
 */
final class SiteController extends ControllerBase {

  /**
   * Returns the active site profile response.
   */
  public function site(): JsonResponse {
    $profile = $this->loadDefaultSiteProfile();

    if ($profile instanceof NodeInterface) {
      return new JsonResponse($this->buildSiteProfileResponse($profile));
    }

    return new JsonResponse($this->buildFallbackResponse());
  }

  /**
   * Loads the default active Site Profile.
   */
  private function loadDefaultSiteProfile(): ?NodeInterface {
    $storage = $this->entityTypeManager()->getStorage('node');

    $ids = $storage->getQuery()
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

    $profile = $storage->load(reset($ids));

    return $profile instanceof NodeInterface ? $profile : NULL;
  }

  /**
   * Builds the frontend response from a Site Profile node.
   */
  private function buildSiteProfileResponse(NodeInterface $profile): array {
    $site_name = $profile->label();
    $short_name = $this->getStringValue($profile, 'field_site_short_name') ?: $site_name;

    return [
      'id' => $this->getStringValue($profile, 'field_site_key') ?: 'default',
      'type' => 'site',
      'name' => $site_name,
      'shortName' => $short_name,
      'domains' => [
        'primary' => $this->getLinkUri($profile, 'field_admin_domain'),
        'ui' => $this->getLinkUri($profile, 'field_ui_domain'),
        'admin' => $this->getLinkUri($profile, 'field_admin_domain'),
        'api' => $this->getLinkUri($profile, 'field_api_domain'),
      ],
      'branding' => [
        'logo' => NULL,
        'favicon' => NULL,
        'defaultImage' => NULL,
      ],
      'contact' => [
        'email' => $this->getStringValue($profile, 'field_contact_email'),
        'phone' => $this->getStringValue($profile, 'field_contact_phone'),
        'address' => $this->getStringValue($profile, 'field_contact_address'),
      ],
      'seo' => [
        'title' => $this->getStringValue($profile, 'field_default_meta_title') ?: $site_name,
        'description' => $this->getStringValue($profile, 'field_default_meta_description'),
        'image' => NULL,
      ],
      'social' => [],
      'theme' => [
        'color' => $this->getStringValue($profile, 'field_theme_color'),
      ],
      'meta' => [
        'source' => 'site_profile',
        'nodeId' => (int) $profile->id(),
        'isDefault' => $this->getBooleanValue($profile, 'field_is_default'),
        'isActive' => $this->getBooleanValue($profile, 'field_is_active'),
      ],
    ];
  }

  /**
   * Builds fallback response when no Site Profile exists.
   */
  private function buildFallbackResponse(): array {
    $site_config = $this->config('system.site');

    $admin_domain = getenv('DRUPAL_ADMIN_DOMAIN') ?: '';
    $api_domain = getenv('DRUPAL_API_DOMAIN') ?: '';
    $ui_domain = getenv('DRUPAL_UI_DOMAIN') ?: '';
    $primary_domain = getenv('DRUPAL_PRIMARY_DOMAIN') ?: '';

    $site_name = $site_config->get('name') ?: 'Site Platform';

    return [
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
      'theme' => [
        'color' => '',
      ],
      'meta' => [
        'source' => 'system.site',
        'isDefault' => TRUE,
      ],
    ];
  }

  /**
   * Gets a plain field value.
   */
  private function getStringValue(NodeInterface $node, string $field_name): string {
    if (!$node->hasField($field_name) || $node->get($field_name)->isEmpty()) {
      return '';
    }

    return (string) $node->get($field_name)->value;
  }

  /**
   * Gets a link field URI.
   */
  private function getLinkUri(NodeInterface $node, string $field_name): string {
    if (!$node->hasField($field_name) || $node->get($field_name)->isEmpty()) {
      return '';
    }

    return $this->normalizeDomain((string) $node->get($field_name)->uri);
  }

  /**
   * Gets a boolean field value.
   */
  private function getBooleanValue(NodeInterface $node, string $field_name): bool {
    if (!$node->hasField($field_name) || $node->get($field_name)->isEmpty()) {
      return FALSE;
    }

    return (bool) $node->get($field_name)->value;
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
