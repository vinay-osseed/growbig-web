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
 * Site SEO API controller.
 */
final class SeoApiController extends ControllerBase {

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
   * Returns SEO index data for the resolved site.
   */
  public function seo(Request $request): JsonResponse {
    $context = $this->siteContextResolver->resolve($request);

    if (!$context->isResolved() || !$context->getSiteProfileId()) {
      return $this->errorResponse(
        'site_not_resolved',
        'No active site matched this request host.',
        ['host' => $request->getHost()],
        404,
      );
    }

    $pages = $this->loadPages((int) $context->getSiteProfileId());

    return $this->successResponse([
      'siteKey' => $context->getSiteKey(),
      'siteName' => $context->getSiteName(),
      'pages' => array_map([$this, 'normalizeSeoPage'], $pages),
    ], $context, [
      'node_list:site_page',
      'node:' . $context->getSiteProfileId(),
    ]);
  }

  /**
   * Returns SEO data for one page slug.
   */
  public function seoPage(Request $request, string $slug): JsonResponse {
    $context = $this->siteContextResolver->resolve($request);
    $slug = $this->normalizeKey($slug);

    if (!$context->isResolved() || !$context->getSiteProfileId()) {
      return $this->errorResponse(
        'site_not_resolved',
        'No active site matched this request host.',
        ['host' => $request->getHost(), 'slug' => $slug],
        404,
      );
    }

    $page = $this->loadPageBySlug((int) $context->getSiteProfileId(), $slug);
    if (!$page instanceof NodeInterface) {
      return $this->errorResponse(
        'seo_page_not_found',
        'SEO page not found.',
        ['siteKey' => $context->getSiteKey(), 'slug' => $slug],
        404,
      );
    }

    return $this->successResponse($this->normalizeSeoPage($page), $context, [
      'node:' . $page->id(),
      'node:' . $context->getSiteProfileId(),
    ]);
  }

  /**
   * Loads site pages.
   *
   * @return array<int, \Drupal\node\NodeInterface>
   *   Site page nodes.
   */
  private function loadPages(int $siteProfileId): array {
    $storage = $this->sitePlatformEntityTypeManager->getStorage('node');
    $ids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'site_page')
      ->condition('status', 1)
      ->condition('field_site_profile.target_id', $siteProfileId)
      ->sort('field_page_weight.value', 'ASC')
      ->sort('title', 'ASC')
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
   * Loads one page by slug.
   */
  private function loadPageBySlug(int $siteProfileId, string $slug): ?NodeInterface {
    $storage = $this->sitePlatformEntityTypeManager->getStorage('node');
    $ids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'site_page')
      ->condition('status', 1)
      ->condition('field_site_profile.target_id', $siteProfileId)
      ->condition('field_page_slug', $slug)
      ->range(0, 1)
      ->execute();

    if (!$ids) {
      return NULL;
    }

    $page = $storage->load(reset($ids));
    return $page instanceof NodeInterface ? $page : NULL;
  }

  /**
   * Normalizes one page into SEO metadata.
   *
   * @return array<string, mixed>
   *   SEO metadata.
   */
  private function normalizeSeoPage(NodeInterface $page): array {
    $title = $this->fieldValue($page, 'field_seo_title') ?: $page->label();
    $description = $this->fieldValue($page, 'field_seo_description');

    return [
      'id' => 'page-' . $page->id(),
      'type' => 'SitePageSeo',
      'title' => $title,
      'description' => $description,
      'pageTitle' => $page->label(),
      'slug' => $this->fieldValue($page, 'field_page_slug'),
      'path' => $this->fieldValue($page, 'field_page_path'),
      'canonicalPath' => $this->fieldValue($page, 'field_page_path'),
    ];
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
   * Normalizes a key.
   */
  private function normalizeKey(string $key): string {
    $key = strtolower(trim($key));
    $key = preg_replace('/[^a-z0-9_-]+/', '-', $key) ?: $key;

    return trim($key, '-');
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
