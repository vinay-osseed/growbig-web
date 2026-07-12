<?php

declare(strict_types=1);

namespace Drupal\site_platform_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Site\Settings;
use Drupal\node\NodeInterface;
use Drupal\site_platform_api\SitePlatformMediaNormalizer;
use Drupal\site_platform_api\SiteResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Returns frontend-ready site profile responses.
 */
final class SiteController extends ControllerBase {

  /**
   * Constructs a SiteController object.
   */
  public function __construct(
    private readonly SitePlatformMediaNormalizer $mediaNormalizer,
    private readonly SiteResolver $siteResolver,
  ) {}

  /**
   * Creates the controller.
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('site_platform_api.media_normalizer'),
      $container->get('site_platform_api.site_resolver'),
    );
  }

  /**
   * Returns active site profile data.
   */
  public function site(): JsonResponse {
    $profile = $this->siteResolver->resolve();

    if ($profile instanceof NodeInterface) {
      return new JsonResponse($this->buildSiteProfileResponse($profile));
    }

    return new JsonResponse($this->buildFallbackResponse());
  }

  /**
   * Builds response from Site Profile content.
   */
  private function buildSiteProfileResponse(NodeInterface $profile): array {
    $site_name = $profile->label();
    $short_name = $this->getFieldValue($profile, 'field_site_short_name') ?: $site_name;

    return [
      'id' => $this->getFieldValue($profile, 'field_site_key'),
      'type' => 'site',
      'name' => $site_name,
      'shortName' => $short_name,
      'tagline' => $this->getFieldValue($profile, 'field_tagline'),
      'description' => $this->getFieldValue($profile, 'field_description'),
      'domains' => [
        'primary' => $this->getLinkUri($profile, 'field_primary_domain'),
        'ui' => $this->getLinkUri($profile, 'field_ui_domain'),
        'admin' => $this->getLinkUri($profile, 'field_admin_domain'),
        'api' => $this->getLinkUri($profile, 'field_api_domain'),
        'website' => $this->getLinkUri($profile, 'field_website'),
      ],
      'branding' => [
        'logo' => $this->normalizeMediaField($profile, 'field_logo'),
        'favicon' => $this->normalizeMediaField($profile, 'field_favicon'),
        'defaultImage' => $this->normalizeMediaField($profile, 'field_default_social_image'),
      ],
      'contact' => [
        'email' => $this->getFieldValue($profile, 'field_contact_email'),
        'phone' => $this->getFieldValue($profile, 'field_contact_phone'),
        'alternatePhone' => $this->getFieldValue($profile, 'field_alternate_phone'),
        'address' => $this->getFieldValue($profile, 'field_contact_address'),
        'city' => $this->getFieldValue($profile, 'field_city'),
        'state' => $this->getFieldValue($profile, 'field_state'),
        'country' => $this->getFieldValue($profile, 'field_country'),
        'postalCode' => $this->getFieldValue($profile, 'field_postal_code'),
        'workingHours' => $this->getFieldValue($profile, 'field_working_hours'),
        'map' => [
          'url' => $this->getLinkUri($profile, 'field_google_map_link'),
          'latitude' => $this->getDecimalFieldValue($profile, 'field_map_latitude'),
          'longitude' => $this->getDecimalFieldValue($profile, 'field_map_longitude'),
        ],
      ],
      'seo' => [
        'title' => $this->getFieldValue($profile, 'field_default_meta_title') ?: $site_name,
        'description' => $this->getFieldValue($profile, 'field_default_meta_description'),
        'image' => $this->normalizeMediaField($profile, 'field_default_social_image'),
      ],
      'social' => $this->normalizeSocialLinks($profile),
      'theme' => [
        'color' => $this->getFieldValue($profile, 'field_theme_color'),
      ],
      'footer' => [
        'description' => $this->getFieldValue($profile, 'field_footer_description'),
        'copyright' => $this->getFieldValue($profile, 'field_footer_copyright'),
        'menus' => [
          'main' => $this->getFieldValue($profile, 'field_main_menu'),
          'quickLinks' => $this->getFieldValue($profile, 'field_footer_quick_links_menu'),
          'services' => $this->getFieldValue($profile, 'field_footer_services_menu'),
        ],
      ],
      'meta' => [
        'source' => 'site_profile',
        'nodeId' => (int) $profile->id(),
        'isDefault' => (bool) $this->getFieldValue($profile, 'field_is_default'),
        'isActive' => (bool) $this->getFieldValue($profile, 'field_is_active'),
      ],
    ];
  }

  /**
   * Builds fallback response when Site Profile content is missing.
   */
  private function buildFallbackResponse(): array {
    $site_name = (string) $this->config('system.site')->get('name');

    $primary_domain = getenv('DRUPAL_PRIMARY_DOMAIN') ?: Settings::get('trusted_host_patterns')[0] ?? '';
    $admin_domain = getenv('DRUPAL_ADMIN_DOMAIN') ?: '';
    $api_domain = getenv('DRUPAL_API_DOMAIN') ?: '';
    $ui_domain = getenv('DRUPAL_UI_DOMAIN') ?: '';

    return [
      'id' => 'default',
      'type' => 'site',
      'name' => $site_name,
      'shortName' => $site_name,
      'tagline' => '',
      'description' => '',
      'domains' => [
        'primary' => $this->normalizeDomain($primary_domain),
        'ui' => $this->normalizeDomain($ui_domain),
        'admin' => $this->normalizeDomain($admin_domain),
        'api' => $this->normalizeDomain($api_domain),
        'website' => $this->normalizeDomain($primary_domain),
      ],
      'branding' => [
        'logo' => NULL,
        'favicon' => NULL,
        'defaultImage' => NULL,
      ],
      'contact' => [
        'email' => '',
        'phone' => '',
        'alternatePhone' => '',
        'address' => '',
        'city' => '',
        'state' => '',
        'country' => '',
        'postalCode' => '',
        'workingHours' => '',
        'map' => [
          'url' => '',
          'latitude' => NULL,
          'longitude' => NULL,
        ],
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
      'footer' => [
        'description' => '',
        'copyright' => '',
        'menus' => [
          'main' => '',
          'quickLinks' => '',
          'services' => '',
        ],
      ],
      'meta' => [
        'source' => 'fallback',
        'nodeId' => NULL,
        'isDefault' => FALSE,
        'isActive' => FALSE,
      ],
    ];
  }

  /**
   * Gets a plain field value.
   */
  private function getFieldValue(NodeInterface $node, string $field_name): string {
    if (!$node->hasField($field_name) || $node->get($field_name)->isEmpty()) {
      return '';
    }

    return (string) $node->get($field_name)->value;
  }

  /**
   * Gets a decimal field value.
   */
  private function getDecimalFieldValue(NodeInterface $node, string $field_name): ?float {
    if (!$node->hasField($field_name) || $node->get($field_name)->isEmpty()) {
      return NULL;
    }

    return (float) $node->get($field_name)->value;
  }

  /**
   * Gets a link URI field value.
   */
  private function getLinkUri(NodeInterface $node, string $field_name): string {
    if (!$node->hasField($field_name) || $node->get($field_name)->isEmpty()) {
      return '';
    }

    return (string) $node->get($field_name)->uri;
  }

  /**
   * Normalizes media referenced by a field.
   */
  private function normalizeMediaField(NodeInterface $node, string $field_name): ?array {
    if (!$node->hasField($field_name) || $node->get($field_name)->isEmpty()) {
      return NULL;
    }

    $media = $node->get($field_name)->entity;

    if (!$media) {
      return NULL;
    }

    return $this->mediaNormalizer->normalize($media);
  }

  /**
   * Normalizes social links.
   */
  private function normalizeSocialLinks(NodeInterface $profile): array {
    if (!$profile->hasField('field_social_links') || $profile->get('field_social_links')->isEmpty()) {
      return [];
    }

    $items = [];

    foreach ($profile->get('field_social_links')->referencedEntities() as $paragraph) {
      if ($paragraph->hasField('field_is_active') && !$paragraph->get('field_is_active')->value) {
        continue;
      }

      $items[] = [
        'platform' => $paragraph->hasField('field_platform_name') ? (string) $paragraph->get('field_platform_name')->value : '',
        'url' => $paragraph->hasField('field_profile_url') && !$paragraph->get('field_profile_url')->isEmpty()
          ? (string) $paragraph->get('field_profile_url')->uri
          : '',
        'icon' => $paragraph->hasField('field_icon') ? (string) $paragraph->get('field_icon')->value : '',
        'order' => $paragraph->hasField('field_display_order') ? (int) $paragraph->get('field_display_order')->value : 0,
      ];
    }

    usort($items, static fn(array $a, array $b): int => $a['order'] <=> $b['order']);

    return $items;
  }

  /**
   * Normalizes a bare host/domain value into a URL.
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
