<?php

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Creates or updates the default Site Profile content item.
 */

$storage = \Drupal::entityTypeManager()->getStorage('node');

$existing = $storage->getQuery()
  ->accessCheck(FALSE)
  ->condition('type', 'site_profile')
  ->condition('field_site_key', 'growbig')
  ->range(0, 1)
  ->execute();

$values = [
  'type' => 'site_profile',
  'title' => 'GrowBig Technologies LLP',
  'status' => TRUE,
  'field_site_key' => 'growbig',
  'field_site_short_name' => 'GrowBig',
  'field_tagline' => 'Building digital excellence for startups and enterprises across India and beyond.',
  'field_description' => 'GrowBig Technologies LLP delivers modern websites, mobile applications, cloud solutions, digital transformation, AI-powered software, branding, and technology consulting for startups and enterprises.',
  'field_primary_domain' => [
    'uri' => 'https://growbig-web.ddev.site',
  ],
  'field_ui_domain' => [
    'uri' => 'https://ui.growbig-web.ddev.site',
  ],
  'field_admin_domain' => [
    'uri' => 'https://admin.growbig-web.ddev.site',
  ],
  'field_api_domain' => [
    'uri' => 'https://api.growbig-web.ddev.site',
  ],
  'field_contact_email' => 'hello@growbigtech.in',
  'field_contact_phone' => '+91 00000 00000',
  'field_alternate_phone' => '+91 98765 43210',
  'field_website' => [
    'uri' => 'https://growbig-web.ddev.site',
  ],
  'field_contact_address' => 'Narayan Arcade, 1st Floor Above Axis Bank Near ST Stand, Sawantwadi',
  'field_city' => 'Sawantwadi',
  'field_state' => 'Maharashtra',
  'field_country' => 'India',
  'field_postal_code' => '',
  'field_working_hours' => 'Mon - Sat: 9:00 AM - 7:00 PM' . PHP_EOL . 'Sunday: Closed',
  'field_google_map_link' => [
    'uri' => 'https://maps.google.com/?q=Sawantwadi,Maharashtra,India',
  ],
  'field_map_latitude' => '15.9046',
  'field_map_longitude' => '73.8213',
  'field_footer_description' => 'Building digital excellence for startups and enterprises across India and beyond.',
  'field_footer_copyright' => '© 2026 GrowBig Technologies LLP. All Rights Reserved.',
  'field_default_meta_title' => 'GrowBig Technologies LLP',
  'field_default_meta_description' => 'GrowBig Technologies LLP delivers websites, mobile apps, software, cloud solutions, and digital consulting for growing businesses.',
  'field_theme_color' => '#0f172a',
  'field_main_menu' => 'main',
  'field_footer_quick_links_menu' => 'footer',
  'field_footer_services_menu' => 'footer-services',
  'field_is_default' => TRUE,
  'field_is_active' => TRUE,
];

if ($existing) {
  $node = $storage->load(reset($existing));

  if ($node instanceof Node) {
    foreach ($values as $field_name => $value) {
      if ($field_name === 'type') {
        continue;
      }

      if ($node->hasField($field_name)) {
        $node->set($field_name, $value);
      }
    }

    if ($node->hasField('field_social_links')) {
      $social_links = [
        [
          'platform' => 'Facebook',
          'url' => 'https://www.facebook.com/',
          'icon' => 'facebook',
          'order' => 10,
        ],
        [
          'platform' => 'LinkedIn',
          'url' => 'https://www.linkedin.com/',
          'icon' => 'linkedin',
          'order' => 20,
        ],
        [
          'platform' => 'Instagram',
          'url' => 'https://www.instagram.com/',
          'icon' => 'instagram',
          'order' => 30,
        ],
        [
          'platform' => 'GitHub',
          'url' => 'https://github.com/',
          'icon' => 'github',
          'order' => 40,
        ],
      ];

      $paragraph_values = [];

      foreach ($social_links as $social_link) {
        $paragraph = Paragraph::create([
          'type' => 'social_link',
          'field_platform_name' => $social_link['platform'],
          'field_profile_url' => [
            'uri' => $social_link['url'],
          ],
          'field_icon' => $social_link['icon'],
          'field_display_order' => $social_link['order'],
          'field_is_active' => TRUE,
        ]);

        $paragraph->save();

        $paragraph_values[] = [
          'target_id' => $paragraph->id(),
          'target_revision_id' => $paragraph->getRevisionId(),
        ];
      }

      $node->set('field_social_links', $paragraph_values);
    }

    $node->save();

    print "Updated default Site Profile node: {$node->id()}\n";
  }

  return;
}

$node = Node::create($values);

if ($node->hasField('field_social_links')) {
  $social_links = [
    [
      'platform' => 'Facebook',
      'url' => 'https://www.facebook.com/',
      'icon' => 'facebook',
      'order' => 10,
    ],
    [
      'platform' => 'LinkedIn',
      'url' => 'https://www.linkedin.com/',
      'icon' => 'linkedin',
      'order' => 20,
    ],
    [
      'platform' => 'Instagram',
      'url' => 'https://www.instagram.com/',
      'icon' => 'instagram',
      'order' => 30,
    ],
    [
      'platform' => 'GitHub',
      'url' => 'https://github.com/',
      'icon' => 'github',
      'order' => 40,
    ],
  ];

  $paragraph_values = [];

  foreach ($social_links as $social_link) {
    $paragraph = Paragraph::create([
      'type' => 'social_link',
      'field_platform_name' => $social_link['platform'],
      'field_profile_url' => [
        'uri' => $social_link['url'],
      ],
      'field_icon' => $social_link['icon'],
      'field_display_order' => $social_link['order'],
      'field_is_active' => TRUE,
    ]);

    $paragraph->save();

    $paragraph_values[] = [
      'target_id' => $paragraph->id(),
      'target_revision_id' => $paragraph->getRevisionId(),
    ];
  }

  $node->set('field_social_links', $paragraph_values);
}

$node->save();

print "Created default Site Profile node: {$node->id()}\n";
