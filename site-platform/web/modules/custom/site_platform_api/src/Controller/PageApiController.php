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
 * Page API controller.
 */
final class PageApiController extends ControllerBase {

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
   * Returns pages for the resolved site.
   */
  public function pages(Request $request): JsonResponse {
    $context = $this->siteContextResolver->resolve($request);

    if (!$context->isResolved() || !$context->getSiteProfileId()) {
      return $this->errorResponse(
        'site_not_resolved',
        'No active site matched this request host.',
        ['host' => $request->getHost()],
        404,
      );
    }

    $nodes = $this->loadPagesForSite((int) $context->getSiteProfileId());

    return $this->successResponse(array_map([$this, 'normalizePage'], $nodes), $context, [
      'node_list:site_page',
      'node:' . $context->getSiteProfileId(),
    ]);
  }

  /**
   * Returns a single page by slug.
   */
  public function page(Request $request, string $slug): JsonResponse {
    $context = $this->siteContextResolver->resolve($request);
    $slug = $this->normalizeSlug($slug);

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
        'page_not_found',
        'Page not found.',
        [
          'siteKey' => $context->getSiteKey(),
          'slug' => $slug,
          'language' => $context->getLanguageId(),
        ],
        404,
      );
    }

    return $this->successResponse($this->normalizePage($page), $context, [
      'node:' . $page->id(),
      'node:' . $context->getSiteProfileId(),
    ]);
  }

  /**
   * Resolves a frontend route path to a page.
   */
  public function route(Request $request, string $path = ''): JsonResponse {
    $context = $this->siteContextResolver->resolve($request);
    $route_path = $this->normalizeRoutePath($path);

    if (!$context->isResolved() || !$context->getSiteProfileId()) {
      return $this->errorResponse(
        'site_not_resolved',
        'No active site matched this request host.',
        ['host' => $request->getHost(), 'path' => $route_path],
        404,
      );
    }

    $page = $this->loadPageByPath((int) $context->getSiteProfileId(), $route_path);

    if (!$page instanceof NodeInterface) {
      return $this->errorResponse(
        'route_not_found',
        'Route not found.',
        [
          'siteKey' => $context->getSiteKey(),
          'path' => $route_path,
          'language' => $context->getLanguageId(),
        ],
        404,
      );
    }

    return $this->successResponse([
      'route' => [
        'type' => 'page',
        'path' => $route_path,
        'slug' => $this->fieldValue($page, 'field_page_slug'),
      ],
      'page' => $this->normalizePage($page),
    ], $context, [
      'node:' . $page->id(),
      'node:' . $context->getSiteProfileId(),
    ]);
  }

  /**
   * Loads pages for a Site Profile.
   *
   * @return array<int, \Drupal\node\NodeInterface>
   *   Page nodes.
   */
  private function loadPagesForSite(int $siteProfileId): array {
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
   * Loads one page by frontend path.
   */
  private function loadPageByPath(int $siteProfileId, string $path): ?NodeInterface {
    $storage = $this->sitePlatformEntityTypeManager->getStorage('node');

    $ids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'site_page')
      ->condition('status', 1)
      ->condition('field_site_profile.target_id', $siteProfileId)
      ->condition('field_page_path', $path)
      ->range(0, 1)
      ->execute();

    if (!$ids) {
      return NULL;
    }

    $page = $storage->load(reset($ids));

    return $page instanceof NodeInterface ? $page : NULL;
  }

  /**
   * Normalizes a page node for public API output.
   *
   * @return array<string, mixed>
   *   Normalized page data.
   */
  private function normalizePage(NodeInterface $page): array {
    return [
      'id' => 'page-' . $page->id(),
      'type' => 'SitePage',
      'title' => $page->label(),
      'pageKey' => $this->fieldValue($page, 'field_page_key'),
      'slug' => $this->fieldValue($page, 'field_page_slug'),
      'path' => $this->fieldValue($page, 'field_page_path'),
      'template' => $this->fieldValue($page, 'field_page_template') ?: 'default',
      'seo' => [
        'title' => $this->fieldValue($page, 'field_seo_title'),
        'description' => $this->fieldValue($page, 'field_seo_description'),
      ],
      'isDemo' => (bool) $this->fieldValue($page, 'field_is_demo'),
      'demoSource' => $this->fieldValue($page, 'field_demo_source'),
      'weight' => (int) ($this->fieldValue($page, 'field_page_weight') ?: 0),
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
   * Normalizes a slug.
   */
  private function normalizeSlug(string $slug): string {
    $slug = trim(strtolower($slug));
    $slug = trim($slug, '/');

    return $slug === '' ? 'home' : $slug;
  }

  /**
   * Normalizes a route path.
   */
  private function normalizeRoutePath(string $path): string {
    $path = trim(urldecode($path));
    $path = preg_replace('/\?.*$/', '', $path) ?: $path;
    $path = '/' . trim($path, '/');

    return $path === '/' ? '/' : rtrim($path, '/');
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
