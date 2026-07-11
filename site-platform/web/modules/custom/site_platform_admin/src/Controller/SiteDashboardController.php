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
      'toolbar' => $this->buildToolbar(),
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
    $controller = new AdminDashboardController();
    $response = $controller->dashboard();
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
   * Builds dashboard toolbar.
   */
  private function buildToolbar(): array {
    return [
      '#type' => 'container',
      '#attributes' => [
        'class' => [
          'site-dashboard-toolbar',
        ],
      ],
      'search' => [
        '#type' => 'html_tag',
        '#tag' => 'input',
        '#attributes' => [
          'class' => [
            'site-dashboard-toolbar__search',
          ],
          'type' => 'search',
          'placeholder' => $this->t('Search cards, jobs, forms, content...'),
          'aria-label' => $this->t('Search dashboard cards'),
        ],
      ],
      'refresh' => [
        '#type' => 'html_tag',
        '#tag' => 'button',
        '#value' => $this->t('Refresh live data'),
        '#attributes' => [
          'type' => 'button',
          'class' => [
            'site-dashboard-toolbar__refresh',
          ],
        ],
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

    $markup = '<div class="site-dashboard-card__recent"><div class="site-dashboard-card__recent-title">' . $this->t('Recent') . '</div><ul>';

    foreach ($items as $item) {
      $markup .= '<li>' . ($item['title'] ?? '') . '</li>';
    }

    $markup .= '</ul></div>';

    return $markup;
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
