<?php

declare(strict_types=1);

namespace Drupal\site_platform_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Drupal\site_platform_api\SitePlatformPageNormalizer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Returns frontend-ready Site Page responses.
 */
final class PageController extends ControllerBase {

  /**
   * Constructs a PageController object.
   */
  public function __construct(
    private readonly SitePlatformPageNormalizer $pageNormalizer,
  ) {}

  /**
   * Creates the controller.
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('site_platform_api.page_normalizer'),
    );
  }

  /**
   * Returns a page by page key.
   */
  public function page(string $slug): JsonResponse {
    $page = $this->loadPage($slug);

    if (!$page instanceof NodeInterface) {
      return new JsonResponse([
        'error' => [
          'code' => 'not_found',
          'message' => 'Page not found.',
        ],
      ], 404);
    }

    return new JsonResponse($this->pageNormalizer->normalize($page));
  }

  /**
   * Loads a published Site Page by key.
   */
  private function loadPage(string $slug): ?NodeInterface {
    $storage = $this->entityTypeManager()->getStorage('node');

    $ids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'site_page')
      ->condition('status', 1)
      ->condition('field_page_key', $slug)
      ->range(0, 1)
      ->execute();

    if (!$ids) {
      return NULL;
    }

    $page = $storage->load(reset($ids));

    return $page instanceof NodeInterface ? $page : NULL;
  }

}
