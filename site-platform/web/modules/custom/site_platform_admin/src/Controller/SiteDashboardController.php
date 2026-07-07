<?php

declare(strict_types=1);

namespace Drupal\site_platform_admin\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

/**
 * Builds the Site Platform content admin dashboard.
 */
final class SiteDashboardController extends ControllerBase {

  /**
   * Returns the dashboard render array.
   */
  public function dashboard(): array {
    $cards = [
      [
        'title' => 'Site Settings',
        'description' => 'Edit company settings, domains, branding, contact details, social links, footer details, and default SEO.',
        'url' => Url::fromUri('internal:/admin/content', [
          'query' => [
            'type' => 'site_profile',
          ],
        ]),
        'status' => 'Ready',
      ],
      [
        'title' => 'Menus',
        'description' => 'Manage header, footer, quick links, and service links using Drupal menus.',
        'url' => Url::fromUri('internal:/admin/structure/menu'),
        'status' => 'Ready',
      ],
      [
        'title' => 'Content',
        'description' => 'View and manage all Drupal content items.',
        'url' => Url::fromUri('internal:/admin/content'),
        'status' => 'Ready',
      ],
      [
        'title' => 'Media Library',
        'description' => 'Manage uploaded logos, icons, images, favicons, and social sharing images.',
        'url' => Url::fromUri('internal:/admin/content/media'),
        'status' => 'Ready',
      ],
      [
        'title' => 'API Docs',
        'description' => 'Open the Swagger/OpenAPI documentation for frontend developers.',
        'url' => Url::fromUri('https://vinay-osseed.github.io/growbig-web/api/'),
        'status' => 'Ready',
      ],
      [
        'title' => 'Pages',
        'description' => 'Create and edit reusable frontend pages with structured sections.',
        'url' => Url::fromUri('internal:/admin/content', [
          'query' => [
            'type' => 'site_page',
          ],
        ]),
        'status' => 'Ready',
      ],
      [
        'title' => 'Services',
        'description' => 'Reusable service cards and service detail content. This will be added after the Page model.',
        'url' => NULL,
        'status' => 'Coming Soon',
      ],
      [
        'title' => 'Partners',
        'description' => 'Partner logo and partner information management. This will be added after the Page model.',
        'url' => NULL,
        'status' => 'Coming Soon',
      ],
      [
        'title' => 'Leadership',
        'description' => 'Leadership and team member management. This will be added after the Page model.',
        'url' => NULL,
        'status' => 'Coming Soon',
      ],
    ];

    $build = [
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
          '#markup' => '<h2>Reusable Site Platform Dashboard</h2>',
        ],
        'description' => [
          '#markup' => '<p>Use this dashboard to manage the major website components exposed to the frontend UI.</p>',
        ],
      ],
      'cards' => [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'site-dashboard-grid',
          ],
        ],
      ],
    ];

    foreach ($cards as $index => $card) {
      $card_build = [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'site-dashboard-card',
          ],
        ],
        'status' => [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#value' => $card['status'],
          '#attributes' => [
            'class' => [
              'site-dashboard-card__status',
              $card['status'] === 'Ready' ? 'is-ready' : 'is-coming-soon',
            ],
          ],
        ],
        'title' => [
          '#type' => 'html_tag',
          '#tag' => 'h3',
          '#value' => $card['title'],
          '#attributes' => [
            'class' => [
              'site-dashboard-card__title',
            ],
          ],
        ],
        'description' => [
          '#type' => 'html_tag',
          '#tag' => 'p',
          '#value' => $card['description'],
          '#attributes' => [
            'class' => [
              'site-dashboard-card__description',
            ],
          ],
        ],
      ];

      if ($card['url'] instanceof Url) {
        $card_build['link'] = [
          '#type' => 'link',
          '#title' => $this->t('Open'),
          '#url' => $card['url'],
          '#attributes' => [
            'class' => [
              'button',
              'button--primary',
              'site-dashboard-card__button',
            ],
          ],
        ];
      }
      else {
        $card_build['link'] = [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#value' => $this->t('Planned'),
          '#attributes' => [
            'class' => [
              'button',
              'site-dashboard-card__button',
              'is-disabled',
            ],
          ],
        ];
      }

      $build['cards']['card_' . $index] = $card_build;
    }

    return $build;
  }

}
