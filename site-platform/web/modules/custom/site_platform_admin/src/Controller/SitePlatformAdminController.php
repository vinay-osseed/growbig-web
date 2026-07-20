<?php

declare(strict_types=1);

namespace Drupal\site_platform_admin\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Site Platform admin overview controller.
 */
final class SitePlatformAdminController extends ControllerBase {

  /**
   * Managed bundles shown in the overview.
   */
  private const MANAGED_BUNDLES = [
    'site_profile' => 'Site Profile',
    'site_page' => 'Site Page',
    'site_menu' => 'Site Menu',
    'site_menu_item' => 'Site Menu Item',
    'site_form' => 'Legacy Site Form wrapper',
    'site_form_field' => 'Legacy Site Form Field wrapper',
    'site_form_submission' => 'Legacy Site Form Submission fallback',
    'site_content_block' => 'Site Content Block',
    'site_media_asset' => 'Site Media Asset',
  ];

  /**
   * Site Platform modules shown in the overview.
   */
  private const PLATFORM_MODULES = [
    'site_platform_core' => 'Core',
    'site_platform_site' => 'Site Profile',
    'site_platform_page' => 'Pages',
    'site_platform_menu' => 'Menus',
    'site_platform_component' => 'Components',
    'site_platform_form' => 'Legacy form wrappers',
    'site_platform_content' => 'Content Blocks',
    'site_platform_media' => 'Media Assets',
    'site_platform_api' => 'API',
    'site_platform_setup' => 'Setup',
    'site_platform_admin' => 'Admin',
    'webform' => 'Webform',
  ];

  /**
   * Constructs the controller.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly StateInterface $state,
    private readonly ModuleHandlerInterface $moduleHandler,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('entity_type.manager'),
      $container->get('state'),
      $container->get('module_handler'),
    );
  }

  /**
   * Builds the admin overview page.
   *
   * @return array<string, mixed>
   *   Render array.
   */
  public function overview(): array {
    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['site-platform-admin-overview']],
      'intro' => [
        '#markup' => '<p>Site Platform backend overview. Use this page to review setup state, enabled modules, managed records, and available API groups.</p>',
      ],
      'setup' => [
        '#type' => 'details',
        '#title' => $this->t('Setup status'),
        '#open' => TRUE,
        'table' => [
          '#type' => 'table',
          '#header' => [$this->t('Item'), $this->t('Value')],
          '#rows' => $this->setupRows(),
          '#empty' => $this->t('No setup status found.'),
        ],
      ],
      'modules' => [
        '#type' => 'details',
        '#title' => $this->t('Modules'),
        '#open' => TRUE,
        'table' => [
          '#type' => 'table',
          '#header' => [$this->t('Module'), $this->t('Status')],
          '#rows' => $this->moduleRows(),
        ],
      ],
      'counts' => [
        '#type' => 'details',
        '#title' => $this->t('Managed record counts'),
        '#open' => TRUE,
        'table' => [
          '#type' => 'table',
          '#header' => [$this->t('Record type'), $this->t('Machine name'), $this->t('Count')],
          '#rows' => $this->countRows(),
        ],
      ],
      'apis' => [
        '#type' => 'details',
        '#title' => $this->t('API groups'),
        '#open' => TRUE,
        'list' => [
          '#theme' => 'item_list',
          '#items' => [
            '/api/v1/site',
            '/api/v1/pages and /api/v1/pages/{slug}',
            '/api/v1/routes and /api/v1/routes/{path}',
            '/api/v1/menus and /api/v1/menus/{menu}',
            '/api/v1/forms/{form} and /api/v1/forms/{form}/submit',
            '/api/v1/content, /api/v1/content/{source}, /api/v1/content/{source}/{key}',
            '/api/v1/media and /api/v1/media/{key}',
            '/api/v1/seo and /api/v1/seo/page/{slug}',
            '/api/v1/analytics',
            '/api/v1/search?q=term',
          ],
        ],
      ],
    ];
  }

  /**
   * Builds setup status rows.
   *
   * @return array<int, array<int, string>>
   *   Table rows.
   */
  private function setupRows(): array {
    $last_run = $this->state->get('site_platform_setup.last_run');
    $last_import = $this->state->get('site_platform_setup.last_import');
    $last_reset = $this->state->get('site_platform_setup.last_site_reset');
    $completion = $this->state->get('site_platform_setup.completion');

    return [
      ['Last run', $this->formatStateSummary($last_run, 'status', 'ranAt')],
      ['Last import', $this->formatStateSummary($last_import, 'siteKey', 'ranAt')],
      ['Last site reset', $this->formatStateSummary($last_reset, 'siteKey', 'ranAt')],
      ['Completion status', is_array($completion) ? (string) ($completion['status'] ?? 'unknown') : 'not_completed'],
      ['Setup locked', is_array($completion) && !empty($completion['locked']) ? 'yes' : 'no'],
    ];
  }

  /**
   * Builds module status rows.
   *
   * @return array<int, array<int, string>>
   *   Table rows.
   */
  private function moduleRows(): array {
    $rows = [];
    foreach (self::PLATFORM_MODULES as $module => $label) {
      $rows[] = [$label . ' (' . $module . ')', $this->moduleHandler->moduleExists($module) ? 'enabled' : 'disabled'];
    }

    return $rows;
  }

  /**
   * Builds managed entity count rows.
   *
   * @return array<int, array<int, string|int>>
   *   Table rows.
   */
  private function countRows(): array {
    $rows = [];
    foreach (self::MANAGED_BUNDLES as $bundle => $label) {
      $rows[] = [$label, $bundle, $this->countBundle($bundle)];
    }

    return $rows;
  }

  /**
   * Counts nodes by bundle.
   */
  private function countBundle(string $bundle): int {
    try {
      $ids = $this->entityTypeManager->getStorage('node')
        ->getQuery()
        ->accessCheck(FALSE)
        ->condition('type', $bundle)
        ->execute();

      return count($ids);
    }
    catch (\Throwable) {
      return 0;
    }
  }

  /**
   * Formats a state array summary.
   */
  private function formatStateSummary(mixed $value, string $mainKey, string $dateKey): string {
    if (!is_array($value)) {
      return 'never';
    }

    $main = (string) ($value[$mainKey] ?? 'unknown');
    $date = (string) ($value[$dateKey] ?? 'unknown');

    return $main . ' at ' . $date;
  }

}
