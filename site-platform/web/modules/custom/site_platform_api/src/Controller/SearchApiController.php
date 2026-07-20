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
 * Simple site-scoped search API controller.
 */
final class SearchApiController extends ControllerBase {

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
   * Returns simple search results for the resolved site.
   */
  public function search(Request $request): JsonResponse {
    $context = $this->siteContextResolver->resolve($request);
    $query = trim((string) $request->query->get('q', ''));

    if (!$context->isResolved() || !$context->getSiteProfileId()) {
      return $this->errorResponse(
        'site_not_resolved',
        'No active site matched this request host.',
        ['host' => $request->getHost(), 'q' => $query],
        404,
      );
    }

    if ($query === '') {
      return $this->successResponse([
        'query' => '',
        'count' => 0,
        'results' => [],
      ], $context, ['node:' . $context->getSiteProfileId()]);
    }

    $results = $this->searchSiteRecords((int) $context->getSiteProfileId(), $query);

    return $this->successResponse([
      'query' => $query,
      'count' => count($results),
      'results' => $results,
    ], $context, [
      'node:' . $context->getSiteProfileId(),
      'node_list:site_page',
      'node_list:site_content_block',
      'node_list:site_media_asset',
    ]);
  }

  /**
   * Searches supported site-scoped records.
   *
   * @return array<int, array<string, mixed>>
   *   Search results.
   */
  private function searchSiteRecords(int $siteProfileId, string $query): array {
    $results = [];

    foreach (['site_page', 'site_content_block', 'site_media_asset'] as $bundle) {
      foreach ($this->loadNodesByBundle($siteProfileId, $bundle) as $node) {
        $haystack = strtolower($node->label() . ' ' . implode(' ', $this->searchableFieldValues($node)));
        if (!str_contains($haystack, strtolower($query))) {
          continue;
        }
        $results[] = $this->normalizeResult($node, $bundle);
      }
    }

    usort($results, static function (array $a, array $b): int {
      return strcmp((string) $a['title'], (string) $b['title']);
    });

    return array_slice($results, 0, 25);
  }

  /**
   * Loads site-scoped nodes by bundle.
   *
   * @return array<int, \Drupal\node\NodeInterface>
   *   Nodes.
   */
  private function loadNodesByBundle(int $siteProfileId, string $bundle): array {
    $storage = $this->sitePlatformEntityTypeManager->getStorage('node');
    $ids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', $bundle)
      ->condition('status', 1)
      ->condition('field_site_profile.target_id', $siteProfileId)
      ->range(0, 100)
      ->execute();

    if (!$ids) {
      return [];
    }

    return array_values(array_filter(
      $storage->loadMultiple($ids),
      static fn($entity): bool => $entity instanceof NodeInterface,
    ));
  }

  /**
   * Gets searchable field values.
   *
   * @return array<int, string>
   *   Searchable values.
   */
  private function searchableFieldValues(NodeInterface $node): array {
    $field_names = [
      'field_page_key',
      'field_page_slug',
      'field_page_path',
      'field_seo_title',
      'field_seo_description',
      'field_content_source',
      'field_content_key',
      'field_content_label',
      'field_content_summary',
      'field_content_body',
      'field_media_key',
      'field_media_kind',
      'field_media_url',
      'field_media_alt',
      'field_media_credit',
    ];

    $values = [];
    foreach ($field_names as $field_name) {
      $value = $this->fieldValue($node, $field_name);
      if ($value !== '') {
        $values[] = $value;
      }
    }

    return $values;
  }

  /**
   * Normalizes one search result.
   *
   * @return array<string, mixed>
   *   Result data.
   */
  private function normalizeResult(NodeInterface $node, string $bundle): array {
    return [
      'id' => $bundle . '-' . $node->id(),
      'type' => $this->publicType($bundle),
      'title' => $node->label(),
      'url' => $this->resultUrl($node, $bundle),
      'summary' => $this->resultSummary($node, $bundle),
    ];
  }

  /**
   * Maps a bundle to a public type.
   */
  private function publicType(string $bundle): string {
    return match ($bundle) {
      'site_page' => 'page',
      'site_content_block' => 'content',
      'site_media_asset' => 'media',
      default => $bundle,
    };
  }

  /**
   * Gets a result URL/path.
   */
  private function resultUrl(NodeInterface $node, string $bundle): string {
    return match ($bundle) {
      'site_page' => $this->fieldValue($node, 'field_page_path'),
      'site_media_asset' => $this->fieldValue($node, 'field_media_url'),
      default => '',
    };
  }

  /**
   * Gets a result summary.
   */
  private function resultSummary(NodeInterface $node, string $bundle): string {
    $value = match ($bundle) {
      'site_page' => $this->fieldValue($node, 'field_seo_description'),
      'site_content_block' => $this->fieldValue($node, 'field_content_summary'),
      'site_media_asset' => $this->fieldValue($node, 'field_media_alt') ?: $this->fieldValue($node, 'field_media_credit'),
      default => '',
    };

    return mb_substr($value, 0, 220);
  }

  /**
   * Gets a field value safely.
   */
  private function fieldValue(NodeInterface $node, string $fieldName): string {
    if (!$node->hasField($fieldName) || $node->get($fieldName)->isEmpty()) {
      return '';
    }

    $values = [];
    foreach ($node->get($fieldName) as $item) {
      $value = trim((string) ($item->value ?? ''));
      if ($value !== '') {
        $values[] = $value;
      }
    }

    return implode(' ', $values);
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
        'maxAge' => 120,
        'tags' => $cacheTags,
        'contexts' => ['url.site', 'headers:host', 'url.query_args:q'],
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
