<?php

declare(strict_types=1);

namespace Drupal\site_platform_admin\Service;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Runs safe first-run setup preparation tasks.
 */
final class SiteSetupRunner {

  /**
   * Required setup value labels keyed by config path.
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
  ) {}

  /**
   * Returns missing required setup values.
   */
  public function getMissingRequiredValues(): array {
    $values = $this->configFactory->get('site_platform_admin.setup_values');
    $missing = [];

    foreach (self::REQUIRED_VALUES as $key => $label) {
      if (trim((string) $values->get($key)) === '') {
        $missing[$key] = $label;
      }
    }

    return $missing;
  }

  /**
   * Returns a safe setup preview.
   */
  public function getPreview(): array {
    $values = $this->configFactory->get('site_platform_admin.setup_values');

    return [
      'mode' => (string) ($values->get('mode') ?: 'single'),
      'environment' => (string) ($values->get('environment') ?: 'not set'),
      'site_name' => (string) ($values->get('site.name') ?: 'not set'),
      'site_key' => (string) ($values->get('site.key') ?: 'not set'),
      'primary_domain' => (string) ($values->get('site.primary_domain') ?: 'not set'),
      'frontend_url' => (string) ($values->get('site.frontend_url') ?: 'not set'),
      'api_url' => (string) ($values->get('site.api_url') ?: 'not set'),
      'create_default_pages' => (bool) $values->get('setup_options.create_default_pages'),
      'create_default_menus' => (bool) $values->get('setup_options.create_default_menus'),
      'create_default_forms' => (bool) $values->get('setup_options.create_default_forms'),
      'create_default_roles' => (bool) $values->get('setup_options.create_default_roles'),
      'create_demo_content' => (bool) $values->get('setup_options.create_demo_content'),
      'analytics_enabled' => (bool) $values->get('analytics.enabled'),
      'analytics_measurement_id' => (string) ($values->get('analytics.measurement_id') ?: ''),
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

    $values = $this->configFactory->get('site_platform_admin.setup_values');
    $setup_id = $this->getOrCreateSetupId();

    $this->applySystemSiteConfig();
    $this->applyAnalyticsConfig();
    $this->updateSetupStatus($setup_id);
    $this->updateSetupManifest($setup_id);

    return [
      'setup_id' => $setup_id,
      'site_name' => (string) $values->get('site.name'),
      'site_key' => (string) $values->get('site.key'),
      'current_step' => 'runner_prepared',
      'completed' => FALSE,
    ];
  }

  /**
   * Applies Drupal system site config from setup values.
   */
  private function applySystemSiteConfig(): void {
    $values = $this->configFactory->get('site_platform_admin.setup_values');

    $this->configFactory->getEditable('system.site')
      ->set('name', trim((string) $values->get('site.name')))
      ->set('mail', trim((string) $values->get('contact.primary_email')))
      ->save();
  }

  /**
   * Applies analytics config from setup values.
   */
  private function applyAnalyticsConfig(): void {
    $values = $this->configFactory->get('site_platform_admin.setup_values');
    $measurement_id = trim((string) $values->get('analytics.measurement_id'));

    $this->configFactory->getEditable('site_platform_api.analytics')
      ->set('enabled', (bool) $values->get('analytics.enabled') && $measurement_id !== '')
      ->set('provider', 'google_analytics')
      ->set('measurement_id', $measurement_id)
      ->save();
  }

  /**
   * Updates setup status config.
   */
  private function updateSetupStatus(string $setup_id): void {
    $values = $this->configFactory->get('site_platform_admin.setup_values');
    $status = $this->configFactory->getEditable('site_platform_admin.setup_status');

    $started_at = (int) ($status->get('started_at') ?: $this->time->getRequestTime());

    $status
      ->set('installed', TRUE)
      ->set('completed', FALSE)
      ->set('locked', FALSE)
      ->set('current_step', 'runner_prepared')
      ->set('setup_id', $setup_id)
      ->set('mode', (string) ($values->get('mode') ?: 'single'))
      ->set('environment', (string) ($values->get('environment') ?: ''))
      ->set('started_at', $started_at)
      ->set('completed_at', 0)
      ->set('steps', $this->buildStepStatus())
      ->save();
  }

  /**
   * Builds setup step status.
   */
  private function buildStepStatus(): array {
    $values = $this->configFactory->get('site_platform_admin.setup_values');

    return [
      'site_profile' => 'completed',
      'frontend_defaults' => 'completed',
      'roles' => (bool) $values->get('setup_options.create_default_roles') ? 'queued' : 'skipped',
      'pages' => (bool) $values->get('setup_options.create_default_pages') ? 'queued' : 'skipped',
      'menus' => (bool) $values->get('setup_options.create_default_menus') ? 'queued' : 'skipped',
      'forms' => (bool) $values->get('setup_options.create_default_forms') ? 'queued' : 'skipped',
      'content' => (bool) $values->get('setup_options.create_demo_content') ? 'queued' : 'skipped',
      'analytics' => 'completed',
      'verification' => 'pending',
    ];
  }

  /**
   * Updates setup manifest.
   */
  private function updateSetupManifest(string $setup_id): void {
    $manifest = $this->configFactory->getEditable('site_platform_admin.setup_manifest');
    $config = $manifest->get('config') ?: [];

    $config[] = 'system.site';
    $config[] = 'site_platform_api.analytics';
    $config[] = 'site_platform_admin.setup_status';
    $config[] = 'site_platform_admin.setup_values';

    $manifest
      ->set('setup_id', $setup_id)
      ->set('config', array_values(array_unique($config)))
      ->save();
  }

  /**
   * Gets existing setup ID or creates one.
   */
  private function getOrCreateSetupId(): string {
    $status = $this->configFactory->get('site_platform_admin.setup_status');
    $existing = trim((string) $status->get('setup_id'));

    if ($existing !== '') {
      return $existing;
    }

    return 'setup-' . date('Ymd-His', $this->time->getRequestTime());
  }

}
