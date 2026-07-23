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
 * Site media asset API controller.
 */
final class MediaApiController extends ControllerBase {

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
   * Returns all media assets for the resolved site.
   */
  public function media(Request $request): JsonResponse {
    $context = $this->siteContextResolver->resolve($request);

    if (!$context->isResolved() || !$context->getSiteProfileId()) {
      return $this->errorResponse(
        'site_not_resolved',
        'No active site matched this request host.',
        ['host' => $request->getHost()],
        404,
      );
    }

    $items = $this->loadMediaAssets((int) $context->getSiteProfileId());

    return $this->successResponse(array_map([$this, 'normalizeMediaAsset'], $items), $context, [
      'node_list:site_media_asset',
      'node:' . $context->getSiteProfileId(),
    ]);
  }

  /**
   * Returns one media asset by key for the resolved site.
   */
  public function mediaItem(Request $request, string $key): JsonResponse {
    $context = $this->siteContextResolver->resolve($request);
    $key = $this->normalizeKey($key);

    if (!$context->isResolved() || !$context->getSiteProfileId()) {
      return $this->errorResponse(
        'site_not_resolved',
        'No active site matched this request host.',
        ['host' => $request->getHost(), 'key' => $key],
        404,
      );
    }

    $media = $this->loadMediaAsset((int) $context->getSiteProfileId(), $key);

    if (!$media instanceof NodeInterface) {
      return $this->errorResponse(
        'media_not_found',
        'Media asset not found.',
        [
          'siteKey' => $context->getSiteKey(),
          'key' => $key,
          'language' => $context->getLanguageId(),
        ],
        404,
      );
    }

    return $this->successResponse($this->normalizeMediaAsset($media), $context, [
      'node:' . $media->id(),
      'node:' . $context->getSiteProfileId(),
    ]);
  }

  /**
   * Loads media assets for one site.
   *
   * @return array<int, \Drupal\node\NodeInterface>
   *   Media asset nodes.
   */
  private function loadMediaAssets(int $siteProfileId): array {
    $storage = $this->sitePlatformEntityTypeManager->getStorage('node');

    $query = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'site_media_asset')
      ->condition('status', 1)
      ->condition('field_site_profile.target_id', $siteProfileId)
      ->sort('field_media_weight.value', 'ASC')
      ->sort('title', 'ASC');

    if ($this->fieldExists('node.site_media_asset.field_media_is_active')) {
      $query->condition('field_media_is_active', 1);
    }

    $ids = $query->execute();

    if (!$ids) {
      return [];
    }

    return array_values(array_filter(
      $storage->loadMultiple($ids),
      static fn($entity): bool => $entity instanceof NodeInterface,
    ));
  }

  /**
   * Loads one media asset.
   */
  private function loadMediaAsset(int $siteProfileId, string $key): ?NodeInterface {
    $storage = $this->sitePlatformEntityTypeManager->getStorage('node');

    $query = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'site_media_asset')
      ->condition('status', 1)
      ->condition('field_site_profile.target_id', $siteProfileId)
      ->condition('field_media_key', $key)
      ->range(0, 1);

    if ($this->fieldExists('node.site_media_asset.field_media_is_active')) {
      $query->condition('field_media_is_active', 1);
    }

    $ids = $query->execute();

    if (!$ids) {
      return NULL;
    }

    $media = $storage->load(reset($ids));

    return $media instanceof NodeInterface ? $media : NULL;
  }

  /**
   * Normalizes one media asset.
   *
   * @return array<string, mixed>
   *   Normalized media asset data.
   */
  private function normalizeMediaAsset(NodeInterface $media): array {
    return [
      'id' => 'media-' . $media->id(),
      'type' => 'SiteMediaAsset',
      'key' => $this->fieldValue($media, 'field_media_key'),
      'title' => $media->label(),
      'kind' => $this->fieldValue($media, 'field_media_kind') ?: 'image',
      'url' => $this->fieldValue($media, 'field_media_url'),
      'alt' => $this->fieldValue($media, 'field_media_alt'),
      'credit' => $this->fieldValue($media, 'field_media_credit'),
      'weight' => (int) ($this->fieldValue($media, 'field_media_weight') ?: 0),
      'isActive' => (bool) $this->fieldValue($media, 'field_media_is_active'),
      'isDemo' => (bool) $this->fieldValue($media, 'field_is_demo'),
      'demoSource' => $this->fieldValue($media, 'field_demo_source'),
    ];
  }

  /**
   * Gets a field value safely.
   */
  private function fieldValue(NodeInterface $node, string $fieldName): mixed {
    if (!$node->hasField($fieldName) || $node->get($fieldName)->isEmpty()) {
      return '';
    }

    return $node->get($fieldName)->value ?? '';
  }

  /**
   * Returns TRUE when a field config exists.
   */
  private function fieldExists(string $fieldId): bool {
    try {
      return (bool) $this->sitePlatformEntityTypeManager
        ->getStorage('field_config')
        ->load($fieldId);
    }
    catch (\Throwable) {
      return FALSE;
    }
  }

  /**
   * Normalizes a machine key.
   */
  private function normalizeKey(string $key): string {
    $key = strtolower(trim($key));
    $key = preg_replace('/[^a-z0-9_-]+/', '-', $key) ?: $key;

    return trim($key, '-');
  }

  /**
   * Builds a success response.
   *
   * @param mixed $data
   *   Response data.
   * @param array<int, string> $cacheTags
   *   Cache tags.
   */
  private function successResponse(mixed $data, SiteContext $context, array $cacheTags): JsonResponse {
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
