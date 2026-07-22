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
      'intro' => ['#markup' => '<p><strong>Start here.</strong> This is the editor home for building and managing every site from the Drupal-native backend.</p>'],
      'workflow' => [
        '#type' => 'details',
        '#title' => $this->t('Simple editor workflow'),
        '#open' => TRUE,
        'items' => [
          '#theme' => 'item_list',
          '#items' => [
            $this->t('Sites and Domains: create/select a Site Profile and connect it to Domain, Domain Alias, Domain Source, or Domain Content where needed.'),
            $this->t('Media: upload logos, favicons, icons, hero images, partner logos, documents, and reusable images in Media Library.'),
            $this->t('Forms: build all forms with Webform and expose schemas through the form APIs.'),
            $this->t('Pages: create Landing Pages and add page sections with the page builder.'),
            $this->t('Menus: place pages into Drupal core menus using Menu UI, not custom menu wrapper content.'),
            $this->t('Data Sets: add site-specific structured data such as ISP plans, coverage areas, pricing tables, locations, product plans, careers, and benefits.'),
            $this->t('APIs: give frontend developers bootstrap, page index, page detail, navigation, form index, and dataset index endpoints.'),
          ],
        ],
      ],
      'links' => [
        '#type' => 'table',
        '#header' => ['Step', 'Use this for', 'Open'],
        '#rows' => [
          ['1. Sites', 'Brand/site profile, logo, favicon, contact, socials, analytics.', ['data' => ['#markup' => '<a href="/admin/site-platform/sites">Open Sites</a>']]],
          ['2. Domains', 'Domain, aliases, domain config, source/content mapping.', ['data' => ['#markup' => '<a href="/admin/config/domain">Open Domain settings</a>']]],
          ['3. Media', 'Images, logos, files, icons, partner assets.', ['data' => ['#markup' => '<a href="/admin/content/media">Open Media Library</a>']]],
          ['4. Webforms', 'Contact, lead, career, inquiry, and application forms.', ['data' => ['#markup' => '<a href="/admin/structure/webform">Open Webforms</a>']]],
          ['5. Landing Pages', 'Home, About, Services, Careers, Contact, landing pages.', ['data' => ['#markup' => '<a href="/admin/site-platform/pages">Open Pages</a>']]],
          ['6. Menus', 'Main/footer navigation from Drupal core Menu UI.', ['data' => ['#markup' => '<a href="/admin/structure/menu">Open Menus</a>']]],
          ['7. Data Sets', 'Plans, areas, pricing, services, partners, careers, locations.', ['data' => ['#markup' => '<a href="/node/add/site_data_set">Add Data Set</a>']]],
          ['8. API Guide', 'Clean frontend API contract and request order.', ['data' => ['#markup' => '<a href="/admin/site-platform/api-guide">Open API Guide</a>']]],
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
      'intro' => ['#markup' => '<p><strong>Frontend API guide.</strong> Use index endpoints for listings and detail endpoints only when a detail view is needed.</p>'],
      'table' => [
        '#type' => 'table',
        '#header' => ['Endpoint', 'Purpose'],
        '#rows' => [
          ['/api/v2/bootstrap?site=growbig', 'Site profile, Domain mapping, brand, contact, analytics, feature availability, and common menus.'],
          ['/api/v2/pages?site=growbig', 'Page index. Use this to fetch all published pages for routing, page cards, and static generation.'],
          ['/api/v2/pages?site=growbig&include=full', 'Optional full page index with sections included. Use for small sites or static builds.'],
          ['/api/v2/pages/home?site=growbig', 'One complete page detail payload: page, SEO, sections, media, and embedded references.'],
          ['/api/v2/navigation?site=growbig&menu=main', 'Drupal core menu payload.'],
          ['/api/v2/forms?site=growbig', 'Webform index for frontend form discovery.'],
          ['/api/v2/forms/contact?site=growbig', 'Webform schema payload.'],
          ['/api/v2/datasets?site=growbig', 'Dataset index for flexible structured data discovery.'],
          ['/api/v2/datasets/plans?site=growbig', 'Flexible structured dataset payload for plans, locations, coverage areas, pricing, etc.'],
          ['/api/v2/careers?site=growbig', 'Career-focused dataset/list endpoint.'],
        ],
      ],
    ];
  }

}
