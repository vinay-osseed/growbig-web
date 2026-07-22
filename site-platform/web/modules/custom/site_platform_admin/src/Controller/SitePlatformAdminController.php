<?php

declare(strict_types=1);

namespace Drupal\site_platform_admin\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\State\StateInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Site Platform admin controller.
 *
 * Keep this controller deliberately safe: no Url objects inside table rows, no
 * config import requirement, and no active config mutation. CRUD operations are
 * exposed as plain, escaped HTML links to Drupal's native node/Webform screens.
 */
final class SitePlatformAdminController extends ControllerBase {

  /**
   * Primary workspace sections.
   */
  private const SECTIONS = [
    'Sites' => [
      'path' => '/admin/site-platform/sites',
      'purpose' => 'Site Profiles for brands, domains, and per-site settings.',
    ],
    'Landing Pages' => [
      'path' => '/admin/site-platform/pages',
      'purpose' => 'Frontend page records with route path, template, SEO, and page sections.',
    ],
    'Content Blocks' => [
      'path' => '/admin/site-platform/content-blocks',
      'purpose' => 'Reusable source/key content exposed through the content API.',
    ],
    'Media Assets' => [
      'path' => '/admin/site-platform/media-assets',
      'purpose' => 'Frontend-safe media metadata while binary files move toward Media Library.',
    ],
    'Forms' => [
      'path' => '/admin/site-platform/forms',
      'purpose' => 'Native Drupal Webform first, legacy wrappers only as fallback compatibility.',
    ],
    'Menus' => [
      'path' => '/admin/site-platform/menus',
      'purpose' => 'Drupal core menus as target workflow; existing node wrappers remain compatibility data.',
    ],
    'Legacy Wrappers' => [
      'path' => '/admin/site-platform/legacy-wrappers',
      'purpose' => 'Technical compatibility bundles kept out of the primary editor workflow.',
    ],
  ];

  /**
   * Managed bundles shown in the overview.
   */
  private const MANAGED_BUNDLES = [
    'site_profile' => 'Site Profile',
    'site_page' => 'Landing Page',
    'site_content_block' => 'Content Block',
    'site_media_asset' => 'Media Asset Metadata',
    'site_menu' => 'Legacy API Menu Wrapper',
    'site_menu_item' => 'Legacy API Menu Item Wrapper',
    'site_form' => 'Legacy API Form Wrapper',
    'site_form_field' => 'Legacy API Form Field Wrapper',
    'site_form_submission' => 'Legacy API Form Submission Fallback',
  ];

  /**
   * Site Platform modules shown in the overview.
   */
  private const PLATFORM_MODULES = [
    'site_platform_core' => 'Core site context',
    'site_platform_site' => 'Site profiles',
    'site_platform_page' => 'Landing pages',
    'site_platform_component' => 'Page sections / components',
    'site_platform_content' => 'Reusable content blocks',
    'site_platform_media' => 'Media asset metadata',
    'site_platform_api' => 'Frontend API',
    'site_platform_setup' => 'Setup and YAML import',
    'site_platform_admin' => 'Admin workspace',
    'webform' => 'Native Drupal Webform',
  ];

  /**
   * Constructs the controller.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $sitePlatformEntityTypeManager,
    private readonly StateInterface $sitePlatformState,
    private readonly ModuleHandlerInterface $sitePlatformModuleHandler,
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
   * Builds the admin workspace overview.
   */
  public function overview(): array {
    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['site-platform-admin-overview']],
      'intro' => [
        '#markup' => '<p><strong>Site Platform admin workspace.</strong> Use this page as the backend entry point for reviewing and managing sites, pages, content, media, forms, menus, and compatibility wrappers.</p>',
      ],
      'workflow' => [
        '#type' => 'details',
        '#title' => $this->t('Recommended workflow'),
        '#open' => TRUE,
        'items' => [
          '#theme' => 'item_list',
          '#items' => [
            'Create or review one Site Profile per brand/site.',
            'Create Landing Pages and attach Paragraph components for frontend page structure.',
            'Use native Drupal Webform for real forms and submissions.',
            'Use Content Blocks and Media Assets for reusable API output.',
            'Treat legacy menu/form wrapper nodes as compatibility records only.',
          ],
        ],
      ],
      'sections' => [
        '#type' => 'details',
        '#title' => $this->t('Workspace sections'),
        '#open' => TRUE,
        'table' => [
          '#type' => 'table',
          '#header' => ['Section', 'Purpose', 'Open'],
          '#rows' => $this->sectionRows(),
        ],
      ],
      'setup' => [
        '#type' => 'details',
        '#title' => $this->t('Setup status'),
        '#open' => TRUE,
        'table' => [
          '#type' => 'table',
          '#header' => ['Item', 'Value'],
          '#rows' => $this->setupRows(),
          '#empty' => 'No setup status found.',
        ],
      ],
      'modules' => [
        '#type' => 'details',
        '#title' => $this->t('Backend capability status'),
        '#open' => TRUE,
        'table' => [
          '#type' => 'table',
          '#header' => ['Capability', 'Status'],
          '#rows' => $this->moduleRows(),
        ],
      ],
      'counts' => [
        '#type' => 'details',
        '#title' => $this->t('Managed record counts'),
        '#open' => TRUE,
        'table' => [
          '#type' => 'table',
          '#header' => ['Record type', 'Machine name', 'Count', 'Open section'],
          '#rows' => $this->countRows(),
        ],
      ],
      'apis' => [
        '#type' => 'details',
        '#title' => $this->t('Frontend API groups'),
        '#open' => TRUE,
        'list' => [
          '#theme' => 'item_list',
          '#items' => [
            '/api/v1/site',
            '/api/v1/pages and /api/v1/pages/{slug}',
            '/api/v1/routes/{path}',
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
   * Builds the Site Profiles admin page.
   */
  public function sites(): array {
    $rows = [];
    foreach ($this->loadNodes('site_profile') as $site) {
      $rows[] = [
        $this->nodeTitleCell($site),
        $this->fieldValue($site, 'field_site_key'),
        $this->fieldValue($site, 'field_frontend_domains'),
        $this->fieldValue($site, 'field_api_domains'),
        $this->fieldValue($site, 'field_is_active') === '1' ? 'yes' : 'no',
        $this->fieldValue($site, 'field_is_default') === '1' ? 'yes' : 'no',
        $this->nodeActionsCell($site),
      ];
    }

    return $this->listingPage(
      'Sites',
      'One Site Profile represents one brand/site/domain set.',
      [
        'Add Site Profile' => '/node/add/site_profile',
        'Drupal content list' => '/admin/content',
      ],
      ['Site', 'Key', 'Frontend domains', 'API domains', 'Active', 'Default', 'Actions'],
      $rows,
      'No Site Profiles found.',
    );
  }

  /**
   * Builds the Landing Pages admin page.
   */
  public function pages(): array {
    $rows = [];
    foreach ($this->loadNodes('site_page') as $page) {
      $rows[] = [
        $this->siteLabelForNode($page),
        $this->nodeTitleCell($page),
        $this->fieldValue($page, 'field_page_key'),
        $this->fieldValue($page, 'field_page_path'),
        $this->fieldValue($page, 'field_page_template'),
        (string) $this->paragraphCount($page, 'field_page_components'),
        $this->fieldValue($page, 'field_seo_title') !== '' ? 'yes' : 'no',
        $this->nodeActionsCell($page),
      ];
    }

    return $this->listingPage(
      'Landing Pages',
      'Frontend page records with site, path, template, SEO, and Paragraph sections.',
      [
        'Add Landing Page' => '/node/add/site_page',
        'Drupal content list' => '/admin/content',
      ],
      ['Site', 'Page', 'Key', 'Path', 'Template', 'Sections', 'SEO title', 'Actions'],
      $rows,
      'No Landing Pages found.',
    );
  }

  /**
   * Builds the Content Blocks admin page.
   */
  public function contentBlocks(): array {
    $rows = [];
    foreach ($this->loadNodes('site_content_block') as $block) {
      $rows[] = [
        $this->siteLabelForNode($block),
        $this->nodeTitleCell($block),
        $this->fieldValue($block, 'field_content_source'),
        $this->fieldValue($block, 'field_content_key'),
        $this->fieldValue($block, 'field_content_is_active') === '1' ? 'yes' : 'no',
        $this->nodeActionsCell($block),
      ];
    }

    return $this->listingPage(
      'Content Blocks',
      'Reusable source/key content exposed through the content API.',
      [
        'Add Content Block' => '/node/add/site_content_block',
        'Drupal content list' => '/admin/content',
      ],
      ['Site', 'Block', 'Source', 'Key', 'Active', 'Actions'],
      $rows,
      'No Content Blocks found.',
    );
  }

  /**
   * Builds the Media Assets admin page.
   */
  public function mediaAssets(): array {
    $rows = [];
    foreach ($this->loadNodes('site_media_asset') as $asset) {
      $rows[] = [
        $this->siteLabelForNode($asset),
        $this->nodeTitleCell($asset),
        $this->fieldValue($asset, 'field_media_key'),
        $this->fieldValue($asset, 'field_media_kind'),
        $this->fieldValue($asset, 'field_media_is_active') === '1' ? 'yes' : 'no',
        $this->nodeActionsCell($asset),
      ];
    }

    return $this->listingPage(
      'Media Assets',
      'Frontend-safe media metadata. Binary file management should move toward Drupal Media Library.',
      [
        'Add Media Asset Metadata' => '/node/add/site_media_asset',
        'Drupal files' => '/admin/content/files',
      ],
      ['Site', 'Asset', 'Key', 'Kind', 'Active', 'Actions'],
      $rows,
      'No Media Assets found.',
    );
  }

  /**
   * Builds the Forms admin page.
   */
  public function forms(): array {
    $webform_rows = $this->webformRows();
    $legacy_rows = [];
    foreach ($this->loadNodes('site_form') as $form) {
      $legacy_rows[] = [
        $this->siteLabelForNode($form),
        $this->nodeTitleCell($form),
        $this->fieldValue($form, 'field_form_key'),
        $this->fieldValue($form, 'field_form_is_active') === '1' ? 'yes' : 'no',
        $this->nodeActionsCell($form),
      ];
    }

    $build = $this->listingPage(
      'Forms',
      'Native Drupal Webform is the primary form system. Legacy Site Form nodes remain fallback compatibility records only.',
      [
        'Add Webform' => '/admin/structure/webform/add',
        'All Webforms' => '/admin/structure/webform',
        'Add Legacy Wrapper' => '/node/add/site_form',
      ],
      ['Webform', 'ID', 'Status', 'Actions'],
      $webform_rows,
      'No Webforms found.',
    );

    $build['legacy'] = [
      '#type' => 'details',
      '#title' => 'Legacy form wrappers',
      '#open' => FALSE,
      'table' => [
        '#type' => 'table',
        '#header' => ['Site', 'Wrapper', 'Key', 'Active', 'Actions'],
        '#rows' => $legacy_rows,
        '#empty' => 'No legacy Site Form wrappers found.',
      ],
    ];

    return $build;
  }

  /**
   * Builds the Menus admin page.
   */
  public function menus(): array {
    $rows = [];
    foreach ($this->loadNodes('site_menu') as $menu) {
      $rows[] = [
        $this->siteLabelForNode($menu),
        $this->nodeTitleCell($menu),
        $this->fieldValue($menu, 'field_menu_key'),
        $this->fieldValue($menu, 'field_menu_is_active') === '1' ? 'yes' : 'no',
        $this->nodeActionsCell($menu),
      ];
    }

    return $this->listingPage(
      'Menus',
      'Long-term target is Drupal core menus. Current Site Menu nodes are compatibility wrappers for the API/YAML importer.',
      [
        'Drupal core menus' => '/admin/structure/menu',
        'Add Drupal menu' => '/admin/structure/menu/add',
        'Add Legacy Menu Wrapper' => '/node/add/site_menu',
      ],
      ['Site', 'Legacy menu wrapper', 'Key', 'Active', 'Actions'],
      $rows,
      'No legacy menu wrappers found.',
    );
  }

  /**
   * Builds the Legacy Wrappers admin page.
   */
  public function legacyWrappers(): array {
    $rows = [];
    foreach (['site_menu', 'site_menu_item', 'site_form', 'site_form_field', 'site_form_submission'] as $bundle) {
      foreach ($this->loadNodes($bundle) as $node) {
        $rows[] = [
          self::MANAGED_BUNDLES[$bundle] ?? $bundle,
          $this->siteLabelForNode($node),
          $this->nodeTitleCell($node),
          $this->nodeActionsCell($node),
        ];
      }
    }

    return $this->listingPage(
      'Legacy Wrappers',
      'Compatibility node records. These are not the preferred editor workflow.',
      [
        'Add Menu Wrapper' => '/node/add/site_menu',
        'Add Menu Item Wrapper' => '/node/add/site_menu_item',
        'Add Form Wrapper' => '/node/add/site_form',
        'Add Form Field Wrapper' => '/node/add/site_form_field',
      ],
      ['Type', 'Site', 'Record', 'Actions'],
      $rows,
      'No legacy wrapper records found.',
    );
  }

  /**
   * Builds a reusable listing page.
   */
  private function listingPage(string $title, string $intro, array $actions, array $header, array $rows, string $empty): array {
    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['site-platform-admin-listing']],
      'intro' => [
        '#markup' => '<p><strong>' . Html::escape($title) . '.</strong> ' . Html::escape($intro) . '</p>',
      ],
      'actions' => $this->actionsMarkup($actions),
      'table' => [
        '#type' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#empty' => $empty,
      ],
    ];
  }

  /**
   * Builds workspace section rows.
   */
  private function sectionRows(): array {
    $rows = [];
    foreach (self::SECTIONS as $section => $info) {
      $rows[] = [$section, $info['purpose'], $this->linksCell(['Open' => $info['path']])];
    }

    return $rows;
  }

  /**
   * Builds setup status rows.
   */
  private function setupRows(): array {
    $last_run = $this->sitePlatformState->get('site_platform_setup.last_run');
    $last_import = $this->sitePlatformState->get('site_platform_setup.last_import');
    $last_reset = $this->sitePlatformState->get('site_platform_setup.last_site_reset');
    $completion = $this->sitePlatformState->get('site_platform_setup.completion');

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
   */
  private function moduleRows(): array {
    $rows = [];
    foreach (self::PLATFORM_MODULES as $module => $label) {
      $rows[] = [$label . ' (' . $module . ')', $this->sitePlatformModuleHandler->moduleExists($module) ? 'enabled' : 'disabled'];
    }

    return $rows;
  }

  /**
   * Builds managed entity count rows.
   */
  private function countRows(): array {
    $section_by_bundle = [
      'site_profile' => '/admin/site-platform/sites',
      'site_page' => '/admin/site-platform/pages',
      'site_content_block' => '/admin/site-platform/content-blocks',
      'site_media_asset' => '/admin/site-platform/media-assets',
      'site_menu' => '/admin/site-platform/legacy-wrappers',
      'site_menu_item' => '/admin/site-platform/legacy-wrappers',
      'site_form' => '/admin/site-platform/legacy-wrappers',
      'site_form_field' => '/admin/site-platform/legacy-wrappers',
      'site_form_submission' => '/admin/site-platform/legacy-wrappers',
    ];

    $rows = [];
    foreach (self::MANAGED_BUNDLES as $bundle => $label) {
      $rows[] = [
        $label,
        $bundle,
        (string) $this->countBundle($bundle),
        $this->linksCell(['Open' => $section_by_bundle[$bundle] ?? '/admin/site-platform']),
      ];
    }

    return $rows;
  }

  /**
   * Counts nodes by bundle.
   */
  private function countBundle(string $bundle): int {
    try {
      $ids = $this->sitePlatformEntityTypeManager->getStorage('node')
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
   * Loads nodes for a bundle.
   *
   * @return array<int, \Drupal\node\NodeInterface>
   *   Loaded nodes.
   */
  private function loadNodes(string $bundle, int $limit = 50): array {
    try {
      $storage = $this->sitePlatformEntityTypeManager->getStorage('node');
      $ids = $storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('type', $bundle)
        ->sort('title')
        ->range(0, $limit)
        ->execute();

      return $ids ? $storage->loadMultiple($ids) : [];
    }
    catch (\Throwable) {
      return [];
    }
  }

  /**
   * Builds native Webform rows.
   */
  private function webformRows(): array {
    if (!$this->sitePlatformModuleHandler->moduleExists('webform')) {
      return [];
    }

    try {
      $storage = $this->sitePlatformEntityTypeManager->getStorage('webform');
      $webforms = $storage->loadMultiple();
    }
    catch (\Throwable) {
      return [];
    }

    $rows = [];
    foreach ($webforms as $webform) {
      $id = method_exists($webform, 'id') ? (string) $webform->id() : '';
      $label = method_exists($webform, 'label') ? (string) $webform->label() : $id;
      $status = method_exists($webform, 'isOpen') && $webform->isOpen() ? 'open' : 'closed';
      $rows[] = [
        $this->linksCell([$label => '/admin/structure/webform/manage/' . $id]),
        $id,
        $status,
        $this->linksCell([
          'Manage' => '/admin/structure/webform/manage/' . $id,
          'View' => '/form/' . $id,
          'Results' => '/admin/structure/webform/manage/' . $id . '/results/submissions',
          'Settings' => '/admin/structure/webform/manage/' . $id . '/settings',
          'Delete' => '/admin/structure/webform/manage/' . $id . '/delete',
        ]),
      ];
    }

    return $rows;
  }

  /**
   * Gets a basic field value as a plain string.
   */
  private function fieldValue(NodeInterface $node, string $field_name): string {
    if (!$node->hasField($field_name) || $node->get($field_name)->isEmpty()) {
      return '';
    }

    return trim($node->get($field_name)->getString());
  }

  /**
   * Gets the Site Profile label for a node.
   */
  private function siteLabelForNode(NodeInterface $node): string {
    if (!$node->hasField('field_site_profile') || $node->get('field_site_profile')->isEmpty()) {
      return '';
    }

    $entities = $node->get('field_site_profile')->referencedEntities();
    if (!$entities) {
      return $this->fieldValue($node, 'field_site_profile');
    }

    return (string) reset($entities)->label();
  }

  /**
   * Counts paragraph references.
   */
  private function paragraphCount(NodeInterface $node, string $field_name): int {
    if (!$node->hasField($field_name) || $node->get($field_name)->isEmpty()) {
      return 0;
    }

    return $node->get($field_name)->count();
  }

  /**
   * Builds a linked title cell for a node.
   */
  private function nodeTitleCell(NodeInterface $node): array {
    return $this->linksCell([(string) $node->label() => '/node/' . $node->id()]);
  }

  /**
   * Builds CRUD action links for a node row.
   */
  private function nodeActionsCell(NodeInterface $node): array {
    return $this->linksCell([
      'View' => '/node/' . $node->id(),
      'Edit' => '/node/' . $node->id() . '/edit',
      'Delete' => '/node/' . $node->id() . '/delete',
    ]);
  }

  /**
   * Builds a table cell containing safe, clickable links.
   */
  private function linksCell(array $links): array {
    return [
      'data' => [
        '#markup' => Markup::create($this->linksHtml($links, ' | ')),
      ],
    ];
  }

  /**
   * Builds top action link markup.
   */
  private function actionsMarkup(array $links): array {
    if (!$links) {
      return [];
    }

    return [
      '#markup' => Markup::create('<p class="site-platform-admin-actions">' . $this->linksHtml($links, ' &nbsp; ') . '</p>'),
    ];
  }

  /**
   * Builds escaped anchor HTML from label/path pairs.
   */
  private function linksHtml(array $links, string $separator): string {
    $items = [];
    foreach ($links as $label => $path) {
      $label = trim((string) $label);
      $path = trim((string) $path);
      if ($label === '' || $path === '') {
        continue;
      }
      $items[] = '<a href="' . Html::escape($path) . '">' . Html::escape($label) . '</a>';
    }

    return implode($separator, $items);
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
