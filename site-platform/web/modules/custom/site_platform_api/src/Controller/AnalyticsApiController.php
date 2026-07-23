<?php

declare(strict_types=1);

namespace Drupal\site_platform_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\site_platform_core\Context\SiteContext;
use Drupal\site_platform_core\Context\SiteContextResolverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Site analytics API controller.
 */
final class AnalyticsApiController extends ControllerBase {

  /**
   * Constructs the controller.
   */
  public function __construct(
    private readonly SiteContextResolverInterface $siteContextResolver,
    private readonly EntityTypeManagerInterface $sitePlatformEntityTypeManager,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('site_platform_core.site_context_resolver'),
      $container->get('entity_type.manager'),
    );
  }

  /**
   * Returns analytics config for the resolved site.
   */
  public function analytics(Request $request): JsonResponse {
    $context = $this->siteContextResolver->resolve($request);

    if (!$context->isResolved() || !$context->getSiteProfileId()) {
      return $this->errorResponse(
        'site_not_resolved',
        'No active site matched this request host.',
        ['host' => $request->getHost()],
        404,
      );
    }

    $site = $this->loadSiteProfile((int) $context->getSiteProfileId());
    if (!$site instanceof NodeInterface) {
      return $this->errorResponse(
        'site_not_found',
        'Resolved Site Profile could not be loaded.',
        ['siteProfileId' => $context->getSiteProfileId()],
        404,
      );
    }

    $ga = $this->fieldValue($site, 'field_ga_measurement_id');
    $gtm = $this->fieldValue($site, 'field_gtm_container_id');

    return $this->successResponse([
      'siteKey' => $context->getSiteKey(),
      'enabled' => $ga !== '' || $gtm !== '',
      'gaMeasurementId' => $ga,
      'gtmContainerId' => $gtm,
      'loadMode' => 'frontend_controlled',
    ], $context, ['node:' . $site->id()]);
  }

  /**
   * Loads a Site Profile node.
   */
  private function loadSiteProfile(int $siteProfileId): ?NodeInterface {
    $site = $this->sitePlatformEntityTypeManager->getStorage('node')->load($siteProfileId);
    return $site instanceof NodeInterface ? $site : NULL;
  }

  /**
   * Gets a field value safely.
   */
  private function fieldValue(NodeInterface $node, string $fieldName): string {
    if (!$node->hasField($fieldName) || $node->get($fieldName)->isEmpty()) {
      return '';
    }

    return trim((string) ($node->get($fieldName)->value ?? ''));
  }

  /**
   * Builds a success response.
   *
   * @param array<string, mixed> $data
   *   Response data.
   * @param array<int, string> $cacheTags
   *   Cache tags.
   */
  private function successResponse(array $data, SiteContext $context, array $cacheTags): JsonResponse {
    return new JsonResponse([
      'data' => $data,
      'meta' => [
        'siteKey' => $context->getSiteKey(),
        'language' => $context->getLanguageId(),
        'resolvedBy' => $context->getResolvedBy(),
        'generatedAt' => gmdate('c'),
      ],
      'cache' => [
        'maxAge' => 300,
        'tags' => $cacheTags,
        'contexts' => ['url.site', 'headers:host'],
      ],
    ]);
  }

  /**
   * Builds an error response.
   *
   * @param array<string, mixed> $details
   *   Error details.
   */
  private function errorResponse(string $code, string $message, array $details, int $status): JsonResponse {
    return new JsonResponse([
      'error' => [
        'code' => $code,
        'message' => $message,
        'details' => $details,
      ],
    ], $status);
  }

}
