<?php

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Creates demo Site Page content based on GrowBig frontend mockups.
 *
 * This script is idempotent and safe to re-run.
 */

$node_storage = \Drupal::entityTypeManager()->getStorage('node');

/**
 * Loads the default GrowBig Site Profile.
 */
$get_site_profile = static function () use ($node_storage): ?NodeInterface {
  $ids = $node_storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', 'site_profile')
    ->condition('field_site_key', 'growbig')
    ->range(0, 1)
    ->execute();

  if (!$ids) {
    return NULL;
  }

  $node = $node_storage->load(reset($ids));

  return $node instanceof NodeInterface ? $node : NULL;
};

/**
 * Creates and saves a paragraph.
 */
$create_paragraph = static function (string $type, array $values): Paragraph {
  $paragraph = Paragraph::create([
    'type' => $type,
  ] + $values);

  $paragraph->save();

  return $paragraph;
};

/**
 * Returns entity reference revisions values for paragraphs.
 */
$paragraph_refs = static function (array $paragraphs): array {
  $refs = [];

  foreach ($paragraphs as $paragraph) {
    if ($paragraph instanceof Paragraph) {
      $refs[] = [
        'target_id' => $paragraph->id(),
        'target_revision_id' => $paragraph->getRevisionId(),
      ];
    }
  }

  return $refs;
};

/**
 * Removes old referenced sections from a page before replacing content.
 */
$delete_existing_sections = static function (NodeInterface $page): void {
  if (!$page->hasField('field_sections') || $page->get('field_sections')->isEmpty()) {
    return;
  }

  foreach ($page->get('field_sections')->referencedEntities() as $paragraph) {
    if ($paragraph instanceof Paragraph) {
      $paragraph->delete();
    }
  }
};

/**
 * Creates or updates a Site Page.
 */
$save_page = static function (
  string $page_key,
  string $title,
  string $page_type,
  string $summary,
  array $sections,
  NodeInterface $site_profile,
  array $seo = [],
) use ($node_storage, $paragraph_refs, $delete_existing_sections): NodeInterface {
  $ids = $node_storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', 'site_page')
    ->condition('field_page_key', $page_key)
    ->range(0, 1)
    ->execute();

  if ($ids) {
    $page = $node_storage->load(reset($ids));

    if (!$page instanceof NodeInterface) {
      throw new RuntimeException("Unable to load existing page: {$page_key}");
    }

    $delete_existing_sections($page);
  }
  else {
    $page = Node::create([
      'type' => 'site_page',
    ]);
  }

  $page->setTitle($title);
  $page->set('status', TRUE);
  $page->set('field_page_key', $page_key);
  $page->set('field_page_type', $page_type);
  $page->set('field_summary', $summary);
  $page->set('field_sites', [
    [
      'target_id' => $site_profile->id(),
    ],
  ]);
  $page->set('field_sections', $paragraph_refs($sections));

  foreach ($seo as $field_name => $value) {
    if ($page->hasField($field_name)) {
      $page->set($field_name, $value);
    }
  }

  $page->save();

  print "Saved Site Page: {$title} ({$page_key}) node {$page->id()}\n";

  return $page;
};

$site_profile = $get_site_profile();

if (!$site_profile instanceof NodeInterface) {
  throw new RuntimeException('Default GrowBig Site Profile was not found.');
}

/**
 * Home page sections.
 */
$home_hero = $create_paragraph('hero_section', [
  'field_badge' => 'Premium IT Solutions - Est. 2020',
  'field_heading' => 'Building Digital Solutions That Drive Business Growth',
  'field_highlight_text' => 'Solutions',
  'field_description' => 'GrowBig Technologies LLP delivers modern websites, mobile applications, cloud solutions, digital transformation, AI-powered software, branding, and technology consulting for startups and enterprises.',
  'field_primary_button_text' => 'Start Your Project',
  'field_primary_button_link' => [
    'uri' => 'internal:/contact',
  ],
  'field_secondary_button_text' => 'View Services',
  'field_secondary_button_link' => [
    'uri' => 'internal:/services',
  ],
  'field_layout_variant' => 'split_image_right',
  'field_background_style' => 'dark_grid',
]);

$home_stats_items = [
  $create_paragraph('stats_item', [
    'field_title' => 'Projects Delivered',
    'field_count' => 150,
    'field_suffix' => '+',
    'field_icon' => 'briefcase',
    'field_display_order' => 10,
  ]),
  $create_paragraph('stats_item', [
    'field_title' => 'Happy Clients',
    'field_count' => 50,
    'field_suffix' => '+',
    'field_icon' => 'users',
    'field_display_order' => 20,
  ]),
  $create_paragraph('stats_item', [
    'field_title' => 'Years Experience',
    'field_count' => 6,
    'field_suffix' => '+',
    'field_icon' => 'calendar',
    'field_display_order' => 30,
  ]),
];

$home_stats = $create_paragraph('stats_section', [
  'field_stats_items' => $paragraph_refs($home_stats_items),
]);

$service_cards = [
  $create_paragraph('card_item', [
    'field_title' => 'Website Development',
    'field_description' => 'Pixel-perfect, high-performance websites built with modern frameworks — from landing pages to enterprise portals.',
    'field_icon' => 'globe',
    'field_accent_color' => '#2563eb',
    'field_link_text' => 'Learn More',
    'field_link_url' => [
      'uri' => 'internal:/services/website-development',
    ],
    'field_display_order' => 10,
    'field_is_active' => TRUE,
  ]),
  $create_paragraph('card_item', [
    'field_title' => 'Mobile App Development',
    'field_description' => 'Native and cross-platform apps for iOS and Android, crafted with seamless UX and robust backend integration.',
    'field_icon' => 'smartphone',
    'field_accent_color' => '#f59e0b',
    'field_link_text' => 'Learn More',
    'field_link_url' => [
      'uri' => 'internal:/services/mobile-app-development',
    ],
    'field_display_order' => 20,
    'field_is_active' => TRUE,
  ]),
  $create_paragraph('card_item', [
    'field_title' => 'Custom Software',
    'field_description' => 'Tailored software solutions engineered around your business logic, workflows, and scaling requirements.',
    'field_icon' => 'code',
    'field_accent_color' => '#8b5cf6',
    'field_link_text' => 'Learn More',
    'field_link_url' => [
      'uri' => 'internal:/services/custom-software',
    ],
    'field_display_order' => 30,
    'field_is_active' => TRUE,
  ]),
];

$home_services = $create_paragraph('card_grid_section', [
  'field_badge' => 'What We Do',
  'field_heading' => 'Our Services',
  'field_description' => 'We build scalable digital products that transform how businesses connect with their customers.',
  'field_cards' => $paragraph_refs($service_cards),
  'field_layout_variant' => 'default',
]);

$home_partners = $create_paragraph('content_list_section', [
  'field_badge' => 'Ecosystem',
  'field_heading' => 'Our Trusted Partners',
  'field_description' => 'We collaborate with industry-leading technology partners to deliver best-in-class solutions.',
  'field_content_source' => 'partners',
  'field_limit' => 6,
  'field_featured_only' => TRUE,
  'field_layout_variant' => 'default',
]);

$save_page('home', 'Home', 'home', 'GrowBig homepage composed from reusable sections.', [
  $home_hero,
  $home_stats,
  $home_services,
  $home_partners,
], $site_profile, [
  'field_meta_title' => 'GrowBig Technologies LLP | Digital Solutions That Drive Business Growth',
  'field_meta_description' => 'GrowBig Technologies LLP delivers websites, mobile apps, custom software, cloud solutions, and digital consulting.',
  'field_og_title' => 'GrowBig Technologies LLP',
  'field_og_description' => 'Building digital solutions that drive business growth.',
  'field_robots' => 'index_follow',
]);

/**
 * About page sections.
 */
$about_hero = $create_paragraph('hero_section', [
  'field_badge' => 'Est. 2020 - Sawantwadi, India',
  'field_heading' => 'We Are GrowBig',
  'field_highlight_text' => 'GrowBig',
  'field_description' => 'A passionate team of engineers, designers, and strategists building digital products that matter — for startups, SMEs, and enterprises across India.',
  'field_layout_variant' => 'centered',
  'field_background_style' => 'dark_grid',
]);

$mission_cards = [
  $create_paragraph('card_item', [
    'field_title' => 'Our Mission',
    'field_description' => 'To deliver world-class digital solutions that empower businesses — big and small — to compete, grow, and thrive in a technology-first world.',
    'field_icon' => 'target',
    'field_accent_color' => '#2563eb',
    'field_display_order' => 10,
    'field_is_active' => TRUE,
  ]),
  $create_paragraph('card_item', [
    'field_title' => 'Our Vision',
    'field_description' => 'To be the most trusted technology partner for emerging businesses across India — known for craftsmanship, integrity, and lasting impact.',
    'field_icon' => 'eye',
    'field_accent_color' => '#f59e0b',
    'field_display_order' => 20,
    'field_is_active' => TRUE,
  ]),
];

$mission_section = $create_paragraph('card_grid_section', [
  'field_badge' => 'Purpose',
  'field_heading' => 'Mission & Vision',
  'field_description' => 'The principles that guide every line of code, every design decision, and every client conversation.',
  'field_cards' => $paragraph_refs($mission_cards),
  'field_layout_variant' => 'default',
]);

$value_cards = [
  [
    'Innovation First',
    'We push boundaries and embrace emerging technologies to deliver forward-thinking solutions.',
    'lightbulb',
    '#f59e0b',
  ],
  [
    'Client-Centric',
    'Every decision we make is driven by the outcomes and satisfaction of our clients.',
    'heart',
    '#ef4444',
  ],
  [
    'Integrity',
    'We operate with full transparency, honest communication, and unwavering ethical standards.',
    'shield',
    '#2563eb',
  ],
  [
    'Agile Excellence',
    'Speed without sacrifice. We ship fast and maintain quality through rigorous agile practices.',
    'zap',
    '#10b981',
  ],
  [
    'True Partnership',
    'We do not just take briefs — we embed with your team and share ownership of the outcome.',
    'handshake',
    '#8b5cf6',
  ],
  [
    'Continuous Growth',
    'Learning never stops. We invest in our people, processes, and tools to stay at the cutting edge.',
    'award',
    '#f59e0b',
  ],
];

$value_paragraphs = [];

foreach ($value_cards as $index => $card) {
  $value_paragraphs[] = $create_paragraph('card_item', [
    'field_title' => $card[0],
    'field_description' => $card[1],
    'field_icon' => $card[2],
    'field_accent_color' => $card[3],
    'field_display_order' => ($index + 1) * 10,
    'field_is_active' => TRUE,
  ]);
}

$values_section = $create_paragraph('card_grid_section', [
  'field_badge' => 'What We Stand For',
  'field_heading' => 'Our Core Values',
  'field_description' => 'Six principles that shape how we think, work, and deliver for every client.',
  'field_cards' => $paragraph_refs($value_paragraphs),
  'field_layout_variant' => 'default',
]);

$leadership_section = $create_paragraph('content_list_section', [
  'field_badge' => 'The Team',
  'field_heading' => 'Our Leadership',
  'field_description' => 'The people steering strategy, technology, and culture at GrowBig.',
  'field_content_source' => 'team',
  'field_limit' => 4,
  'field_featured_only' => TRUE,
  'field_layout_variant' => 'default',
]);

$about_cta = $create_paragraph('cta_section', [
  'field_heading' => 'Want to join our growing team?',
  'field_description' => 'We are always looking for talented people who share our passion for great software.',
  'field_primary_button_text' => 'View Open Roles',
  'field_primary_button_link' => [
    'uri' => 'internal:/careers',
  ],
  'field_layout_variant' => 'centered',
  'field_background_style' => 'dark',
]);

$save_page('about', 'About', 'about', 'About GrowBig, mission, values, and leadership.', [
  $about_hero,
  $mission_section,
  $values_section,
  $leadership_section,
  $about_cta,
], $site_profile, [
  'field_meta_title' => 'About GrowBig Technologies LLP',
  'field_meta_description' => 'Learn about GrowBig Technologies LLP, our mission, vision, values, and leadership.',
  'field_og_title' => 'About GrowBig',
  'field_og_description' => 'A passionate team building digital products that matter.',
  'field_robots' => 'index_follow',
]);

print "Demo Site Page content creation complete.\n";
