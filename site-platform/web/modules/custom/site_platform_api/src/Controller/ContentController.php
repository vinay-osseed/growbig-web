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
   * Supported reusable content sources.
   */
  private const SUPPORTED_SOURCES = ['services', 'partners', 'team'];

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
    if (!$this->isSupportedSource($source)) {
      return $this->invalidSourceResponse();
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

  /**
   * Returns one reusable content item by source and key.
   */
  public function detail(string $source, string $key): JsonResponse {
    if (!$this->isSupportedSource($source)) {
      return $this->invalidSourceResponse();
    }

    $items = $this->contentListNormalizer->loadItems($source);

    foreach ($items as $item) {
      if (($item['key'] ?? '') === $key) {
        return new JsonResponse([
          'source' => $source,
          'key' => $key,
          'item' => $item,
        ]);
      }
    }

    return new JsonResponse([
      'error' => [
        'code' => 'not_found',
        'message' => 'Content item not found.',
      ],
    ], 404);
  }

  /**
   * Checks if source is supported.
   */
  private function isSupportedSource(string $source): bool {
    return in_array($source, self::SUPPORTED_SOURCES, TRUE);
  }

  /**
   * Returns invalid source response.
   */
  private function invalidSourceResponse(): JsonResponse {
    return new JsonResponse([
      'error' => [
        'code' => 'invalid_source',
        'message' => 'Unsupported content source.',
      ],
    ], 400);
  }

}
