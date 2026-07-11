<?php

declare(strict_types=1);

namespace Drupal\site_platform_admin\Service;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\user\RoleInterface;

/**
 * Runs safe first-run setup preparation tasks.
 */
final class SiteSetupRunner {

  /**
   * Setup role definitions.
   */
  private const ROLE_DEFINITIONS = [
    'site_developer' => 'Site Developer',
    'content_admin' => 'Content Admin',
    'hr_manager' => 'HR Manager',
    'form_manager' => 'Form Manager',
    'analytics_viewer' => 'Analytics Viewer',
  ];

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
    $this->updateSetupStatus($setup_id, $values);
    $this->updateSetupManifest($setup_id, $created_roles, $created_nodes, $created_webforms);

    return [
      'setup_id' => $setup_id,
      'site_name' => (string) $this->getValue($values, 'site.name'),
      'site_key' => (string) $this->getValue($values, 'site.key'),
      'current_step' => 'runner_prepared',
      'completed' => FALSE,
      'roles' => $created_roles,
      'nodes' => $created_nodes,
      'webforms' => $created_webforms,
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

    foreach (self::ROLE_DEFINITIONS as $role_id => $label) {
      $role = $storage->load($role_id);

      if (!$role instanceof RoleInterface) {
        $role = $storage->create([
          'id' => $role_id,
          'label' => $label,
        ]);
      }
      else {
        $role->set('label', $label);
      }

      $this->grantRolePermissions($role_id, $role);
      $role->save();

      $created_or_existing[] = $role_id;
    }

    return $created_or_existing;
  }

  /**
   * Grants baseline permissions to setup roles.
   */
  private function grantRolePermissions(string $role_id, RoleInterface $role): void {
    $permissions = match ($role_id) {
      'site_developer' => [
        'access administration pages',
        'administer site configuration',
        'administer site setup',
      ],
      'content_admin' => [
        'access administration pages',
        'access content overview',
        'administer nodes',
        'create site_page content',
        'edit any site_page content',
        'delete any site_page content',
      ],
      'hr_manager' => [
        'access administration pages',
        'access content overview',
        'create job content',
        'edit any job content',
        'delete any job content',
      ],
      'form_manager' => [
        'access administration pages',
        'access webform overview',
      ],
      'analytics_viewer' => [
        'access administration pages',
      ],
      default => [],
    };

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
