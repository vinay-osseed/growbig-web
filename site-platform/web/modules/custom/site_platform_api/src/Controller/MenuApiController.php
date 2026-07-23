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
 * Menu API controller.
 */
final class MenuApiController extends ControllerBase {

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
   * Returns menus for the resolved site.
   */
  public function menus(Request $request): JsonResponse {
    $context = $this->siteContextResolver->resolve($request);

    if (!$context->isResolved() || !$context->getSiteProfileId()) {
      return $this->errorResponse(
        'site_not_resolved',
        'No active site matched this request host.',
        ['host' => $request->getHost()],
        404,
      );
    }

    $menus = $this->loadMenusForSite((int) $context->getSiteProfileId());

    return $this->successResponse(array_map(function (NodeInterface $menu): array {
      return $this->normalizeMenu($menu, FALSE);
    }, $menus), $context, [
      'node_list:site_menu',
      'node:' . $context->getSiteProfileId(),
    ]);
  }

  /**
   * Returns one menu by key.
   */
  public function menu(Request $request, string $menu): JsonResponse {
    $context = $this->siteContextResolver->resolve($request);
    $menu_key = $this->normalizeKey($menu);

    if (!$context->isResolved() || !$context->getSiteProfileId()) {
      return $this->errorResponse(
        'site_not_resolved',
        'No active site matched this request host.',
        ['host' => $request->getHost(), 'menu' => $menu_key],
        404,
      );
    }

    $menu_node = $this->loadMenuByKey((int) $context->getSiteProfileId(), $menu_key);

    if (!$menu_node instanceof NodeInterface) {
      return $this->errorResponse(
        'menu_not_found',
        'Menu not found.',
        [
          'siteKey' => $context->getSiteKey(),
          'menu' => $menu_key,
          'language' => $context->getLanguageId(),
        ],
        404,
      );
    }

    return $this->successResponse($this->normalizeMenu($menu_node, TRUE), $context, [
      'node:' . $menu_node->id(),
      'node:' . $context->getSiteProfileId(),
      'node_list:site_menu_item',
    ]);
  }

  /**
   * Loads menus for a Site Profile.
   *
   * @return array<int, \Drupal\node\NodeInterface>
   *   Menu nodes.
   */
  private function loadMenusForSite(int $siteProfileId): array {
    $storage = $this->sitePlatformEntityTypeManager->getStorage('node');

    $query = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'site_menu')
      ->condition('status', 1)
      ->condition('field_site_profile.target_id', $siteProfileId)
      ->sort('field_menu_weight.value', 'ASC')
      ->sort('title', 'ASC');

    if ($this->fieldExists('node.site_menu.field_menu_is_active')) {
      $query->condition('field_menu_is_active', 1);
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
   * Loads one menu by menu key.
   */
  private function loadMenuByKey(int $siteProfileId, string $menuKey): ?NodeInterface {
    $storage = $this->sitePlatformEntityTypeManager->getStorage('node');

    $query = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'site_menu')
      ->condition('status', 1)
      ->condition('field_site_profile.target_id', $siteProfileId)
      ->condition('field_menu_key', $menuKey)
      ->range(0, 1);

    if ($this->fieldExists('node.site_menu.field_menu_is_active')) {
      $query->condition('field_menu_is_active', 1);
    }

    $ids = $query->execute();

    if (!$ids) {
      return NULL;
    }

    $menu = $storage->load(reset($ids));

    return $menu instanceof NodeInterface ? $menu : NULL;
  }

  /**
   * Loads menu items for a Site Menu.
   *
   * @return array<int, \Drupal\node\NodeInterface>
   *   Menu item nodes.
   */
  private function loadMenuItems(NodeInterface $menu): array {
    $storage = $this->sitePlatformEntityTypeManager->getStorage('node');

    $query = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'site_menu_item')
      ->condition('status', 1)
      ->condition('field_site_menu.target_id', (int) $menu->id())
      ->sort('field_menu_weight.value', 'ASC')
      ->sort('title', 'ASC');

    if ($this->fieldExists('node.site_menu_item.field_menu_is_enabled')) {
      $query->condition('field_menu_is_enabled', 1);
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
   * Normalizes a Site Menu node.
   *
   * @return array<string, mixed>
   *   Normalized menu data.
   */
  private function normalizeMenu(NodeInterface $menu, bool $includeItems): array {
    $data = [
      'id' => 'menu-' . $menu->id(),
      'type' => 'SiteMenu',
      'key' => $this->fieldValue($menu, 'field_menu_key'),
      'label' => $this->fieldValue($menu, 'field_menu_label') ?: $menu->label(),
      'weight' => (int) ($this->fieldValue($menu, 'field_menu_weight') ?: 0),
    ];

    if ($includeItems) {
      $data['items'] = array_map(function (NodeInterface $item): array {
        return $this->normalizeMenuItem($item);
      }, $this->loadMenuItems($menu));
    }

    return $data;
  }

  /**
   * Normalizes a Site Menu Item node.
   *
   * @return array<string, mixed>
   *   Normalized menu item data.
   */
  private function normalizeMenuItem(NodeInterface $item): array {
    $page = $this->referencedNode($item, 'field_menu_page');
    $parent = $this->referencedNode($item, 'field_menu_parent');
    $link_type = $this->fieldValue($item, 'field_menu_link_type') ?: 'path';

    return [
      'id' => 'menu-item-' . $item->id(),
      'type' => 'SiteMenuItem',
      'key' => $this->fieldValue($item, 'field_menu_item_key'),
      'title' => $this->fieldValue($item, 'field_menu_title') ?: $item->label(),
      'parentKey' => $parent instanceof NodeInterface ? $this->fieldValue($parent, 'field_menu_item_key') : '',
      'link' => $this->buildLink($item, $page, $link_type),
      'target' => $this->fieldValue($item, 'field_menu_target') ?: '_self',
      'isButton' => (bool) $this->fieldValue($item, 'field_menu_is_button'),
      'weight' => (int) ($this->fieldValue($item, 'field_menu_weight') ?: 0),
    ];
  }

  /**
   * Builds normalized link data.
   *
   * @return array<string, mixed>
   *   Normalized link data.
   */
  private function buildLink(NodeInterface $item, ?NodeInterface $page, string $linkType): array {
    $anchor = trim((string) $this->fieldValue($item, 'field_menu_anchor'));

    if ($linkType === 'page' && $page instanceof NodeInterface) {
      $path = $this->fieldValue($page, 'field_page_path') ?: '/';
      return [
        'type' => 'page',
        'pageKey' => $this->fieldValue($page, 'field_page_key'),
        'slug' => $this->fieldValue($page, 'field_page_slug'),
        'path' => $this->appendAnchor($path, $anchor),
      ];
    }

    if ($linkType === 'external') {
      return [
        'type' => 'external',
        'url' => $this->fieldValue($item, 'field_menu_external_url'),
      ];
    }

    if ($linkType === 'nolink') {
      return [
        'type' => 'nolink',
      ];
    }

    $path = $this->fieldValue($item, 'field_menu_path') ?: '#';
    return [
      'type' => 'path',
      'path' => $this->appendAnchor($path, $anchor),
    ];
  }

  /**
   * Appends an anchor to a path.
   */
  private function appendAnchor(string $path, string $anchor): string {
    if ($anchor === '') {
      return $path;
    }

    return rtrim($path, '#') . '#' . ltrim($anchor, '#');
  }

  /**
   * Gets a referenced node.
   */
  private function referencedNode(NodeInterface $node, string $fieldName): ?NodeInterface {
    if (!$node->hasField($fieldName) || $node->get($fieldName)->isEmpty()) {
      return NULL;
    }

    $entity = $node->get($fieldName)->entity;

    return $entity instanceof NodeInterface ? $entity : NULL;
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
