<?php

declare(strict_types=1);

use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;

/**
 * Creates demo Services, Partners, and Team Members.
 *
 * This script is idempotent and safe to re-run.
 */

$node_storage = \Drupal::entityTypeManager()->getStorage('node');

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

$save_node = static function (
  string $type,
  string $key_field,
  string $key,
  string $title,
  array $values,
  NodeInterface $site_profile
) use ($node_storage): NodeInterface {
  $ids = $node_storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', $type)
    ->condition($key_field, $key)
    ->range(0, 1)
    ->execute();

  if ($ids) {
    $node = $node_storage->load(reset($ids));

    if (!$node instanceof NodeInterface) {
      throw new RuntimeException("Unable to load {$type}: {$key}");
    }
  }
  else {
    $node = Node::create([
      'type' => $type,
    ]);
  }

  $node->setTitle($title);
  $node->set('status', TRUE);
  $node->set($key_field, $key);
  $node->set('field_sites', [
    [
      'target_id' => $site_profile->id(),
    ],
  ]);

  foreach ($values as $field_name => $value) {
    if ($node->hasField($field_name)) {
      $node->set($field_name, $value);
    }
  }

  $node->save();

  print "Saved {$type}: {$title} ({$key}) node {$node->id()}\n";

  return $node;
};

$site_profile = $get_site_profile();

if (!$site_profile instanceof NodeInterface) {
  throw new RuntimeException('Default GrowBig Site Profile was not found.');
}

$services = [
  [
    'key' => 'website-development',
    'title' => 'Website Development',
    'summary' => 'Modern, responsive, high-performance websites built for growth, SEO, and conversion.',
    'icon' => 'globe',
    'color' => '#2563eb',
    'url' => 'internal:/services/website-development',
    'order' => 10,
  ],
  [
    'key' => 'mobile-app-development',
    'title' => 'Mobile App Development',
    'summary' => 'Native and cross-platform mobile applications for iOS and Android.',
    'icon' => 'smartphone',
    'color' => '#f59e0b',
    'url' => 'internal:/services/mobile-app-development',
    'order' => 20,
  ],
  [
    'key' => 'custom-software',
    'title' => 'Custom Software',
    'summary' => 'Tailored software built around your business workflows, data, and scaling needs.',
    'icon' => 'code',
    'color' => '#8b5cf6',
    'url' => 'internal:/services/custom-software',
    'order' => 30,
  ],
  [
    'key' => 'cloud-solutions',
    'title' => 'Cloud Solutions',
    'summary' => 'Cloud architecture, deployment, optimization, and DevOps support for modern platforms.',
    'icon' => 'cloud',
    'color' => '#10b981',
    'url' => 'internal:/services/cloud-solutions',
    'order' => 40,
  ],
  [
    'key' => 'digital-transformation',
    'title' => 'Digital Transformation',
    'summary' => 'Technology consulting and implementation support for process automation and growth.',
    'icon' => 'refresh-cw',
    'color' => '#ef4444',
    'url' => 'internal:/services/digital-transformation',
    'order' => 50,
  ],
  [
    'key' => 'ai-solutions',
    'title' => 'AI Solutions',
    'summary' => 'AI-powered tools, automation, and intelligent workflows for business productivity.',
    'icon' => 'sparkles',
    'color' => '#6366f1',
    'url' => 'internal:/services/ai-solutions',
    'order' => 60,
  ],
];

foreach ($services as $service) {
  $save_node('service', 'field_service_key', $service['key'], $service['title'], [
    'field_summary' => $service['summary'],
    'field_icon' => $service['icon'],
    'field_accent_color' => $service['color'],
    'field_link_url' => [
      'uri' => $service['url'],
    ],
    'field_display_order' => $service['order'],
    'field_is_featured' => TRUE,
    'field_is_active' => TRUE,
    'field_meta_title' => $service['title'] . ' | GrowBig Technologies LLP',
    'field_meta_description' => $service['summary'],
  ], $site_profile);
}

$partners = [
  [
    'key' => 'aws',
    'title' => 'Amazon Web Services',
    'summary' => 'Cloud infrastructure and deployment platform partner.',
    'website' => 'https://aws.amazon.com',
    'order' => 10,
  ],
  [
    'key' => 'google-cloud',
    'title' => 'Google Cloud',
    'summary' => 'Cloud, analytics, and AI infrastructure partner.',
    'website' => 'https://cloud.google.com',
    'order' => 20,
  ],
  [
    'key' => 'microsoft',
    'title' => 'Microsoft',
    'summary' => 'Productivity, cloud, and enterprise technology partner.',
    'website' => 'https://www.microsoft.com',
    'order' => 30,
  ],
  [
    'key' => 'github',
    'title' => 'GitHub',
    'summary' => 'Source control, collaboration, and CI/CD platform partner.',
    'website' => 'https://github.com',
    'order' => 40,
  ],
];

foreach ($partners as $partner) {
  $save_node('partner', 'field_partner_key', $partner['key'], $partner['title'], [
    'field_summary' => $partner['summary'],
    'field_website' => [
      'uri' => $partner['website'],
    ],
    'field_display_order' => $partner['order'],
    'field_is_featured' => TRUE,
    'field_is_active' => TRUE,
  ], $site_profile);
}

$team_members = [
  [
    'key' => 'founder-ceo',
    'title' => 'Founder & CEO',
    'role' => 'Founder & CEO',
    'summary' => 'Leads company strategy, business development, and client partnerships.',
    'bio' => 'Responsible for vision, growth, partnerships, and ensuring that every client engagement delivers measurable business value.',
    'email' => 'hello@growbigtechnologies.com',
    'linkedin' => 'https://www.linkedin.com/company/growbig-technologies-llp',
    'order' => 10,
  ],
  [
    'key' => 'technology-lead',
    'title' => 'Technology Lead',
    'role' => 'Technology Lead',
    'summary' => 'Leads architecture, engineering quality, and delivery standards.',
    'bio' => 'Responsible for technical direction, code quality, platform architecture, and scalable engineering practices.',
    'email' => 'tech@growbigtechnologies.com',
    'linkedin' => 'https://www.linkedin.com/company/growbig-technologies-llp',
    'order' => 20,
  ],
  [
    'key' => 'design-lead',
    'title' => 'Design Lead',
    'role' => 'Design Lead',
    'summary' => 'Leads UI, UX, product design, and customer experience.',
    'bio' => 'Responsible for translating business requirements into simple, usable, and polished digital experiences.',
    'email' => 'design@growbigtechnologies.com',
    'linkedin' => 'https://www.linkedin.com/company/growbig-technologies-llp',
    'order' => 30,
  ],
];

foreach ($team_members as $member) {
  $save_node('team_member', 'field_member_key', $member['key'], $member['title'], [
    'field_role' => $member['role'],
    'field_summary' => $member['summary'],
    'field_bio' => $member['bio'],
    'field_email' => $member['email'],
    'field_linkedin_url' => [
      'uri' => $member['linkedin'],
    ],
    'field_display_order' => $member['order'],
    'field_is_featured' => TRUE,
    'field_is_active' => TRUE,
  ], $site_profile);
}

print "Demo business content creation complete.\n";
