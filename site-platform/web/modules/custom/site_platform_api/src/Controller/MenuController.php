<?php

declare(strict_types=1);

namespace Drupal\site_platform_api\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\node\NodeInterface;
use Drupal\site_platform_api\SiteResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Provides frontend menu APIs driven by Site Page menu fields.
 */
final class MenuController extends ControllerBase {

  /**
   * Constructs a MenuController object.
   */
  public function __construct(
    private readonly SiteResolver $siteResolver,
  ) {}

  /**
   * Creates the controller.
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('site_platform_api.site_resolver'),
    );
  }

  /**
   * Supported public menus.
   */
  private const MENU_MAP = [
    'header' => 'field_show_in_header',
    'footer' => 'field_show_in_footer',
  ];

  /**
   * Returns all frontend menus.
   */
  public function index(): JsonResponse {
    return new JsonResponse([
      'menus' => [
        'header' => [
          'apiPath' => '/api/v1/menus/header',
        ],
        'footer' => [
          'apiPath' => '/api/v1/menus/footer',
        ],
      ],
      'items' => [
        'header' => $this->buildMenu('header'),
        'footer' => $this->buildMenu('footer'),
      ],
    ]);
  }

  /**
   * Returns a single menu.
   */
  public function menu(string $menu): JsonResponse {
    if (!isset(self::MENU_MAP[$menu])) {
      return new JsonResponse([
        'error' => [
          'code' => 'not_found',
          'message' => 'Menu not found.',
        ],
      ], 404);
    }

    return new JsonResponse($this->buildMenu($menu));
  }

  /**
   * Builds frontend menu response.
   */
  private function buildMenu(string $menu): array {
    $visibility_field = self::MENU_MAP[$menu];

    $storage = $this->entityTypeManager()->getStorage('node');

    $query = $storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('type', 'site_page')
      ->condition('status', 1)
      ->condition($visibility_field, TRUE);

    $this->siteResolver->applyCurrentSiteFilter($query, 'site_page');

    $ids = $query
      ->sort('field_menu_weight', 'ASC')
      ->sort('title', 'ASC')
      ->execute();

    $items = [];

    foreach ($storage->loadMultiple($ids) as $page) {
      if (!$page instanceof NodeInterface) {
        continue;
      }

      $items[] = $this->normalizePageMenuItem($page);
    }

    return [
      'id' => $menu,
      'type' => 'menu',
      'count' => count($items),
      'items' => $items,
    ];
  }

  /**
   * Normalizes one Site Page as a frontend menu item.
   */
  private function normalizePageMenuItem(NodeInterface $page): array {
    $slug = $this->getFieldValue($page, 'field_page_key');
    $title = $this->getFieldValue($page, 'field_menu_title') ?: $page->label();
    $url = $slug === 'home' ? '/' : '/' . trim($slug, '/');

    return [
      'id' => (int) $page->id(),
      'uuid' => $page->uuid(),
      'title' => $title,
      'slug' => $slug,
      'url' => $url,
      'weight' => $this->getIntValue($page, 'field_menu_weight'),
      'apiPath' => '/api/v1/pages/' . $slug,
    ];
  }

  /**
   * Gets a string field value.
   */
  private function getFieldValue(NodeInterface $node, string $field_name): string {
    if (!$node->hasField($field_name) || $node->get($field_name)->isEmpty()) {
      return '';
    }

    return (string) $node->get($field_name)->value;
  }

  /**
   * Gets an integer field value.
   */
  private function getIntValue(NodeInterface $node, string $field_name): int {
    if (!$node->hasField($field_name) || $node->get($field_name)->isEmpty()) {
      return 0;
    }

    return (int) $node->get($field_name)->value;
  }

}
