<?php

declare(strict_types=1);

namespace Drupal\site_platform_admin\Service;

use Drupal\Component\Datetime\TimeInterface;
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
    $this->updateSetupStatus($setup_id, $values);
    $this->updateSetupManifest($setup_id, $created_roles);

    return [
      'setup_id' => $setup_id,
      'site_name' => (string) $this->getValue($values, 'site.name'),
      'site_key' => (string) $this->getValue($values, 'site.key'),
      'current_step' => 'runner_prepared',
      'completed' => FALSE,
      'roles' => $created_roles,
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
      'pages' => (bool) $this->getValue($values, 'setup_options.create_default_pages') ? 'queued' : 'skipped',
      'menus' => (bool) $this->getValue($values, 'setup_options.create_default_menus') ? 'queued' : 'skipped',
      'forms' => (bool) $this->getValue($values, 'setup_options.create_default_forms') ? 'queued' : 'skipped',
      'content' => (bool) $this->getValue($values, 'setup_options.create_demo_content') ? 'queued' : 'skipped',
      'analytics' => 'completed',
      'verification' => 'pending',
    ];
  }

  /**
   * Updates setup manifest.
   */
  private function updateSetupManifest(string $setup_id, array $created_roles): void {
    $manifest = $this->setupStorage->getManifest();
    $config = $manifest['config'] ?? [];
    $roles = $manifest['roles'] ?? [];

    foreach ($created_roles as $role_id) {
      $roles[] = $role_id;
    }

    $config[] = 'system.site';
    $config[] = 'site_platform_api.analytics';

    $manifest['setup_id'] = $setup_id;
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
