<?php

declare(strict_types=1);

namespace Drupal\site_platform_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\site_platform_api\SitePlatformContentListNormalizer;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns reusable backend content responses.
 */
final class ContentController extends ControllerBase {

  /**
   * Supported reusable content sources.
   */
  private const SOURCE_MAP = [
    'services' => [
      'bundle' => 'service',
      'key_field' => 'field_service_key',
    ],
    'partners' => [
      'bundle' => 'partner',
      'key_field' => 'field_partner_key',
    ],
    'team' => [
      'bundle' => 'team_member',
      'key_field' => 'field_member_key',
    ],
  ];

  /**
   * Constructs a ContentController object.
   */
  public function __construct(
    private readonly SitePlatformContentListNormalizer $contentListNormalizer,
    private readonly EntityTypeManagerInterface $apiEntityTypeManager,
  ) {}

  /**
   * Creates the controller.
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('site_platform_api.content_list_normalizer'),
      $container->get('entity_type.manager'),
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

    $node = $this->loadNodeBySourceAndKey($source, $key);

    if (!$node instanceof NodeInterface) {
      return new JsonResponse([
        'error' => [
          'code' => 'not_found',
          'message' => 'Content item not found.',
        ],
      ], 404);
    }

    $items = $this->contentListNormalizer->loadItems($source, 0, FALSE, [(int) $node->id()]);
    $item = reset($items);

    return new JsonResponse([
      'source' => $source,
      'key' => $key,
      'item' => $item ?: NULL,
    ]);
  }

  /**
   * Loads one node by source and key.
   */
  private function loadNodeBySourceAndKey(string $source, string $key): ?NodeInterface {
    $definition = self::SOURCE_MAP[$source];

    $node_ids = $this->apiEntityTypeManager
      ->getStorage('node')
      ->getQuery()
      ->accessCheck(TRUE)
      ->condition('status', NodeInterface::PUBLISHED)
      ->condition('type', $definition['bundle'])
      ->condition($definition['key_field'], $key)
      ->range(0, 1)
      ->execute();

    if (!$node_ids) {
      return NULL;
    }

    $node = $this->apiEntityTypeManager
      ->getStorage('node')
      ->load(reset($node_ids));

    return $node instanceof NodeInterface ? $node : NULL;
  }

  /**
   * Checks if source is supported.
   */
  private function isSupportedSource(string $source): bool {
    return isset(self::SOURCE_MAP[$source]);
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
