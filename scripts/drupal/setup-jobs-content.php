<?php

declare(strict_types=1);

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;

$bundle = 'job';

if (!NodeType::load($bundle)) {
  NodeType::create([
    'type' => $bundle,
    'name' => 'Job',
    'description' => 'Reusable job openings for the careers section.',
  ])->save();

  print "Created content type: job\n";
}
else {
  print "Content type already exists: job\n";
}

function ensure_field_storage(string $field_name, string $type, array $settings = [], int $cardinality = 1): void {
  if (FieldStorageConfig::loadByName('node', $field_name)) {
    print "Field storage exists: {$field_name}\n";
    return;
  }

  FieldStorageConfig::create([
    'field_name' => $field_name,
    'entity_type' => 'node',
    'type' => $type,
    'settings' => $settings,
    'cardinality' => $cardinality,
  ])->save();

  print "Created field storage: {$field_name}\n";
}

function ensure_field_config(string $bundle, string $field_name, string $label, bool $required = FALSE, array $settings = []): void {
  if (FieldConfig::loadByName('node', $bundle, $field_name)) {
    print "Field config exists: {$field_name}\n";
    return;
  }

  FieldConfig::create([
    'field_name' => $field_name,
    'entity_type' => 'node',
    'bundle' => $bundle,
    'label' => $label,
    'required' => $required,
    'settings' => $settings,
  ])->save();

  print "Created field config: {$field_name}\n";
}

ensure_field_storage('field_job_key', 'string', ['max_length' => 128]);
ensure_field_config($bundle, 'field_job_key', 'Job Key', TRUE);

ensure_field_storage('field_summary', 'string_long');
ensure_field_config($bundle, 'field_summary', 'Summary');

ensure_field_storage('field_department', 'string', ['max_length' => 128]);
ensure_field_config($bundle, 'field_department', 'Department');

ensure_field_storage('field_location', 'string', ['max_length' => 255]);
ensure_field_config($bundle, 'field_location', 'Location');

ensure_field_storage('field_employment_type', 'list_string', [
  'allowed_values' => [
    'full_time' => 'Full Time',
    'part_time' => 'Part Time',
    'contract' => 'Contract',
    'internship' => 'Internship',
    'remote' => 'Remote',
  ],
]);
ensure_field_config($bundle, 'field_employment_type', 'Employment Type');

ensure_field_storage('field_experience_level', 'string', ['max_length' => 128]);
ensure_field_config($bundle, 'field_experience_level', 'Experience Level');

ensure_field_storage('field_salary_range', 'string', ['max_length' => 128]);
ensure_field_config($bundle, 'field_salary_range', 'Salary Range');

ensure_field_storage('field_description', 'text_long');
ensure_field_config($bundle, 'field_description', 'Description');

ensure_field_storage('field_responsibilities', 'text_long');
ensure_field_config($bundle, 'field_responsibilities', 'Responsibilities');

ensure_field_storage('field_requirements', 'text_long');
ensure_field_config($bundle, 'field_requirements', 'Requirements');

ensure_field_storage('field_display_order', 'integer');
ensure_field_config($bundle, 'field_display_order', 'Display Order');

ensure_field_storage('field_is_featured', 'boolean');
ensure_field_config($bundle, 'field_is_featured', 'Featured');

ensure_field_storage('field_is_active', 'boolean');
ensure_field_config($bundle, 'field_is_active', 'Active');

ensure_field_storage('field_closing_date', 'datetime', ['datetime_type' => 'date']);
ensure_field_config($bundle, 'field_closing_date', 'Closing Date');

$jobs = [
  [
    'title' => 'Frontend Developer',
    'key' => 'frontend-developer',
    'summary' => 'Build responsive, accessible, high-quality frontend interfaces for modern web applications.',
    'department' => 'Engineering',
    'location' => 'Remote / Sawantwadi, India',
    'employment_type' => 'full_time',
    'experience_level' => '1-3 years',
    'salary_range' => '',
    'description' => 'We are looking for a frontend developer who can build clean, reusable, responsive user interfaces.',
    'responsibilities' => "Build frontend components\nIntegrate APIs\nMaintain performance and accessibility\nCollaborate with backend and design teams",
    'requirements' => "Strong HTML, CSS, and JavaScript\nExperience with React or similar frameworks\nGood understanding of API integration",
    'order' => 10,
    'featured' => TRUE,
  ],
  [
    'title' => 'Backend Developer',
    'key' => 'backend-developer',
    'summary' => 'Work on backend APIs, Drupal platforms, integrations, and scalable application architecture.',
    'department' => 'Engineering',
    'location' => 'Remote / Sawantwadi, India',
    'employment_type' => 'full_time',
    'experience_level' => '2-5 years',
    'salary_range' => '',
    'description' => 'We are looking for a backend developer who can build reliable APIs, integrations, and platform features.',
    'responsibilities' => "Build backend APIs\nWork with Drupal and PHP\nDesign data models\nWrite clean and maintainable code",
    'requirements' => "Strong PHP fundamentals\nExperience with Drupal or Laravel preferred\nGood SQL and API design knowledge",
    'order' => 20,
    'featured' => TRUE,
  ],
  [
    'title' => 'Digital Marketing Executive',
    'key' => 'digital-marketing-executive',
    'summary' => 'Support SEO, campaigns, analytics, social media, and content-driven growth initiatives.',
    'department' => 'Marketing',
    'location' => 'Remote / Sawantwadi, India',
    'employment_type' => 'full_time',
    'experience_level' => '0-2 years',
    'salary_range' => '',
    'description' => 'We are looking for a digital marketing executive to support campaigns, SEO, content, and analytics.',
    'responsibilities' => "Manage SEO tasks\nSupport content planning\nTrack campaign performance\nCoordinate social media updates",
    'requirements' => "Basic SEO and digital marketing knowledge\nGood communication skills\nWillingness to learn and execute",
    'order' => 30,
    'featured' => FALSE,
  ],
];

$storage = \Drupal::entityTypeManager()->getStorage('node');

foreach ($jobs as $job) {
  $existing = $storage->getQuery()
    ->accessCheck(FALSE)
    ->condition('type', 'job')
    ->condition('field_job_key', $job['key'])
    ->range(0, 1)
    ->execute();

  if ($existing) {
    print "Job already exists: {$job['key']}\n";
    continue;
  }

  $node = Node::create([
    'type' => 'job',
    'title' => $job['title'],
    'status' => 1,
    'field_job_key' => $job['key'],
    'field_summary' => $job['summary'],
    'field_department' => $job['department'],
    'field_location' => $job['location'],
    'field_employment_type' => $job['employment_type'],
    'field_experience_level' => $job['experience_level'],
    'field_salary_range' => $job['salary_range'],
    'field_description' => [
      'value' => $job['description'],
      'format' => 'basic_html',
    ],
    'field_responsibilities' => [
      'value' => $job['responsibilities'],
      'format' => 'basic_html',
    ],
    'field_requirements' => [
      'value' => $job['requirements'],
      'format' => 'basic_html',
    ],
    'field_display_order' => $job['order'],
    'field_is_featured' => $job['featured'],
    'field_is_active' => TRUE,
  ]);

  $node->save();

  print "Created job: {$job['key']}\n";
}

print "Jobs content setup complete.\n";
