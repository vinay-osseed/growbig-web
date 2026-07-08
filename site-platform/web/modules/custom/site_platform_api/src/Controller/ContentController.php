<?php

declare(strict_types=1);

namespace Drupal\site_platform_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\site_platform_api\SitePlatformContentListNormalizer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns reusable backend content list responses.
 */
final class ContentController extends ControllerBase {

  /**
   * Constructs a ContentController object.
   */
  public function __construct(
    private readonly SitePlatformContentListNormalizer $contentListNormalizer,
  ) {}

  /**
   * Creates the controller.
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('site_platform_api.content_list_normalizer'),
    );
  }

  /**
   * Returns reusable content by source.
   */
  public function list(string $source, Request $request): JsonResponse {
    if (!in_array($source, ['services', 'partners', 'team'], TRUE)) {
      return new JsonResponse([
        'error' => [
          'code' => 'invalid_source',
          'message' => 'Unsupported content source.',
        ],
      ], 400);
    }

    $limit = max(0, (int) $request->query->get('limit', 0));
    $featured_only = filter_var(
      $request->query->get('featuredOnly', FALSE),
      FILTER_VALIDATE_BOOLEAN
    );

    $items = $this->contentListNormalizer->loadItems($source, $limit, $featured_only);

    return new JsonResponse([
      'source' => $source,
      'limit' => $limit,
      'featuredOnly' => $featured_only,
      'count' => count($items),
      'items' => $items,
    ]);
  }

}
