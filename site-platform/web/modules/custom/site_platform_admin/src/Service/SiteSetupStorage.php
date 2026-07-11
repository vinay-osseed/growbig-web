<?php

declare(strict_types=1);

namespace Drupal\site_platform_admin\Service;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\State\StateInterface;

/**
 * Stores environment-specific setup runtime data.
 */
final class SiteSetupStorage {

  /**
   * Setup values key.
   */
  private const VALUES_KEY = 'site_platform_admin.setup_values';

  /**
   * Setup status key.
   */
  private const STATUS_KEY = 'site_platform_admin.setup_status';

  /**
   * Setup manifest key.
   */
  private const MANIFEST_KEY = 'site_platform_admin.setup_manifest';

  /**
   * Constructs setup storage.
   */
  public function __construct(
    private readonly ConfigFactoryInterface $configFactory,
    private readonly StateInterface $state,
  ) {}

  /**
   * Gets setup values.
   */
  public function getValues(): array {
    return array_replace_recursive(
      $this->getDefaultValues(),
      $this->getConfigDefaults(self::VALUES_KEY),
      $this->getStateArray(self::VALUES_KEY)
    );
  }

  /**
   * Merges setup values with existing runtime values and saves them.
   */
  public function mergeValues(array $values): void {
    $this->saveValues(array_replace_recursive($this->getValues(), $values));
  }

  /**
   * Saves setup values to state.
   */
  public function saveValues(array $values): void {
    $this->state->set(self::VALUES_KEY, array_replace_recursive($this->getDefaultValues(), $values));
  }

  /**
   * Gets setup status.
   */
  public function getStatus(): array {
    return array_replace_recursive(
      $this->getDefaultStatus(),
      $this->getConfigDefaults(self::STATUS_KEY),
      $this->getStateArray(self::STATUS_KEY)
    );
  }

  /**
   * Saves setup status to state.
   */
  public function saveStatus(array $status): void {
    $this->state->set(self::STATUS_KEY, array_replace_recursive($this->getDefaultStatus(), $status));
  }

  /**
   * Gets setup manifest.
   */
  public function getManifest(): array {
    return array_replace_recursive(
      $this->getDefaultManifest(),
      $this->getConfigDefaults(self::MANIFEST_KEY),
      $this->getStateArray(self::MANIFEST_KEY)
    );
  }

  /**
   * Saves setup manifest to state.
   */
  public function saveManifest(array $manifest): void {
    $this->state->set(self::MANIFEST_KEY, array_replace_recursive($this->getDefaultManifest(), $manifest));
  }

  /**
   * Resets setup status runtime state only.
   */
  public function resetStatus(): void {
    $this->state->set(self::STATUS_KEY, $this->getDefaultStatus());
  }

  /**
   * Unlocks setup runtime status.
   */
  public function unlockSetup(): void {
    $status = $this->getStatus();

    $status['completed'] = FALSE;
    $status['locked'] = FALSE;
    $status['current_step'] = 'setup_unlocked';
    $status['completed_at'] = 0;

    if (isset($status['steps']['verification'])) {
      $status['steps']['verification'] = 'pending';
    }

    $this->saveStatus($status);
  }

  /**
   * Gets config defaults.
   */
  private function getConfigDefaults(string $name): array {
    $config = $this->configFactory->get($name)->getRawData();

    return is_array($config) ? $config : [];
  }

  /**
   * Gets state array.
   */
  private function getStateArray(string $name): array {
    $value = $this->state->get($name, []);

    return is_array($value) ? $value : [];
  }

  /**
   * Gets default setup values.
   */
  private function getDefaultValues(): array {
    return [
      'mode' => 'single',
      'environment' => '',
      'site' => [
        'name' => '',
        'key' => '',
        'primary_domain' => '',
        'frontend_url' => '',
        'admin_url' => '',
        'api_url' => '',
      ],
      'contact' => [
        'company_name' => '',
        'primary_email' => '',
        'country' => '',
      ],
      'branding' => [
        'theme_color' => '#0f62fe',
        'site_title_bar' => '',
        'header_logo' => 0,
        'header_logo_alt' => '',
        'footer_logo' => 0,
        'footer_logo_alt' => '',
        'favicon_ico' => 0,
        'app_icon' => 0,
        'social_image' => 0,
        'footer_copyright' => '',
        'logo' => 0,
        'favicon' => 0,
      ],
      'setup_options' => [
        'create_default_pages' => TRUE,
        'create_default_menus' => TRUE,
        'create_default_forms' => TRUE,
        'create_default_roles' => TRUE,
        'create_demo_content' => FALSE,
      ],
      'analytics' => [
        'enabled' => FALSE,
        'measurement_id' => '',
      ],
      'extra_sites' => [],
    ];
  }

  /**
   * Gets default setup status.
   */
  private function getDefaultStatus(): array {
    return [
      'installed' => TRUE,
      'completed' => FALSE,
      'locked' => FALSE,
      'current_step' => 'not_started',
      'setup_id' => '',
      'mode' => 'single',
      'environment' => '',
      'started_at' => 0,
      'completed_at' => 0,
      'steps' => [
        'site_profile' => 'pending',
        'frontend_defaults' => 'pending',
        'roles' => 'pending',
        'pages' => 'pending',
        'menus' => 'pending',
        'forms' => 'pending',
        'content' => 'pending',
        'analytics' => 'pending',
        'verification' => 'pending',
      ],
    ];
  }

  /**
   * Gets default setup manifest.
   */
  private function getDefaultManifest(): array {
    return [
      'setup_id' => '',
      'nodes' => [],
      'webforms' => [],
      'roles' => [],
      'config' => [],
      'files' => [],
    ];
  }

}
