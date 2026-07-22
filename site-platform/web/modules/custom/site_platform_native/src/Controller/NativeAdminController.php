<?php

namespace Drupal\site_platform_native\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Drupal-native Site Platform admin guide pages.
 */
final class NativeAdminController extends ControllerBase {

  /**
   * Start-here onboarding guide.
   */
  public function start(): array {
    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['site-platform-admin-overview']],
      'intro' => ['#markup' => '<p><strong>Start here.</strong> This is the Drupal-native workflow for building one or many sites from the same backend.</p>'],
      'workflow' => [
        '#type' => 'details',
        '#title' => $this->t('Simple editor workflow'),
        '#open' => TRUE,
        'items' => [
          '#theme' => 'item_list',
          '#items' => [
            $this->t('Create or select a Site Profile and map it to a Domain.'),
            $this->t('Upload logos, icons, hero images, partner logos, and files in Media Library.'),
            $this->t('Build real forms in Webform.'),
            $this->t('Create Landing Pages and add page sections with the page builder.'),
            $this->t('Place pages into Drupal core menus using Menu UI.'),
            $this->t('Use Site Data Sets for special business data like ISP plans, locations, coverage areas, pricing plans, or careers.'),
            $this->t('Give frontend developers /api/v2/bootstrap and /api/v2/pages/{slug}; avoid many small requests.'),
          ],
        ],
      ],
      'links' => [
        '#type' => 'table',
        '#header' => ['Task', 'Open'],
        '#rows' => [
          ['Sites / domains', ['data' => ['#markup' => '<a href="/admin/site-platform/sites">Open Sites</a>']]],
          ['Media Library', ['data' => ['#markup' => '<a href="/admin/content/media">Open Media</a>']]],
          ['Webforms', ['data' => ['#markup' => '<a href="/admin/structure/webform">Open Webforms</a>']]],
          ['Landing Pages', ['data' => ['#markup' => '<a href="/admin/site-platform/pages">Open Pages</a>']]],
          ['Drupal Menus', ['data' => ['#markup' => '<a href="/admin/structure/menu">Open Menus</a>']]],
          ['Data Sets', ['data' => ['#markup' => '<a href="/node/add/site_data_set">Add Data Set</a>']]],
          ['API guide', ['data' => ['#markup' => '<a href="/admin/site-platform/api-guide">Open API Guide</a>']]],
        ],
      ],
    ];
  }

  /**
   * API guide page.
   */
  public function apiGuide(): array {
    return [
      '#type' => 'container',
      '#attributes' => ['class' => ['site-platform-admin-overview']],
      'intro' => ['#markup' => '<p><strong>Frontend API guide.</strong> These endpoints are intentionally coarse-grained to minimize frontend requests.</p>'],
      'table' => [
        '#type' => 'table',
        '#header' => ['Endpoint', 'Purpose'],
        '#rows' => [
          ['/api/v2/bootstrap?site=growbig', 'Site profile, brand, contact, analytics, and common menus.'],
          ['/api/v2/pages/home?site=growbig', 'One complete page payload: page, SEO, sections, media, and embedded references.'],
          ['/api/v2/navigation?site=growbig&menu=main', 'Drupal core menu payload.'],
          ['/api/v2/forms/contact?site=growbig', 'Webform schema payload.'],
          ['/api/v2/datasets/plans?site=growbig', 'Flexible structured dataset payload for plans, locations, coverage areas, pricing, etc.'],
          ['/api/v2/careers?site=growbig', 'Career-focused dataset/list endpoint.'],
        ],
      ],
    ];
  }

}
