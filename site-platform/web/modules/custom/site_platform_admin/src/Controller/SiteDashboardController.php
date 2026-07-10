<?php

declare(strict_types=1);

namespace Drupal\site_platform_admin\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\site_platform_api\Controller\AdminDashboardController;

/**
 * Builds the Site Platform admin dashboard.
 */
final class SiteDashboardController extends ControllerBase {

  /**
   * Returns the dashboard render array.
   */
  public function dashboard(): array {
    $dashboard = $this->getDashboardData();

    return [
      '#attached' => [
        'library' => [
          'site_platform_admin/dashboard',
        ],
      ],
      'intro' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'site-dashboard-intro',
          ],
        ],
        'title' => [
          '#markup' => '<h2>' . $this->t('Site Dashboard') . '</h2>',
        ],
        'description' => [
          '#markup' => '<p>' . $this->t('Use this role-aware dashboard to manage frontend pages, menus, content, jobs, forms, and platform data.') . '</p>',
        ],
      ],
      'counts' => $this->buildCounts($dashboard['counts'] ?? []),
      'cards' => $this->buildCards($dashboard['cards'] ?? []),
      'recent' => $this->buildRecentContent($dashboard['recent']['content'] ?? []),
    ];
  }

  /**
   * Gets dashboard API data through the shared API controller.
   */
  private function getDashboardData(): array {
    $controller = new AdminDashboardController();
    $response = $controller->dashboard();
    $data = json_decode($response->getContent(), TRUE);

    return is_array($data) ? $data : [];
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
  private function buildCards(array $cards): array {
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
      $url = Url::fromUserInput($card['url'] ?? '/admin');
      $link = Link::fromTextAndUrl($this->t('Open'), $url)->toRenderable();
      $link['#attributes']['class'][] = 'site-dashboard-card__button';

      $build['grid'][$card['id'] ?? uniqid('card_', TRUE)] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'site-dashboard-card',
          ],
        ],
        'title' => [
          '#markup' => '<h4 class="site-dashboard-card__title">' . ($card['title'] ?? '') . '</h4>',
        ],
        'description' => [
          '#markup' => '<p class="site-dashboard-card__description">' . ($card['description'] ?? '') . '</p>',
        ],
        'link' => $link,
      ];
    }

    return $build;
  }

  /**
   * Builds recent content list.
   */
  private function buildRecentContent(array $items): array {
    $rows = [];

    foreach ($items as $item) {
      $rows[] = [
        $item['title'] ?? '',
        $item['type'] ?? '',
        !empty($item['changed']) ? date('Y-m-d H:i', (int) $item['changed']) : '',
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
      'table' => [
        '#type' => 'table',
        '#header' => [
          $this->t('Title'),
          $this->t('Type'),
          $this->t('Updated'),
        ],
        '#rows' => $rows,
        '#empty' => $this->t('No recent content updates found.'),
      ],
    ];
  }

}
