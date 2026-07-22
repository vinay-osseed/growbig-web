<?php

declare(strict_types=1);

namespace Drupal\site_platform_admin\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Link;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Site Platform admin controller.
 */
final class SitePlatformAdminController extends ControllerBase {

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
   * Legacy bundles kept for compatibility, not primary editor workflow.
   */
  private const LEGACY_BUNDLES = [
    'site_menu' => 'Legacy API Menu Wrapper',
    'site_menu_item' => 'Legacy API Menu Item Wrapper',
    'site_form' => 'Legacy API Form Wrapper',
    'site_form_field' => 'Legacy API Form Field Wrapper',
    'site_form_submission' => 'Legacy API Form Submission Fallback',
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
   *
   * @return array<string, mixed>
   *   Render array.
   */
  public function overview(): array {
    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['site-platform-admin-overview']],
      'intro' => [
        '#markup' => '<p><strong>Site Platform is an API-first backend workspace.</strong> Editors should manage real site records through the sections below instead of using the mixed Drupal content table as the primary workflow.</p>',
      ],
      'workflow' => [
        '#type' => 'details',
        '#title' => $this->t('Recommended editor workflow'),
        '#open' => TRUE,
        'content' => [
          '#theme' => 'item_list',
          '#items' => [
            $this->t('Create or review the Site Profile for each brand/site.'),
            $this->t('Create Landing Pages and attach Paragraph components for page structure.'),
            $this->t('Use native Drupal Webform for real forms and submissions.'),
            $this->t('Use media/content sections for reusable assets and API content.'),
            $this->t('Use the API endpoints only as the frontend contract, not as the editing UI.'),
          ],
        ],
      ],
      'sections' => [
        '#type' => 'details',
        '#title' => $this->t('Site Platform sections'),
        '#open' => TRUE,
        'table' => [
          '#type' => 'table',
          '#header' => [$this->t('Section'), $this->t('Purpose'), $this->t('Open')],
          '#rows' => $this->sectionRows(),
        ],
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
        '#title' => $this->t('Backend capability status'),
        '#open' => TRUE,
        'table' => [
          '#type' => 'table',
          '#header' => [$this->t('Capability'), $this->t('Status')],
          '#rows' => $this->moduleRows(),
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
        $this->nodeLink($site),
        $this->fieldValue($site, 'field_site_key'),
        $this->joinedFieldValues($site, 'field_frontend_domains'),
        $this->joinedFieldValues($site, 'field_api_domains'),
        $this->yesNo((bool) $this->fieldValue($site, 'field_is_active')),
        $this->yesNo((bool) $this->fieldValue($site, 'field_is_default')),
        $this->countBySite('site_page', (int) $site->id()),
        $this->editLink($site),
      ];
    }

    return $this->listingPage(
      $this->t('Sites'),
      $this->t('One Site Profile represents one brand/site/domain set. This is the top-level selector used by the frontend APIs.'),
      [
        $this->actionLink($this->t('Add Site Profile'), 'internal:/node/add/site_profile'),
      ],
      [$this->t('Site'), $this->t('Key'), $this->t('Frontend domains'), $this->t('API domains'), $this->t('Active'), $this->t('Default'), $this->t('Pages'), $this->t('Edit')],
      $rows,
      $this->t('No Site Profiles found.'),
    );
  }

  /**
   * Builds the Landing Pages admin page.
   */
  public function pages(Request $request): array {
    $site_id = $this->siteIdFromRequest($request);
    $rows = [];

    foreach ($this->loadNodes('site_page', $site_id) as $page) {
      $rows[] = [
        $this->siteLabelForNode($page),
        $this->nodeLink($page),
        $this->fieldValue($page, 'field_page_key'),
        $this->fieldValue($page, 'field_page_path'),
        $this->fieldValue($page, 'field_page_template'),
        $this->paragraphCount($page, 'field_page_components'),
        $this->fieldValue($page, 'field_seo_title') !== '' ? $this->t('yes') : $this->t('no'),
        $this->editLink($page),
      ];
    }

    return $this->listingPage(
      $this->t('Landing Pages'),
      $this->t('Landing pages hold the frontend route, SEO data, and ordered Paragraph components consumed by the page and route APIs.'),
      [
        $this->actionLink($this->t('Add Landing Page'), 'internal:/node/add/site_page'),
        $this->actionLink($this->t('All Drupal content'), 'internal:/admin/content'),
      ],
      [$this->t('Site'), $this->t('Page'), $this->t('Key'), $this->t('Path'), $this->t('Template'), $this->t('Components'), $this->t('SEO'), $this->t('Edit')],
      $rows,
      $this->t('No Landing Pages found.'),
      $this->siteFilterLinks('site_platform_admin.pages', $site_id),
    );
  }

  /**
   * Builds the reusable content block admin page.
   */
  public function contentBlocks(Request $request): array {
    $site_id = $this->siteIdFromRequest($request);
    $rows = [];

    foreach ($this->loadNodes('site_content_block', $site_id) as $block) {
      $rows[] = [
        $this->siteLabelForNode($block),
        $this->nodeLink($block),
        $this->fieldValue($block, 'field_content_source'),
        $this->fieldValue($block, 'field_content_key'),
        $this->fieldValue($block, 'field_content_variant'),
        $this->yesNo((bool) $this->fieldValue($block, 'field_content_is_active')),
        $this->editLink($block),
      ];
    }

    return $this->listingPage(
      $this->t('Content Blocks'),
      $this->t('Reusable content blocks hold global or shared copy that the frontend can request by source/key.'),
      [
        $this->actionLink($this->t('Add Content Block'), 'internal:/node/add/site_content_block'),
      ],
      [$this->t('Site'), $this->t('Block'), $this->t('Source'), $this->t('Key'), $this->t('Variant'), $this->t('Active'), $this->t('Edit')],
      $rows,
      $this->t('No Content Blocks found.'),
      $this->siteFilterLinks('site_platform_admin.content_blocks', $site_id),
    );
  }

  /**
   * Builds the media asset admin page.
   */
  public function mediaAssets(Request $request): array {
    $site_id = $this->siteIdFromRequest($request);
    $rows = [];

    foreach ($this->loadNodes('site_media_asset', $site_id) as $asset) {
      $rows[] = [
        $this->siteLabelForNode($asset),
        $this->nodeLink($asset),
        $this->fieldValue($asset, 'field_media_key'),
        $this->fieldValue($asset, 'field_media_kind'),
        $this->shortValue($this->fieldValue($asset, 'field_media_url'), 80),
        $this->fieldValue($asset, 'field_media_alt'),
        $this->yesNo((bool) $this->fieldValue($asset, 'field_media_is_active')),
        $this->editLink($asset),
      ];
    }

    return $this->listingPage(
      $this->t('Media Assets'),
      $this->t('Media assets currently store frontend-safe media metadata. Native Drupal Media Library integration remains the preferred next hardening step for binary files.'),
      [
        $this->actionLink($this->t('Add Media Asset'), 'internal:/node/add/site_media_asset'),
        $this->actionLink($this->t('Drupal Files'), 'internal:/admin/content/files'),
      ],
      [$this->t('Site'), $this->t('Asset'), $this->t('Key'), $this->t('Kind'), $this->t('URL'), $this->t('Alt text'), $this->t('Active'), $this->t('Edit')],
      $rows,
      $this->t('No Media Assets found.'),
      $this->siteFilterLinks('site_platform_admin.media_assets', $site_id),
    );
  }

  /**
   * Builds the menu admin page.
   */
  public function menus(Request $request): array {
    $site_id = $this->siteIdFromRequest($request);
    $rows = [];

    foreach ($this->loadNodes('site_menu', $site_id) as $menu) {
      $rows[] = [
        $this->siteLabelForNode($menu),
        $this->nodeLink($menu),
        $this->fieldValue($menu, 'field_menu_key'),
        $this->fieldValue($menu, 'field_menu_label'),
        $this->countMenuItems((int) $menu->id()),
        $this->yesNo((bool) $this->fieldValue($menu, 'field_menu_is_active')),
        $this->editLink($menu),
      ];
    }

    return $this->listingPage(
      $this->t('Menus'),
      $this->t('This page shows the current legacy API menu wrappers. For the long-term editor workflow, Drupal core menus should become the source of truth and the API should normalize them.'),
      [
        $this->actionLink($this->t('Drupal Menus'), 'internal:/admin/structure/menu'),
        $this->actionLink($this->t('Legacy: Add API Menu Wrapper'), 'internal:/node/add/site_menu'),
        $this->actionLink($this->t('Legacy: Add API Menu Item Wrapper'), 'internal:/node/add/site_menu_item'),
      ],
      [$this->t('Site'), $this->t('Menu'), $this->t('Key'), $this->t('Label'), $this->t('Items'), $this->t('Active'), $this->t('Edit')],
      $rows,
      $this->t('No legacy API menu wrappers found.'),
      $this->siteFilterLinks('site_platform_admin.menus', $site_id),
    );
  }

  /**
   * Builds the form admin page.
   */
  public function forms(Request $request): array {
    $site_id = $this->siteIdFromRequest($request);

    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['site-platform-admin-forms']],
      'intro' => [
        '#markup' => '<p><strong>Webform is the primary form system.</strong> Use the native Webform UI for field building, handlers, submission review, spam protection, and exports. Legacy Site Form nodes are listed below only as compatibility wrappers.</p>',
      ],
      'actions' => $this->actionList([
        $this->actionLink($this->t('Drupal Webforms'), 'internal:/admin/structure/webform'),
        $this->actionLink($this->t('Webform Submissions'), 'internal:/admin/structure/webform/submissions/manage'),
        $this->actionLink($this->t('Legacy: Add API Form Wrapper'), 'internal:/node/add/site_form'),
      ]),
      'webforms' => [
        '#type' => 'details',
        '#title' => $this->t('Native Webforms'),
        '#open' => TRUE,
        'table' => [
          '#type' => 'table',
          '#header' => [$this->t('Webform'), $this->t('Machine name'), $this->t('Status'), $this->t('Build'), $this->t('Submissions')],
          '#rows' => $this->webformRows(),
          '#empty' => $this->t('No Webforms found.'),
        ],
      ],
      'legacy' => [
        '#type' => 'details',
        '#title' => $this->t('Legacy API form wrappers'),
        '#open' => TRUE,
        'filter' => $this->siteFilterLinks('site_platform_admin.forms', $site_id),
        'table' => [
          '#type' => 'table',
          '#header' => [$this->t('Site'), $this->t('Wrapper'), $this->t('Key'), $this->t('Label'), $this->t('Active'), $this->t('Edit')],
          '#rows' => $this->legacyFormRows($site_id),
          '#empty' => $this->t('No legacy API form wrappers found.'),
        ],
      ],
    ];
  }

  /**
   * Builds a compatibility records page.
   */
  public function legacyWrappers(Request $request): array {
    $site_id = $this->siteIdFromRequest($request);
    $rows = [];

    foreach (self::LEGACY_BUNDLES as $bundle => $label) {
      foreach ($this->loadNodes($bundle, $site_id) as $node) {
        $rows[] = [
          $label,
          $this->siteLabelForNode($node),
          $this->nodeLink($node),
          $this->legacyKey($node),
          $this->editLink($node),
        ];
      }
    }

    return $this->listingPage(
      $this->t('Legacy API Wrappers'),
      $this->t('These records keep the current API and YAML import compatible. They are not the preferred long-term editor workflow. Use Drupal Webform, Drupal Menu, Paragraphs, and Media Library where possible.'),
      [
        $this->actionLink($this->t('Drupal Webforms'), 'internal:/admin/structure/webform'),
        $this->actionLink($this->t('Drupal Menus'), 'internal:/admin/structure/menu'),
      ],
      [$this->t('Wrapper type'), $this->t('Site'), $this->t('Record'), $this->t('Key'), $this->t('Edit')],
      $rows,
      $this->t('No legacy wrapper records found.'),
      $this->siteFilterLinks('site_platform_admin.legacy_wrappers', $site_id),
    );
  }

  /**
   * Builds section rows for the overview page.
   *
   * @return array<int, array<int, mixed>>
   *   Table rows.
   */
  private function sectionRows(): array {
    return [
      [$this->t('Sites'), $this->t('Brand/site/domain profiles.'), $this->routeLink($this->t('Open Sites'), 'site_platform_admin.sites')],
      [$this->t('Landing Pages'), $this->t('Routes, SEO, templates, and Paragraph components.'), $this->routeLink($this->t('Open Pages'), 'site_platform_admin.pages')],
      [$this->t('Content Blocks'), $this->t('Reusable source/key content exposed by API.'), $this->routeLink($this->t('Open Content Blocks'), 'site_platform_admin.content_blocks')],
      [$this->t('Media Assets'), $this->t('Frontend-safe media metadata exposed by API.'), $this->routeLink($this->t('Open Media Assets'), 'site_platform_admin.media_assets')],
      [$this->t('Forms'), $this->t('Native Webforms first; legacy wrappers only for compatibility.'), $this->routeLink($this->t('Open Forms'), 'site_platform_admin.forms')],
      [$this->t('Menus'), $this->t('Drupal menu target workflow with legacy API wrappers isolated.'), $this->routeLink($this->t('Open Menus'), 'site_platform_admin.menus')],
      [$this->t('Legacy Wrappers'), $this->t('Compatibility records that should not be the main editor workflow.'), $this->routeLink($this->t('Open Legacy Wrappers'), 'site_platform_admin.legacy_wrappers')],
    ];
  }

  /**
   * Builds setup status rows.
   *
   * @return array<int, array<int, string>>
   *   Table rows.
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
   *
   * @return array<int, array<int, string>>
   *   Table rows.
   */
  private function moduleRows(): array {
    $rows = [];
    foreach (self::PLATFORM_MODULES as $module => $label) {
      $rows[] = [$label . ' (' . $module . ')', $this->sitePlatformModuleHandler->moduleExists($module) ? 'enabled' : 'disabled'];
    }

    return $rows;
  }

  /**
   * Creates a listing page render array.
   *
   * @param array<int, mixed> $actions
   *   Action links.
   * @param array<int, mixed> $header
   *   Table header.
   * @param array<int, array<int, mixed>> $rows
   *   Table rows.
   */
  private function listingPage(mixed $title, mixed $description, array $actions, array $header, array $rows, mixed $empty, array $filters = []): array {
    return [
      '#type' => 'container',
      'intro' => [
        '#markup' => '<p>' . $description . '</p>',
      ],
      'actions' => $this->actionList($actions),
      'filters' => $filters,
      'table' => [
        '#type' => 'table',
        '#caption' => $title,
        '#header' => $header,
        '#rows' => $rows,
        '#empty' => $empty,
      ],
    ];
  }

  /**
   * Builds an action link list.
   *
   * @param array<int, mixed> $links
   *   Links.
   */
  private function actionList(array $links): array {
    if ($links === []) {
      return [];
    }

    return [
      '#theme' => 'item_list',
      '#attributes' => ['class' => ['site-platform-admin-actions']],
      '#items' => $links,
    ];
  }

  /**
   * Builds site filter links for a listing page.
   *
   * @return array<string, mixed>
   *   Render array.
   */
  private function siteFilterLinks(string $routeName, ?int $activeSiteId): array {
    $items = [
      $this->routeLink($this->t('All sites'), $routeName),
    ];

    foreach ($this->loadNodes('site_profile') as $site) {
      $site_key = $this->fieldValue($site, 'field_site_key');
      $label = $site_key !== '' ? $site->label() . ' (' . $site_key . ')' : $site->label();
      if ((int) $site->id() === $activeSiteId) {
        $label .= ' - active filter';
      }
      $items[] = Link::fromTextAndUrl($label, Url::fromRoute($routeName, [], ['query' => ['site' => $site_key]]))->toRenderable();
    }

    return [
      '#type' => 'details',
      '#title' => $this->t('Filter by site'),
      '#open' => TRUE,
      'links' => [
        '#theme' => 'item_list',
        '#items' => $items,
      ],
    ];
  }

  /**
   * Reads the active site filter from the request.
   */
  private function siteIdFromRequest(Request $request): ?int {
    $site_key = trim((string) $request->query->get('site', ''));
    if ($site_key === '') {
      return NULL;
    }

    $site = $this->loadSiteByKey($site_key);
    return $site instanceof NodeInterface ? (int) $site->id() : NULL;
  }

  /**
   * Loads a site profile by key.
   */
  private function loadSiteByKey(string $siteKey): ?NodeInterface {
    $storage = $this->sitePlatformEntityTypeManager->getStorage('node');
    $ids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'site_profile')
      ->condition('field_site_key', $siteKey)
      ->range(0, 1)
      ->execute();

    if (!$ids) {
      return NULL;
    }

    $site = $storage->load(reset($ids));
    return $site instanceof NodeInterface ? $site : NULL;
  }

  /**
   * Loads node records by bundle and optional Site Profile id.
   *
   * @return array<int, \Drupal\node\NodeInterface>
   *   Loaded nodes.
   */
  private function loadNodes(string $bundle, ?int $siteProfileId = NULL): array {
    try {
      $storage = $this->sitePlatformEntityTypeManager->getStorage('node');
      $query = $storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('type', $bundle);

      if ($siteProfileId !== NULL && $bundle !== 'site_profile') {
        $query->condition('field_site_profile.target_id', $siteProfileId);
      }

      foreach ($this->sortFieldsForBundle($bundle) as $field => $direction) {
        $query->sort($field . '.value', $direction);
      }
      $query->sort('title', 'ASC');

      $ids = $query->execute();
      if (!$ids) {
        return [];
      }

      return array_values(array_filter(
        $storage->loadMultiple($ids),
        static fn($entity): bool => $entity instanceof NodeInterface,
      ));
    }
    catch (\Throwable) {
      return [];
    }
  }

  /**
   * Sort fields keyed by field name.
   *
   * @return array<string, string>
   *   Field sort definitions.
   */
  private function sortFieldsForBundle(string $bundle): array {
    return match ($bundle) {
      'site_profile' => ['field_site_weight' => 'ASC'],
      'site_page' => ['field_page_weight' => 'ASC'],
      'site_content_block' => ['field_content_weight' => 'ASC'],
      'site_media_asset' => ['field_media_weight' => 'ASC'],
      'site_menu' => ['field_menu_weight' => 'ASC'],
      'site_menu_item' => ['field_menu_weight' => 'ASC'],
      'site_form_field' => ['field_form_field_weight' => 'ASC'],
      default => [],
    };
  }

  /**
   * Counts nodes by bundle and site.
   */
  private function countBySite(string $bundle, int $siteProfileId): int {
    try {
      $ids = $this->sitePlatformEntityTypeManager->getStorage('node')
        ->getQuery()
        ->accessCheck(FALSE)
        ->condition('type', $bundle)
        ->condition('field_site_profile.target_id', $siteProfileId)
        ->execute();

      return count($ids);
    }
    catch (\Throwable) {
      return 0;
    }
  }

  /**
   * Counts menu items for a legacy menu wrapper.
   */
  private function countMenuItems(int $menuId): int {
    try {
      $ids = $this->sitePlatformEntityTypeManager->getStorage('node')
        ->getQuery()
        ->accessCheck(FALSE)
        ->condition('type', 'site_menu_item')
        ->condition('field_site_menu.target_id', $menuId)
        ->execute();

      return count($ids);
    }
    catch (\Throwable) {
      return 0;
    }
  }

  /**
   * Builds native Webform rows.
   *
   * @return array<int, array<int, mixed>>
   *   Table rows.
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
      $is_open = method_exists($webform, 'isOpen') ? (bool) $webform->isOpen() : TRUE;
      $rows[] = [
        $label,
        $id,
        $is_open ? $this->t('Open') : $this->t('Closed'),
        $this->actionLink($this->t('Build'), 'internal:/admin/structure/webform/manage/' . $id),
        $this->actionLink($this->t('Submissions'), 'internal:/admin/structure/webform/manage/' . $id . '/results/submissions'),
      ];
    }

    return $rows;
  }

  /**
   * Builds legacy Site Form rows.
   *
   * @return array<int, array<int, mixed>>
   *   Table rows.
   */
  private function legacyFormRows(?int $siteProfileId): array {
    $rows = [];
    foreach ($this->loadNodes('site_form', $siteProfileId) as $form) {
      $rows[] = [
        $this->siteLabelForNode($form),
        $this->nodeLink($form),
        $this->fieldValue($form, 'field_form_key'),
        $this->fieldValue($form, 'field_form_label'),
        $this->yesNo((bool) $this->fieldValue($form, 'field_form_is_active')),
        $this->editLink($form),
      ];
    }

    return $rows;
  }

  /**
   * Gets a node link.
   */
  private function nodeLink(NodeInterface $node): array {
    return Link::fromTextAndUrl($node->label(), Url::fromUri('internal:/node/' . $node->id() . '/edit'))->toRenderable();
  }

  /**
   * Gets an edit link.
   */
  private function editLink(NodeInterface $node): array {
    return Link::fromTextAndUrl($this->t('Edit'), Url::fromUri('internal:/node/' . $node->id() . '/edit'))->toRenderable();
  }

  /**
   * Builds a route link render array.
   */
  private function routeLink(mixed $label, string $routeName): array {
    return Link::fromTextAndUrl($label, Url::fromRoute($routeName))->toRenderable();
  }

  /**
   * Builds an internal action link render array.
   */
  private function actionLink(mixed $label, string $uri): array {
    return Link::fromTextAndUrl($label, Url::fromUri($uri))->toRenderable();
  }

  /**
   * Gets the first field value for a node.
   */
  private function fieldValue(NodeInterface $node, string $field): string {
    if (!$node->hasField($field) || $node->get($field)->isEmpty()) {
      return '';
    }

    $item = $node->get($field)->first();
    if (!$item) {
      return '';
    }

    if (isset($item->value)) {
      return (string) $item->value;
    }
    if (isset($item->target_id)) {
      return (string) $item->target_id;
    }

    return '';
  }

  /**
   * Gets all scalar values for a node field.
   */
  private function joinedFieldValues(NodeInterface $node, string $field): string {
    if (!$node->hasField($field) || $node->get($field)->isEmpty()) {
      return '';
    }

    $values = [];
    foreach ($node->get($field) as $item) {
      if (isset($item->value) && (string) $item->value !== '') {
        $values[] = (string) $item->value;
      }
    }

    return implode(', ', $values);
  }

  /**
   * Returns a Site Profile label for a node.
   */
  private function siteLabelForNode(NodeInterface $node): string {
    if ($node->bundle() === 'site_profile') {
      return $node->label();
    }

    if (!$node->hasField('field_site_profile') || $node->get('field_site_profile')->isEmpty()) {
      return '';
    }

    $site = $node->get('field_site_profile')->entity;
    if (!$site instanceof NodeInterface) {
      return '';
    }

    $site_key = $this->fieldValue($site, 'field_site_key');
    return $site_key !== '' ? $site->label() . ' (' . $site_key . ')' : $site->label();
  }

  /**
   * Counts referenced paragraphs.
   */
  private function paragraphCount(NodeInterface $node, string $field): int {
    if (!$node->hasField($field) || $node->get($field)->isEmpty()) {
      return 0;
    }

    return $node->get($field)->count();
  }

  /**
   * Resolves a useful key for legacy wrapper records.
   */
  private function legacyKey(NodeInterface $node): string {
    foreach (['field_menu_key', 'field_menu_item_key', 'field_form_key', 'field_form_field_key'] as $field) {
      $value = $this->fieldValue($node, $field);
      if ($value !== '') {
        return $value;
      }
    }

    return '';
  }

  /**
   * Formats a boolean.
   */
  private function yesNo(bool $value): string {
    return $value ? 'yes' : 'no';
  }

  /**
   * Shortens long values for tables.
   */
  private function shortValue(string $value, int $limit): string {
    if (strlen($value) <= $limit) {
      return $value;
    }

    return substr($value, 0, $limit - 3) . '...';
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
