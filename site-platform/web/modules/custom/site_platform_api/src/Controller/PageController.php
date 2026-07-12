<?php

declare(strict_types=1);

namespace Drupal\site_platform_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\site_platform_api\SitePlatformPageNormalizer;
use Drupal\site_platform_api\SiteResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Returns normalized site page responses.
 */
final class PageController extends ControllerBase {

  /**
   * Constructs a PageController object.
   */
  public function __construct(
    private readonly SitePlatformPageNormalizer $pageNormalizer,
    private readonly EntityTypeManagerInterface $apiEntityTypeManager,
    private readonly SiteResolver $siteResolver,
  ) {}

  /**
   * Creates the controller.
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('site_platform_api.page_normalizer'),
      $container->get('entity_type.manager'),
      $container->get('site_platform_api.site_resolver'),
    );
  }

  /**
   * Returns a list of published dynamic pages.
   */
  public function index(): JsonResponse {
    $storage = $this->apiEntityTypeManager->getStorage('node');

    $query = $storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'site_page')
      ->condition('status', NodeInterface::PUBLISHED);

    $this->siteResolver->applyCurrentSiteFilter($query, 'site_page');

    $node_ids = $query
      ->sort('title', 'ASC')
      ->execute();

    $items = [];

    foreach ($storage->loadMultiple($node_ids) as $node) {
      if (!$node instanceof NodeInterface) {
        continue;
      }

      $slug = $this->getFieldValue($node, 'field_page_key');
      $route_path = $slug === 'home' ? '/' : '/' . trim($slug, '/');

      $items[] = [
        'id' => (int) $node->id(),
        'uuid' => $node->uuid(),
        'title' => $node->label(),
        'slug' => $slug,
        'pageType' => $this->getFieldValue($node, 'field_page_type'),
        'route' => [
          'path' => $route_path,
          'apiPath' => '/api/v1/pages/' . $slug,
        ],
        'api' => [
          'self' => '/api/v1/pages/' . $slug,
        ],
      ];
    }

    return new JsonResponse([
      'contractVersion' => '1.0',
      'count' => count($items),
      'items' => array_values($items),
    ]);
  }

  /**
   * Returns one published page by slug.
   */
  public function page(string $slug): JsonResponse {
    $storage = $this->apiEntityTypeManager->getStorage('node');

    $query = $storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'site_page')
      ->condition('status', NodeInterface::PUBLISHED)
      ->condition('field_page_key', $slug);

    $this->siteResolver->applyCurrentSiteFilter($query, 'site_page');

    $node_ids = $query
      ->range(0, 1)
      ->execute();

    if (!$node_ids) {
      return new JsonResponse([
        'error' => [
          'code' => 'not_found',
          'message' => 'Page not found.',
        ],
      ], 404);
    }

    $page = $storage->load(reset($node_ids));

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
   * Gets a plain field value.
   */
  private function getFieldValue(NodeInterface $node, string $field_name): string {
    if (!$node->hasField($field_name) || $node->get($field_name)->isEmpty()) {
      return '';
    }

    return (string) $node->get($field_name)->value;
  }

}
