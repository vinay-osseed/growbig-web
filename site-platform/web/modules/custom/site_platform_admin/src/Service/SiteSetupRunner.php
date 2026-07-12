<?php

declare(strict_types=1);

namespace Drupal\site_platform_admin\Service;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\user\RoleInterface;

/**
 * Runs safe first-run setup preparation tasks.
 */
final class SiteSetupRunner {

  /**
   * Setup role labels.
   */
  private const ROLE_LABELS = [
    'content_editor' => 'Content Editor',
    'hr_manager' => 'HR Manager',
  ];

  /**
   * Deprecated setup roles removed from the older setup model.
   */
  private const DEPRECATED_SETUP_ROLES = [
    'site_developer',
    'content_admin',
    'form_manager',
    'analytics_viewer',
  ];

  /**
   * Setup role permissions.
   */
  private const ROLE_PERMISSIONS = [
    'content_editor' => [
      'access administration pages',
      'access content overview',
      'view own unpublished content',
      'administer media',
      'view media',
      'create site_page content',
      'edit any site_page content',
      'delete any site_page content',
      'create service content',
      'edit any service content',
      'delete any service content',
      'create partner content',
      'edit any partner content',
      'delete any partner content',
      'create team_member content',
      'edit any team_member content',
      'delete any team_member content',
      'create job content',
      'edit any job content',
      'delete any job content',
      'access webform overview',
      'view any webform submission',
      'edit any webform submission',
    ],
    'hr_manager' => [
      'access administration pages',
      'access content overview',
      'create job content',
      'edit any job content',
      'delete any job content',
      'access webform overview',
      'view any webform submission',
      'edit any webform submission',
    ],
  ];

  /**
   * Setup role definitions.
   */


  /**
   * Required setup value labels keyed by value path.
   */
  private const REQUIRED_VALUES = [
    'site.name' => 'Site Name',
    'site.key' => 'Site Key',
    'site.primary_domain' => 'Primary Domain',
    'site.frontend_url' => 'Frontend URL',
    'site.admin_url' => 'Admin URL',
    'site.api_url' => 'API URL',
    'contact.company_name' => 'Company Name',
    'contact.primary_email' => 'Primary Email',
    'contact.country' => 'Country',
  ];

  /**
   * Constructs a setup runner.
   */
  public function __construct(
    private readonly ConfigFactoryInterface $configFactory,
    private readonly TimeInterface $time,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly SiteSetupStorage $setupStorage,
  ) {}

  /**
   * Returns missing required setup values.
   */
  public function getMissingRequiredValues(): array {
    $values = $this->setupStorage->getValues();
    $missing = [];

    foreach (self::REQUIRED_VALUES as $key => $label) {
      if (trim((string) $this->getValue($values, $key)) === '') {
        $missing[$key] = $label;
      }
    }

    return $missing;
  }

  /**
   * Returns a safe setup preview.
   */
  public function getPreview(): array {
    $values = $this->setupStorage->getValues();

    return [
      'mode' => (string) ($this->getValue($values, 'mode') ?: 'single'),
      'environment' => (string) ($this->getValue($values, 'environment') ?: 'not set'),
      'site_name' => (string) ($this->getValue($values, 'site.name') ?: 'not set'),
      'site_key' => (string) ($this->getValue($values, 'site.key') ?: 'not set'),
      'primary_domain' => (string) ($this->getValue($values, 'site.primary_domain') ?: 'not set'),
      'frontend_url' => (string) ($this->getValue($values, 'site.frontend_url') ?: 'not set'),
      'api_url' => (string) ($this->getValue($values, 'site.api_url') ?: 'not set'),
      'create_default_pages' => (bool) $this->getValue($values, 'setup_options.create_default_pages'),
      'create_default_menus' => (bool) $this->getValue($values, 'setup_options.create_default_menus'),
      'create_default_forms' => (bool) $this->getValue($values, 'setup_options.create_default_forms'),
      'create_default_roles' => (bool) $this->getValue($values, 'setup_options.create_default_roles'),
      'create_demo_content' => (bool) $this->getValue($values, 'setup_options.create_demo_content'),
      'analytics_enabled' => (bool) $this->getValue($values, 'analytics.enabled'),
      'analytics_measurement_id' => (string) ($this->getValue($values, 'analytics.measurement_id') ?: ''),
    ];
  }

  /**
   * Runs safe setup preparation.
   */
  public function run(): array {
    $missing = $this->getMissingRequiredValues();

    if ($missing !== []) {
      throw new \InvalidArgumentException('Missing required setup values: ' . implode(', ', $missing));
    }

    $values = $this->setupStorage->getValues();
    $setup_id = $this->getOrCreateSetupId();

    $this->applySystemSiteConfig($values);
    $this->applyAnalyticsConfig($values);
    $created_roles = $this->ensureRoles($values);
    $created_nodes = $this->ensureDefaultPages($values);
    $created_webforms = $this->ensureDefaultForms($values);
    $created_demo_nodes = $this->ensureDemoContent($values);
    $this->updateSetupStatus($setup_id, $values);
    $this->updateSetupManifest($setup_id, $created_roles, array_merge($created_nodes, $created_demo_nodes), $created_webforms);

    return [
      'setup_id' => $setup_id,
      'site_name' => (string) $this->getValue($values, 'site.name'),
      'site_key' => (string) $this->getValue($values, 'site.key'),
      'current_step' => 'runner_prepared',
      'completed' => FALSE,
      'roles' => $created_roles,
      'nodes' => $created_nodes,
      'webforms' => $created_webforms,
      'demo_nodes' => $created_demo_nodes,
    ];
  }

  /**
   * Ensures frontend-safe default forms exist.
   */
  private function ensureDefaultForms(array $values): array {
    if (!(bool) $this->getValue($values, 'setup_options.create_default_forms')) {
      return [];
    }

    if (!$this->entityTypeManager->hasDefinition('webform')) {
      return [];
    }

    $created_or_existing = [];
    $storage = $this->entityTypeManager->getStorage('webform');

    foreach ($this->getDefaultWebformDefinitions() as $webform_id => $definition) {
      $webform = $storage->load($webform_id);

      if (!$webform) {
        $webform = $storage->create([
          'id' => $webform_id,
          'title' => $definition['title'],
          'description' => $definition['description'],
          'status' => 'open',
          'elements' => Yaml::encode($definition['elements']),
        ]);

        $webform->save();
      }

      $created_or_existing[] = $webform_id;
    }

    return $created_or_existing;
  }

  /**
   * Gets default webform definitions.
   */
  private function getDefaultWebformDefinitions(): array {
    return [
      'contact_us' => [
        'title' => 'Contact Us',
        'description' => 'Default contact form created by the setup workflow.',
        'elements' => [
          'name' => [
            '#type' => 'textfield',
            '#title' => 'Name',
            '#required' => TRUE,
          ],
          'email' => [
            '#type' => 'email',
            '#title' => 'Email',
            '#required' => TRUE,
          ],
          'phone' => [
            '#type' => 'textfield',
            '#title' => 'Phone',
          ],
          'company' => [
            '#type' => 'textfield',
            '#title' => 'Company',
          ],
          'subject' => [
            '#type' => 'textfield',
            '#title' => 'Subject',
            '#required' => TRUE,
          ],
          'message' => [
            '#type' => 'textarea',
            '#title' => 'Message',
            '#required' => TRUE,
          ],
          'consent' => [
            '#type' => 'checkbox',
            '#title' => 'I agree to be contacted about this inquiry.',
            '#required' => TRUE,
          ],
          'actions' => [
            '#type' => 'webform_actions',
            '#title' => 'Submit button(s)',
            '#submit__label' => 'Send Message',
          ],
        ],
      ],
      'job_application' => [
        'title' => 'Job Application',
        'description' => 'Default job application form created by the setup workflow.',
        'elements' => [
          'job_key' => [
            '#type' => 'hidden',
            '#title' => 'Job Key',
          ],
          'job_title' => [
            '#type' => 'textfield',
            '#title' => 'Job Title',
          ],
          'name' => [
            '#type' => 'textfield',
            '#title' => 'Name',
            '#required' => TRUE,
          ],
          'email' => [
            '#type' => 'email',
            '#title' => 'Email',
            '#required' => TRUE,
          ],
          'phone' => [
            '#type' => 'textfield',
            '#title' => 'Phone',
          ],
          'current_location' => [
            '#type' => 'textfield',
            '#title' => 'Current Location',
          ],
          'experience_years' => [
            '#type' => 'number',
            '#title' => 'Experience Years',
          ],
          'current_company' => [
            '#type' => 'textfield',
            '#title' => 'Current Company',
          ],
          'resume_upload' => [
            '#type' => 'managed_file',
            '#title' => 'Resume Upload',
          ],
          'portfolio_url' => [
            '#type' => 'url',
            '#title' => 'Portfolio URL',
          ],
          'linkedin_url' => [
            '#type' => 'url',
            '#title' => 'LinkedIn URL',
          ],
          'message' => [
            '#type' => 'textarea',
            '#title' => 'Message',
          ],
          'consent' => [
            '#type' => 'checkbox',
            '#title' => 'I confirm this application information is accurate.',
            '#required' => TRUE,
          ],
          'actions' => [
            '#type' => 'webform_actions',
            '#title' => 'Submit button(s)',
            '#submit__label' => 'Submit Application',
          ],
        ],
      ],
    ];
  }

  /**
   * Ensures frontend-safe default pages exist.
   */
  private function ensureDefaultPages(array $values): array {
    if (!(bool) $this->getValue($values, 'setup_options.create_default_pages')) {
      return [];
    }

    $created_or_existing = [];
    $pages = $this->getDefaultPageDefinitions();
    $storage = $this->entityTypeManager->getStorage('node');

    foreach ($pages as $page_key => $definition) {
      $node = $this->loadPageByKey($page_key);

      if (!$node) {
        $node = $storage->create([
          'type' => 'site_page',
          'title' => $definition['title'],
          'status' => 1,
          'uid' => 1,
        ]);
      }
      else {
        $node->setTitle($definition['title']);
        $node->setPublished(TRUE);
      }

      $this->setFieldValue($node, 'field_page_key', $page_key);
      $this->setFieldValue($node, 'field_page_type', $definition['page_type']);
      $this->setFieldValue($node, 'field_summary', $definition['summary']);

      if ((bool) $this->getValue($values, 'setup_options.create_default_menus')) {
        $this->setFieldValue($node, 'field_show_in_header', TRUE);
        $this->setFieldValue($node, 'field_show_in_footer', TRUE);
        $this->setFieldValue($node, 'field_menu_title', $definition['menu_title']);
        $this->setFieldValue($node, 'field_menu_weight', $definition['menu_weight']);
      }

      $node->save();
      $created_or_existing[] = (int) $node->id();
    }

    return $created_or_existing;
  }

  /**
   * Loads a site page by page key.
   */
  private function loadPageByKey(string $page_key): ?object {
    $storage = $this->entityTypeManager->getStorage('node');
    $query = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'site_page')
      ->condition('field_page_key', $page_key)
      ->range(0, 1);

    $ids = $query->execute();

    if (!$ids) {
      return NULL;
    }

    $node = $storage->load(reset($ids));

    return is_object($node) ? $node : NULL;
  }

  /**
   * Sets a field value only when the field exists.
   */
  private function setFieldValue(object $entity, string $field_name, mixed $value): void {
    if (method_exists($entity, 'hasField') && $entity->hasField($field_name)) {
      $entity->set($field_name, $value);
    }
  }

  /**
   * Ensures safe demo content exists when requested.
   */
  private function ensureDemoContent(array $values): array {
    if (!(bool) $this->getValue($values, 'setup_options.create_demo_content')) {
      return [];
    }

    $created_or_existing = [];
    $site_profile = $this->loadSetupSiteProfile($values);

    foreach ($this->getDefaultDemoContentDefinitions() as $definition) {
      $node = $this->saveDemoNode($definition, $site_profile);
      if ($node instanceof NodeInterface) {
        $created_or_existing[] = (int) $node->id();
      }
    }

    return $created_or_existing;
  }

  /**
   * Loads the setup site profile for assigning demo content.
   */
  private function loadSetupSiteProfile(array $values): ?NodeInterface {
    $site_key = trim((string) $this->getValue($values, 'site.key'));

    if ($site_key === '') {
      return NULL;
    }

    if (!$this->entityTypeManager->getStorage('node_type')->load('site_profile')) {
      return NULL;
    }

    if (!$this->entityTypeManager->getStorage('field_config')->load('node.site_profile.field_site_key')) {
      return NULL;
    }

    $storage = $this->entityTypeManager->getStorage('node');
    $ids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'site_profile')
      ->condition('field_site_key', $site_key)
      ->range(0, 1)
      ->execute();

    if (!$ids) {
      return NULL;
    }

    $node = $storage->load(reset($ids));

    return $node instanceof NodeInterface ? $node : NULL;
  }

  /**
   * Creates or updates one demo content node.
   */
  private function saveDemoNode(array $definition, ?NodeInterface $site_profile): ?NodeInterface {
    $type = (string) ($definition['type'] ?? '');
    $key_field = (string) ($definition['key_field'] ?? '');
    $key = (string) ($definition['key'] ?? '');
    $title = (string) ($definition['title'] ?? '');
    $fields = is_array($definition['fields'] ?? NULL) ? $definition['fields'] : [];

    if ($type === '' || $key_field === '' || $key === '' || $title === '') {
      return NULL;
    }

    if (!$this->entityTypeManager->getStorage('node_type')->load($type)) {
      return NULL;
    }

    if (!$this->entityTypeManager->getStorage('field_config')->load('node.' . $type . '.' . $key_field)) {
      return NULL;
    }

    $storage = $this->entityTypeManager->getStorage('node');
    $ids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', $type)
      ->condition($key_field, $key)
      ->range(0, 1)
      ->execute();

    $node = NULL;
    if ($ids) {
      $loaded = $storage->load(reset($ids));
      if ($loaded instanceof NodeInterface) {
        $node = $loaded;
      }
    }

    if (!$node instanceof NodeInterface) {
      $node = $storage->create([
        'type' => $type,
        'title' => $title,
        'status' => 1,
        'uid' => 1,
      ]);
    }

    $node->setTitle($title);
    $node->setPublished(TRUE);
    $this->setFieldValue($node, $key_field, $key);

    if ($site_profile instanceof NodeInterface && $node->hasField('field_sites')) {
      $node->set('field_sites', [
        [
          'target_id' => $site_profile->id(),
        ],
      ]);
    }

    foreach ($fields as $field_name => $value) {
      $this->setFieldValue($node, (string) $field_name, $value);
    }

    $node->save();

    return $node;
  }

  /**
   * Gets frontend-safe demo content definitions.
   */
  private function getDefaultDemoContentDefinitions(): array {
    return [
      [
        'type' => 'service',
        'key_field' => 'field_service_key',
        'key' => 'website-development',
        'title' => 'Website Development',
        'fields' => [
          'field_summary' => 'Modern, responsive, high-performance websites built for growth, SEO, and conversion.',
          'field_icon' => 'globe',
          'field_accent_color' => '#2563eb',
          'field_link_url' => [
            'uri' => 'internal:/services/website-development',
          ],
          'field_display_order' => 10,
          'field_is_featured' => TRUE,
          'field_is_active' => TRUE,
        ],
      ],
      [
        'type' => 'service',
        'key_field' => 'field_service_key',
        'key' => 'mobile-app-development',
        'title' => 'Mobile App Development',
        'fields' => [
          'field_summary' => 'Native and cross-platform mobile applications for iOS and Android.',
          'field_icon' => 'smartphone',
          'field_accent_color' => '#f59e0b',
          'field_link_url' => [
            'uri' => 'internal:/services/mobile-app-development',
          ],
          'field_display_order' => 20,
          'field_is_featured' => TRUE,
          'field_is_active' => TRUE,
        ],
      ],
      [
        'type' => 'service',
        'key_field' => 'field_service_key',
        'key' => 'ai-solutions',
        'title' => 'AI Solutions',
        'fields' => [
          'field_summary' => 'AI-powered tools, automation, and intelligent workflows for business productivity.',
          'field_icon' => 'sparkles',
          'field_accent_color' => '#6366f1',
          'field_link_url' => [
            'uri' => 'internal:/services/ai-solutions',
          ],
          'field_display_order' => 30,
          'field_is_featured' => TRUE,
          'field_is_active' => TRUE,
        ],
      ],
      [
        'type' => 'partner',
        'key_field' => 'field_partner_key',
        'key' => 'aws',
        'title' => 'Amazon Web Services',
        'fields' => [
          'field_summary' => 'Cloud infrastructure and deployment platform partner.',
          'field_website' => [
            'uri' => 'https://aws.amazon.com',
          ],
          'field_display_order' => 10,
          'field_is_featured' => TRUE,
          'field_is_active' => TRUE,
        ],
      ],
      [
        'type' => 'partner',
        'key_field' => 'field_partner_key',
        'key' => 'github',
        'title' => 'GitHub',
        'fields' => [
          'field_summary' => 'Source control, collaboration, and CI/CD platform partner.',
          'field_website' => [
            'uri' => 'https://github.com',
          ],
          'field_display_order' => 20,
          'field_is_featured' => TRUE,
          'field_is_active' => TRUE,
        ],
      ],
      [
        'type' => 'team_member',
        'key_field' => 'field_member_key',
        'key' => 'founder-ceo',
        'title' => 'Founder & CEO',
        'fields' => [
          'field_role' => 'Founder & CEO',
          'field_summary' => 'Leads company strategy, business development, and client partnerships.',
          'field_bio' => 'Responsible for vision, growth, partnerships, and measurable business value.',
          'field_email' => 'office@growbigllp.com',
          'field_linkedin_url' => [
            'uri' => 'https://www.linkedin.com/company/growbig-technologies-llp',
          ],
          'field_display_order' => 10,
          'field_is_featured' => TRUE,
          'field_is_active' => TRUE,
        ],
      ],
      [
        'type' => 'job',
        'key_field' => 'field_job_key',
        'key' => 'frontend-developer',
        'title' => 'Frontend Developer',
        'fields' => [
          'field_summary' => 'Build fast, accessible, API-driven frontend experiences.',
          'field_location' => 'Remote',
          'field_employment_type' => 'Full-time',
          'field_display_order' => 10,
          'field_is_featured' => TRUE,
          'field_is_active' => TRUE,
        ],
      ],
    ];
  }

  /**
   * Gets default page definitions.
   */
  private function getDefaultPageDefinitions(): array {
    return [
      'home' => [
        'title' => 'Home',
        'page_type' => 'home',
        'summary' => 'Frontend-safe home page created by the setup workflow.',
        'menu_title' => 'Home',
        'menu_weight' => 0,
      ],
      'about' => [
        'title' => 'About',
        'page_type' => 'standard',
        'summary' => 'Default about page created by the setup workflow.',
        'menu_title' => 'About',
        'menu_weight' => 10,
      ],
      'careers' => [
        'title' => 'Careers',
        'page_type' => 'standard',
        'summary' => 'Default careers page created by the setup workflow.',
        'menu_title' => 'Careers',
        'menu_weight' => 20,
      ],
      'contact' => [
        'title' => 'Contact',
        'page_type' => 'standard',
        'summary' => 'Default contact page created by the setup workflow.',
        'menu_title' => 'Contact',
        'menu_weight' => 30,
      ],
    ];
  }

  /**
   * Ensures setup roles exist.
   */
  private function ensureRoles(array $values): array {
    if (!(bool) $this->getValue($values, 'setup_options.create_default_roles')) {
      return [];
    }

    $created_or_existing = [];
    $storage = $this->entityTypeManager->getStorage('user_role');

    foreach (self::DEPRECATED_SETUP_ROLES as $deprecated_role_id) {
      $deprecated_role = $storage->load($deprecated_role_id);
      if ($deprecated_role instanceof RoleInterface) {
        $deprecated_role->delete();
      }
    }

    foreach (self::ROLE_LABELS as $role_id => $label) {
      $role = $storage->load($role_id);

      if (!$role instanceof RoleInterface) {
        $role = $storage->create([
          'id' => $role_id,
          'label' => (string) $label,
        ]);
      }
      else {
        $role->set('label', (string) $label);
      }

      $this->grantRolePermissions($role_id, $role);
      $role->save();

      $created_or_existing[] = $role_id;
    }

    return $created_or_existing;
  }

  /**
   * Gets role permissions managed by setup.
   */
  private function grantRolePermissions(string $role_id, RoleInterface $role): void {
    $permissions = self::ROLE_PERMISSIONS[$role_id] ?? [];

    foreach ($permissions as $permission) {
      if (!$role->hasPermission($permission)) {
        $role->grantPermission($permission);
      }
    }
  }

  /**
   * Applies Drupal system site config from setup values.
   */
  private function applySystemSiteConfig(array $values): void {
    $this->configFactory->getEditable('system.site')
      ->set('name', trim((string) $this->getValue($values, 'site.name')))
      ->set('mail', trim((string) $this->getValue($values, 'contact.primary_email')))
      ->save();
  }

  /**
   * Applies analytics config from setup values.
   */
  private function applyAnalyticsConfig(array $values): void {
    $measurement_id = trim((string) $this->getValue($values, 'analytics.measurement_id'));

    $this->configFactory->getEditable('site_platform_api.analytics')
      ->set('enabled', (bool) $this->getValue($values, 'analytics.enabled') && $measurement_id !== '')
      ->set('provider', 'google_analytics')
      ->set('measurement_id', $measurement_id)
      ->save();
  }

  /**
   * Updates setup status.
   */
  private function updateSetupStatus(string $setup_id, array $values): void {
    $status = $this->setupStorage->getStatus();
    $started_at = (int) ($status['started_at'] ?: $this->time->getRequestTime());

    $status['installed'] = TRUE;
    $status['completed'] = FALSE;
    $status['locked'] = FALSE;
    $status['current_step'] = 'runner_prepared';
    $status['setup_id'] = $setup_id;
    $status['mode'] = (string) ($this->getValue($values, 'mode') ?: 'single');
    $status['environment'] = (string) ($this->getValue($values, 'environment') ?: '');
    $status['started_at'] = $started_at;
    $status['completed_at'] = 0;
    $status['steps'] = $this->buildStepStatus($values);

    $this->setupStorage->saveStatus($status);
  }

  /**
   * Builds setup step status.
   */
  private function buildStepStatus(array $values): array {
    return [
      'site_profile' => 'completed',
      'frontend_defaults' => 'completed',
      'roles' => (bool) $this->getValue($values, 'setup_options.create_default_roles') ? 'completed' : 'skipped',
      'pages' => (bool) $this->getValue($values, 'setup_options.create_default_pages') ? 'completed' : 'skipped',
      'menus' => (bool) $this->getValue($values, 'setup_options.create_default_menus') ? 'completed' : 'skipped',
      'forms' => (bool) $this->getValue($values, 'setup_options.create_default_forms') ? 'completed' : 'skipped',
      'content' => (bool) $this->getValue($values, 'setup_options.create_demo_content') ? 'queued' : 'skipped',
      'analytics' => 'completed',
      'verification' => 'pending',
    ];
  }

  /**
   * Updates setup manifest.
   */
  private function updateSetupManifest(string $setup_id, array $created_roles, array $created_nodes, array $created_webforms): void {
    $manifest = $this->setupStorage->getManifest();
    $config = $manifest['config'] ?? [];
    $roles = $manifest['roles'] ?? [];
    $nodes = $manifest['nodes'] ?? [];
    $webforms = $manifest['webforms'] ?? [];

    foreach ($created_roles as $role_id) {
      $roles[] = $role_id;
    }

    foreach ($created_nodes as $node_id) {
      $nodes[] = $node_id;
    }

    foreach ($created_webforms as $webform_id) {
      $webforms[] = $webform_id;
    }

    $config[] = 'system.site';
    $config[] = 'site_platform_api.analytics';

    $manifest['setup_id'] = $setup_id;
    $manifest['nodes'] = array_values(array_unique($nodes));
    $manifest['webforms'] = array_values(array_unique($webforms));
    $manifest['roles'] = array_values(array_unique($roles));
    $manifest['config'] = array_values(array_unique($config));

    $this->setupStorage->saveManifest($manifest);
  }

  /**
   * Gets setup completion readiness.
   */
  public function getCompletionReadiness(): array {
    $values = $this->setupStorage->getValues();

    $missing_values = array_values($this->getMissingRequiredValues());
    $missing_roles = $this->getMissingRoles($values);
    $missing_pages = $this->getMissingPages($values);
    $missing_webforms = $this->getMissingWebforms($values);

    $missing = array_merge(
      $missing_values,
      $missing_roles,
      $missing_pages,
      $missing_webforms
    );

    return [
      'ready' => $missing === [],
      'missing' => $missing,
      'checked' => [
        'required_values' => $missing_values === [],
        'roles' => $missing_roles === [],
        'pages' => $missing_pages === [],
        'webforms' => $missing_webforms === [],
      ],
    ];
  }

  /**
   * Marks setup completed and locked.
   */
  public function completeAndLock(): array {
    $readiness = $this->getCompletionReadiness();

    if (!$readiness['ready']) {
      throw new \InvalidArgumentException('Setup cannot be completed. Missing: ' . implode(', ', $readiness['missing']));
    }

    $status = $this->setupStorage->getStatus();
    $values = $this->setupStorage->getValues();

    $status['installed'] = TRUE;
    $status['completed'] = TRUE;
    $status['locked'] = TRUE;
    $status['current_step'] = 'setup_completed';
    $status['mode'] = (string) ($this->getValue($values, 'mode') ?: 'single');
    $status['environment'] = (string) ($this->getValue($values, 'environment') ?: '');
    $status['completed_at'] = $this->time->getRequestTime();
    $status['steps'] = $this->buildCompletedStepStatus($values);

    $this->setupStorage->saveStatus($status);

    return $status;
  }

  /**
   * Gets missing setup roles.
   */
  private function getMissingRoles(array $values): array {
    if (!(bool) $this->getValue($values, 'setup_options.create_default_roles')) {
      return [];
    }

    $missing = [];
    $storage = $this->entityTypeManager->getStorage('user_role');

    foreach (self::ROLE_LABELS as $role_id => $label) {
      if (!$storage->load($role_id)) {
        $missing[] = 'Role: ' . $label;
      }
    }

    return $missing;
  }

  /**
   * Gets missing setup pages.
   */
  private function getMissingPages(array $values): array {
    if (!(bool) $this->getValue($values, 'setup_options.create_default_pages')) {
      return [];
    }

    $missing = [];

    foreach (array_keys($this->getDefaultPageDefinitions()) as $page_key) {
      if (!$this->loadPageByKey($page_key)) {
        $missing[] = 'Page: ' . $page_key;
      }
    }

    return $missing;
  }

  /**
   * Gets missing setup webforms.
   */
  private function getMissingWebforms(array $values): array {
    if (!(bool) $this->getValue($values, 'setup_options.create_default_forms')) {
      return [];
    }

    if (!$this->entityTypeManager->hasDefinition('webform')) {
      return ['Webform module/entity type'];
    }

    $missing = [];
    $storage = $this->entityTypeManager->getStorage('webform');

    foreach (array_keys($this->getDefaultWebformDefinitions()) as $webform_id) {
      if (!$storage->load($webform_id)) {
        $missing[] = 'Webform: ' . $webform_id;
      }
    }

    return $missing;
  }

  /**
   * Builds completed setup step status.
   */
  private function buildCompletedStepStatus(array $values): array {
    return [
      'site_profile' => 'completed',
      'frontend_defaults' => 'completed',
      'roles' => (bool) $this->getValue($values, 'setup_options.create_default_roles') ? 'completed' : 'skipped',
      'pages' => (bool) $this->getValue($values, 'setup_options.create_default_pages') ? 'completed' : 'skipped',
      'menus' => (bool) $this->getValue($values, 'setup_options.create_default_menus') ? 'completed' : 'skipped',
      'forms' => (bool) $this->getValue($values, 'setup_options.create_default_forms') ? 'completed' : 'skipped',
      'content' => (bool) $this->getValue($values, 'setup_options.create_demo_content') ? 'completed' : 'skipped',
      'analytics' => 'completed',
      'verification' => 'completed',
    ];
  }

  /**
   * Gets existing setup ID or creates one.
   */
  private function getOrCreateSetupId(): string {
    $status = $this->setupStorage->getStatus();
    $existing = trim((string) ($status['setup_id'] ?? ''));

    if ($existing !== '') {
      return $existing;
    }

    return 'setup-' . date('Ymd-His', $this->time->getRequestTime());
  }

  /**
   * Gets a nested setup value.
   */
  private function getValue(array $values, string $path): mixed {
    $current = $values;

    foreach (explode('.', $path) as $part) {
      if (!is_array($current) || !array_key_exists($part, $current)) {
        return NULL;
      }

      $current = $current[$part];
    }

    return $current;
  }

}
