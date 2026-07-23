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
 * Reusable content API controller.
 */
final class ContentApiController extends ControllerBase {

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
   * Returns reusable content blocks, optionally filtered by source.
   */
  public function content(Request $request, string $source = ''): JsonResponse {
    $context = $this->siteContextResolver->resolve($request);
    $source = $this->normalizeKey($source);

    if (!$context->isResolved() || !$context->getSiteProfileId()) {
      return $this->errorResponse(
        'site_not_resolved',
        'No active site matched this request host.',
        ['host' => $request->getHost(), 'source' => $source],
        404,
      );
    }

    $items = $this->loadContentBlocks((int) $context->getSiteProfileId(), $source);

    return $this->successResponse(array_map([$this, 'normalizeContentBlock'], $items), $context, [
      'node_list:site_content_block',
      'node:' . $context->getSiteProfileId(),
    ]);
  }

  /**
   * Returns one reusable content block by source and key.
   */
  public function contentItem(Request $request, string $source, string $key): JsonResponse {
    $context = $this->siteContextResolver->resolve($request);
    $source = $this->normalizeKey($source);
    $key = $this->normalizeKey($key);

    if (!$context->isResolved() || !$context->getSiteProfileId()) {
      return $this->errorResponse(
        'site_not_resolved',
        'No active site matched this request host.',
        ['host' => $request->getHost(), 'source' => $source, 'key' => $key],
        404,
      );
    }

    $content = $this->loadContentBlock((int) $context->getSiteProfileId(), $source, $key);

    if (!$content instanceof NodeInterface) {
      return $this->errorResponse(
        'content_not_found',
        'Content not found.',
        [
          'siteKey' => $context->getSiteKey(),
          'source' => $source,
          'key' => $key,
          'language' => $context->getLanguageId(),
        ],
        404,
      );
    }

    return $this->successResponse($this->normalizeContentBlock($content), $context, [
      'node:' . $content->id(),
      'node:' . $context->getSiteProfileId(),
    ]);
  }

  /**
   * Loads reusable content blocks.
   *
   * @return array<int, \Drupal\node\NodeInterface>
   *   Content block nodes.
   */
  private function loadContentBlocks(int $siteProfileId, string $source = ''): array {
    $storage = $this->sitePlatformEntityTypeManager->getStorage('node');

    $query = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'site_content_block')
      ->condition('status', 1)
      ->condition('field_site_profile.target_id', $siteProfileId)
      ->sort('field_content_weight.value', 'ASC')
      ->sort('title', 'ASC');

    if ($source !== '') {
      $query->condition('field_content_source', $source);
    }

    if ($this->fieldExists('node.site_content_block.field_content_is_active')) {
      $query->condition('field_content_is_active', 1);
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
   * Loads one reusable content block.
   */
  private function loadContentBlock(int $siteProfileId, string $source, string $key): ?NodeInterface {
    $storage = $this->sitePlatformEntityTypeManager->getStorage('node');

    $query = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'site_content_block')
      ->condition('status', 1)
      ->condition('field_site_profile.target_id', $siteProfileId)
      ->condition('field_content_source', $source)
      ->condition('field_content_key', $key)
      ->range(0, 1);

    if ($this->fieldExists('node.site_content_block.field_content_is_active')) {
      $query->condition('field_content_is_active', 1);
    }

    $ids = $query->execute();

    if (!$ids) {
      return NULL;
    }

    $content = $storage->load(reset($ids));

    return $content instanceof NodeInterface ? $content : NULL;
  }

  /**
   * Normalizes one reusable content block.
   *
   * @return array<string, mixed>
   *   Normalized content block data.
   */
  private function normalizeContentBlock(NodeInterface $content): array {
    return [
      'id' => 'content-' . $content->id(),
      'type' => 'SiteContentBlock',
      'source' => $this->fieldValue($content, 'field_content_source'),
      'key' => $this->fieldValue($content, 'field_content_key'),
      'label' => $this->fieldValue($content, 'field_content_label') ?: $content->label(),
      'variant' => $this->fieldValue($content, 'field_content_variant') ?: 'default',
      'summary' => $this->fieldValue($content, 'field_content_summary'),
      'body' => $this->fieldValue($content, 'field_content_body'),
      'weight' => (int) ($this->fieldValue($content, 'field_content_weight') ?: 0),
      'isDemo' => (bool) $this->fieldValue($content, 'field_is_demo'),
      'demoSource' => $this->fieldValue($content, 'field_demo_source'),
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
