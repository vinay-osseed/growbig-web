<?php

declare(strict_types=1);

namespace Drupal\site_platform_admin\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\node\NodeInterface;
use Drupal\site_platform_api\Controller\AdminDashboardController;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Builds the Site Platform admin dashboard.
 */
final class SiteDashboardController extends ControllerBase {

  /**
   * Constructs the site dashboard controller.
   */
  public function __construct(
    private readonly AdminDashboardController $adminDashboardController,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      AdminDashboardController::create($container)
    );
  }

  /**
   * Returns the dashboard render array.
   */
  public function dashboard(): array {
    $dashboard = $this->getDashboardData();
    $counts = $dashboard['counts'] ?? [];
    $recent = $dashboard['recent']['content'] ?? [];

    return [
      '#attached' => [
        'library' => [
          'site_platform_admin/dashboard',
        ],
      ],
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'site-dashboard',
        ],
      ],
      'intro' => $this->buildIntro(),
      'counts' => $this->buildCounts($counts),
      'cards' => $this->buildCards($dashboard['cards'] ?? [], $counts, $recent),
      'analytics' => $this->buildAnalyticsSummary(),
      'recent' => $this->buildRecentContent($recent),
    ];
  }

  /**
   * Gets dashboard API data through the shared API controller.
   */
  private function getDashboardData(): array {
    $response = $this->adminDashboardController->dashboard();
    $data = json_decode($response->getContent(), TRUE);

    return is_array($data) ? $data : [];
  }

  /**
   * Builds dashboard intro.
   */
  private function buildIntro(): array {
    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'site-dashboard-intro',
        ],
      ],
      'content' => [
        '#markup' => '<div><h2>' . $this->t('Site Dashboard') . '</h2><p>' . $this->t('Manage frontend pages, menus, content, jobs, forms, analytics, and platform data from one place.') . '</p></div>',
      ],
      'badge' => [
        '#markup' => '<div class="site-dashboard-intro__badge">' . $this->t('Live admin overview') . '</div>',
      ],
    ];
  }

  /**
   * Builds dashboard count summary.
   */
  private function buildCounts(array $counts): array {
    $items = [
      'pages' => $this->t('Pages'),
      'services' => $this->t('Services'),
      'partners' => $this->t('Partners'),
      'teamMembers' => $this->t('Team Members'),
      'jobs' => $this->t('Jobs'),
      'contactSubmissions' => $this->t('Contact Enquiries'),
      'jobApplications' => $this->t('Job Applications'),
    ];

    $build = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'site-dashboard-counts',
        ],
      ],
      'title' => [
        '#markup' => '<h3>' . $this->t('Overview') . '</h3>',
      ],
      'grid' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'site-dashboard-counts__grid',
          ],
        ],
      ],
    ];

    foreach ($items as $key => $label) {
      $build['grid'][$key] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'site-dashboard-count',
          ],
        ],
        'value' => [
          '#markup' => '<div class="site-dashboard-count__value">' . (int) ($counts[$key] ?? 0) . '</div>',
        ],
        'label' => [
          '#markup' => '<div class="site-dashboard-count__label">' . $label . '</div>',
        ],
      ];
    }

    return $build;
  }

  /**
   * Builds dashboard cards.
   */
  private function buildCards(array $cards, array $counts, array $recent): array {
    $build = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'site-dashboard-section',
        ],
      ],
      'title' => [
        '#markup' => '<h3>' . $this->t('Quick Actions') . '</h3>',
      ],
      'grid' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'site-dashboard-grid',
          ],
        ],
      ],
    ];

    foreach ($cards as $card) {
      $card_id = (string) ($card['id'] ?? uniqid('card_', TRUE));
      $url = Url::fromUserInput($card['url'] ?? '/admin');
      $link = Link::fromTextAndUrl($this->t('Open'), $url)->toRenderable();
      $link['#attributes']['class'][] = 'site-dashboard-card__button';

      $build['grid'][$card_id] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'site-dashboard-card',
          ],
          'data-dashboard-card' => $card_id,
          'data-dashboard-category' => $this->getCardCategory($card_id),
        ],
        'top' => [
          '#markup' => $this->buildCardTopMarkup($card, $counts),
        ],
        'recent' => [
          '#markup' => $this->buildCardRecentMarkup($card_id, $recent),
        ],
        'link' => $link,
      ];
    }

    return $build;
  }

  /**
   * Builds card top HTML.
   */
  private function buildCardTopMarkup(array $card, array $counts): string {
    $metric = $this->getCardMetric((string) ($card['id'] ?? ''), $counts);

    $markup = '<div class="site-dashboard-card__top">';
    $markup .= '<div>';
    $markup .= '<h4 class="site-dashboard-card__title">' . ($card['title'] ?? '') . '</h4>';
    $markup .= '<p class="site-dashboard-card__description">' . ($card['description'] ?? '') . '</p>';
    $markup .= '</div>';

    if ($metric !== NULL) {
      $markup .= '<div class="site-dashboard-card__metric">' . $metric . '</div>';
    }

    $markup .= '</div>';

    return $markup;
  }

  /**
   * Gets card metric.
   */
  private function getCardMetric(string $card_id, array $counts): ?int {
    $map = [
      'pages' => 'pages',
      'reusable_content' => 'services',
      'jobs' => 'jobs',
      'job_applications' => 'jobApplications',
      'contact_enquiries' => 'contactSubmissions',
    ];

    if (!isset($map[$card_id])) {
      return NULL;
    }

    return (int) ($counts[$map[$card_id]] ?? 0);
  }

  /**
   * Builds per-card recent content markup.
   */
  private function buildCardRecentMarkup(string $card_id, array $recent): string {
    $types = $this->getRecentTypesForCard($card_id);

    if (!$types) {
      return '<div class="site-dashboard-card__recent is-muted">' . $this->t('Ready to manage.') . '</div>';
    }

    $items = [];

    foreach ($recent as $item) {
      if (in_array($item['type'] ?? '', $types, TRUE)) {
        $items[] = $item;
      }

      if (count($items) >= 3) {
        break;
      }
    }

    if (!$items) {
      return '<div class="site-dashboard-card__recent is-muted">' . $this->t('No recent updates yet.') . '</div>';
    }

    $markup = '<div class="site-dashboard-card__recent">';
    $markup .= '<div class="site-dashboard-card__recent-title">' . $this->t('Recent') . '</div>';
    $markup .= '<div class="site-dashboard-card__recent-list">';

    foreach ($items as $item) {
      $title = htmlspecialchars((string) ($item['title'] ?? ''), ENT_QUOTES, 'UTF-8');
      $type = htmlspecialchars((string) ($item['typeLabel'] ?? $item['type'] ?? ''), ENT_QUOTES, 'UTF-8');
      $updated = htmlspecialchars((string) ($item['changedFormatted'] ?? ''), ENT_QUOTES, 'UTF-8');
      $edit_url = htmlspecialchars((string) ($item['editUrl'] ?? '#'), ENT_QUOTES, 'UTF-8');

      $markup .= '<div class="site-dashboard-card__recent-item">';
      $markup .= '<div><strong>' . $title . '</strong><small>' . $type . ' · ' . $updated . '</small></div>';
      $markup .= '<a class="site-dashboard-card__recent-edit" href="' . $edit_url . '">' . $this->t('Edit') . '</a>';
      $markup .= '</div>';
    }

    $markup .= '</div></div>';

    return $markup;
  }

  /**
   * Gets the dashboard filter category for a card.
   */
  private function getCardCategory(string $card_id): string {
    return match ($card_id) {
      'pages', 'menus' => 'pages',
      'reusable_content', 'media' => 'content',
      'jobs', 'job_applications', 'contact_enquiries' => 'jobs',
      'analytics' => 'analytics',
      default => 'system',
    };
  }

  /**
   * Gets recent content types for card.
   */
  private function getRecentTypesForCard(string $card_id): array {
    return match ($card_id) {
      'pages', 'menus' => ['site_page'],
      'reusable_content' => ['service', 'partner', 'team_member'],
      'jobs', 'job_applications' => ['job'],
      default => [],
    };
  }

  /**
   * Builds dummy analytics summary for now.
   */
  private function buildAnalyticsSummary(): array {
    $items = [
      [
        'label' => $this->t('Visitors'),
        'value' => '1.2k',
        'change' => '+18%',
      ],
      [
        'label' => $this->t('Page Views'),
        'value' => '4.8k',
        'change' => '+24%',
      ],
      [
        'label' => $this->t('Career Views'),
        'value' => '860',
        'change' => '+12%',
      ],
      [
        'label' => $this->t('Apply Clicks'),
        'value' => '146',
        'change' => '+9%',
      ],
    ];

    $build = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'site-dashboard-analytics',
        ],
      ],
      'title' => [
        '#markup' => '<div class="site-dashboard-section-heading"><h3>' . $this->t('Analytics Preview') . '</h3><span>' . $this->t('Dummy data until provider is connected') . '</span></div>',
      ],
      'grid' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'site-dashboard-analytics__grid',
          ],
        ],
      ],
    ];

    foreach ($items as $index => $item) {
      $build['grid']['item_' . $index] = [
        '#markup' => '<div class="site-dashboard-analytics__item"><strong>' . $item['value'] . '</strong><span>' . $item['label'] . '</span><em>' . $item['change'] . '</em></div>',
      ];
    }

    return $build;
  }

  /**
   * Builds recent content updates table.
   */
  private function buildRecentContent(array $recent): array {
    $rows = [];

    foreach ($recent as $item) {
      $type = (string) ($item['type'] ?? '');
      $edit_url = Url::fromUserInput($item['editUrl'] ?? '/admin/content');
      $view_url = Url::fromUserInput($item['viewUrl'] ?? '/admin/content');

      $edit = Link::fromTextAndUrl($this->t('Edit'), $edit_url)->toRenderable();
      $edit['#attributes']['class'][] = 'button';
      $edit['#attributes']['class'][] = 'button--small';

      $view = Link::fromTextAndUrl($this->t('View'), $view_url)->toRenderable();
      $view['#attributes']['class'][] = 'button';
      $view['#attributes']['class'][] = 'button--small';

      $rows[] = [
        'data' => [
          'title' => [
            'data' => [
              '#markup' => '<strong>' . htmlspecialchars((string) ($item['title'] ?? ''), ENT_QUOTES, 'UTF-8') . '</strong>',
            ],
          ],
          'type' => [
            'data' => [
              '#markup' => htmlspecialchars((string) ($item['typeLabel'] ?? $type), ENT_QUOTES, 'UTF-8'),
            ],
          ],
          'updated_by' => [
            'data' => [
              '#markup' => htmlspecialchars((string) ($item['updatedBy'] ?? 'Unknown'), ENT_QUOTES, 'UTF-8'),
            ],
          ],
          'updated' => [
            'data' => [
              '#markup' => htmlspecialchars((string) ($item['changedFormatted'] ?? ''), ENT_QUOTES, 'UTF-8'),
            ],
          ],
          'actions' => [
            'data' => [
              '#type' => 'container',
              '#attributes' => [
                'class' => [
                  'site-dashboard-table-actions',
                ],
              ],
              'edit' => $edit,
              'view' => $view,
            ],
          ],
        ],
        'class' => [
          'site-dashboard-recent-row',
        ],
        'data-recent-type' => $type,
      ];
    }

    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'site-dashboard-recent',
        ],
      ],
      'title' => [
        '#markup' => '<h3>' . $this->t('Recent Content Updates') . '</h3>',
      ],
      'filters' => [
        '#markup' => '<div class="site-dashboard-recent-filters">'
          . '<button type="button" class="button is-active" data-recent-filter="">' . $this->t('All') . '</button>'
          . '<button type="button" class="button" data-recent-filter="site_page">' . $this->t('Pages') . '</button>'
          . '<button type="button" class="button" data-recent-filter="service">' . $this->t('Services') . '</button>'
          . '<button type="button" class="button" data-recent-filter="partner">' . $this->t('Partners') . '</button>'
          . '<button type="button" class="button" data-recent-filter="team_member">' . $this->t('Team') . '</button>'
          . '<button type="button" class="button" data-recent-filter="job">' . $this->t('Jobs') . '</button>'
          . '</div>',
      ],
      'table' => [
        '#type' => 'table',
        '#header' => [
          $this->t('Title'),
          $this->t('Category'),
          $this->t('Updated By'),
          $this->t('Updated'),
          $this->t('Actions'),
        ],
        '#rows' => $rows,
        '#empty' => $this->t('No recent content updates found.'),
        '#attributes' => [
          'class' => [
            'site-dashboard-recent-table',
          ],
        ],
      ],
    ];
  }

  /**
   * Builds a dashboard page for frontend menu management.
   */
  public function frontendMenus(): array {
    return $this->buildGroupedContentPage(
      $this->t('Header & Footer Menus'),
      $this->t('Frontend menus are controlled by Site Page menu visibility fields. Edit a page to show or hide it in the header or footer.'),
      [
        'Header Menu Pages' => [
          'bundle' => 'site_page',
          'field' => 'field_show_in_header',
        ],
        'Footer Menu Pages' => [
          'bundle' => 'site_page',
          'field' => 'field_show_in_footer',
        ],
      ],
      '/node/add/site_page'
    );
  }

  /**
   * Builds a dashboard page for reusable content management.
   */
  public function reusableContent(): array {
    return $this->buildGroupedContentPage(
      $this->t('Reusable Content'),
      $this->t('Manage reusable frontend data by category. These items power API lists and page sections.'),
      [
        'Services' => [
          'bundle' => 'service',
        ],
        'Partners' => [
          'bundle' => 'partner',
        ],
        'Team Members' => [
          'bundle' => 'team_member',
        ],
        'Jobs' => [
          'bundle' => 'job',
        ],
      ],
      '/admin/content'
    );
  }

  /**
   * Builds a grouped content admin page.
   */
  private function buildGroupedContentPage($title, $description, array $groups, string $fallback_add_url): array {
    $build = [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'site-dashboard',
          'site-dashboard-admin-list',
        ],
      ],
      'intro' => [
        '#markup' => '<h2>' . $title . '</h2><p>' . $description . '</p>',
      ],
      'back' => Link::fromTextAndUrl($this->t('Back to Site Dashboard'), Url::fromRoute('site_platform_admin.dashboard'))->toRenderable(),
      'groups' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'site-dashboard-admin-groups',
          ],
        ],
      ],
    ];

    foreach ($groups as $label => $definition) {
      $bundle = (string) ($definition['bundle'] ?? '');
      $items = $this->loadDashboardAdminItems($bundle, $definition['field'] ?? NULL);

      $build['groups'][$bundle . '_' . md5((string) $label)] = [
        '#type' => 'details',
        '#title' => $label,
        '#open' => TRUE,
        'add' => Link::fromTextAndUrl($this->t('Add @label', ['@label' => rtrim((string) $label, 's')]), Url::fromUserInput('/node/add/' . $bundle))->toRenderable(),
        'table' => $this->buildDashboardAdminItemsTable($items),
      ];
    }

    return $build;
  }

  /**
   * Loads dashboard admin list items.
   */
  private function loadDashboardAdminItems(string $bundle, ?string $boolean_field = NULL): array {
    $storage = $this->entityTypeManager()->getStorage('node');

    $query = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', $bundle)
      ->sort('changed', 'DESC');

    if ($boolean_field !== NULL) {
      $query->condition($boolean_field, 1);
    }

    if ($bundle !== '') {
      // Site scoping is already applied in the dashboard API. This page keeps
      // the admin list focused on the selected bundle/menu flag.
    }

    $ids = $query->execute();
    $items = [];

    foreach ($storage->loadMultiple($ids) as $node) {
      if (!$node instanceof NodeInterface) {
        continue;
      }

      $changed = (int) $node->getChangedTime();
      $revision_user = method_exists($node, 'getRevisionUser') ? $node->getRevisionUser() : NULL;
      $owner = $revision_user ?: $node->getOwner();

      $items[] = [
        'title' => $node->label(),
        'type' => $node->bundle(),
        'updatedBy' => $owner ? $owner->getDisplayName() : 'Unknown',
        'updated' => date('Y-m-d H:i', $changed),
        'editUrl' => '/node/' . $node->id() . '/edit',
        'viewUrl' => '/node/' . $node->id(),
      ];
    }

    return $items;
  }

  /**
   * Builds an admin items table.
   */
  private function buildDashboardAdminItemsTable(array $items): array {
    $rows = [];

    foreach ($items as $item) {
      $edit = Link::fromTextAndUrl($this->t('Edit'), Url::fromUserInput($item['editUrl']))->toRenderable();
      $view = Link::fromTextAndUrl($this->t('View'), Url::fromUserInput($item['viewUrl']))->toRenderable();

      $rows[] = [
        $item['title'],
        $item['updatedBy'],
        $item['updated'],
        [
          'data' => [
            '#type' => 'container',
            '#attributes' => [
              'class' => [
                'site-dashboard-table-actions',
              ],
            ],
            'edit' => $edit,
            'view' => $view,
          ],
        ],
      ];
    }

    return [
      '#type' => 'table',
      '#header' => [
        $this->t('Title'),
        $this->t('Updated By'),
        $this->t('Updated'),
        $this->t('Actions'),
      ],
      '#rows' => $rows,
      '#empty' => $this->t('No items found.'),
    ];
  }

}
