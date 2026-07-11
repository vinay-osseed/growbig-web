<?php

declare(strict_types=1);

namespace Drupal\site_platform_admin\Commands;

use Drupal\Component\Serialization\Yaml;
use Drupal\site_platform_admin\Service\SiteSetupRunner;
use Drupal\site_platform_admin\Service\SiteSetupStorage;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for the site setup workflow.
 */
final class SiteSetupCommands extends DrushCommands {

  /**
   * Constructs setup commands.
   */
  public function __construct(
    private readonly SiteSetupStorage $setupStorage,
    private readonly SiteSetupRunner $setupRunner,
  ) {
    parent::__construct();
  }

  /**
   * Shows setup runtime status.
   *
   * @command site-platform:setup-status
   * @aliases sp-setup-status
   */
  public function status(): void {
    $this->printArray($this->setupStorage->getStatus());
  }

  /**
   * Shows setup preview and completion readiness.
   *
   * @command site-platform:setup-preview
   * @aliases sp-setup-preview
   */
  public function preview(): void {
    $this->output()->writeln('Setup preview:');
    $this->printArray($this->setupRunner->getPreview());

    $this->output()->writeln('Completion readiness:');
    $this->printArray($this->setupRunner->getCompletionReadiness());
  }

  /**
   * Shows primary and extra setup site rows.
   *
   * @command site-platform:setup-sites
   * @aliases sp-setup-sites
   */
  public function sites(): void {
    $values = $this->setupStorage->getValues();
    $sites = [];

    $primary = $values['site'] ?? [];
    if (is_array($primary)) {
      $sites[] = [
        'type' => 'primary',
        'name' => (string) ($primary['name'] ?? ''),
        'key' => (string) ($primary['key'] ?? ''),
        'primary_domain' => (string) ($primary['primary_domain'] ?? ''),
        'frontend_url' => (string) ($primary['frontend_url'] ?? ''),
        'admin_url' => (string) ($primary['admin_url'] ?? ''),
        'api_url' => (string) ($primary['api_url'] ?? ''),
      ];
    }

    $extra_sites = $values['extra_sites'] ?? [];
    if (is_array($extra_sites)) {
      foreach ($extra_sites as $site) {
        if (!is_array($site)) {
          continue;
        }

        $sites[] = [
          'type' => 'extra',
          'name' => (string) ($site['name'] ?? ''),
          'key' => (string) ($site['key'] ?? ''),
          'primary_domain' => (string) ($site['primary_domain'] ?? ''),
          'frontend_url' => (string) ($site['frontend_url'] ?? ''),
          'admin_url' => (string) ($site['admin_url'] ?? ''),
          'api_url' => (string) ($site['api_url'] ?? ''),
        ];
      }
    }

    $this->printArray([
      'count' => count($sites),
      'items' => $sites,
    ]);
  }

  /**
   * Imports setup runtime values from a YAML file.
   *
   * @param string $file
   *   Path to setup YAML file.
   *
   * @command site-platform:setup-import
   * @aliases sp-setup-import
   */
  public function import(string $file = 'setup/site.yml'): void {
    $resolved_file = $this->resolveSetupFilePath($file);

    if ($resolved_file === NULL) {
      throw new \InvalidArgumentException('Setup YAML file not found: ' . $file);
    }

    $values = Yaml::decode((string) file_get_contents($resolved_file));

    if (!is_array($values)) {
      throw new \InvalidArgumentException('Setup YAML file must contain a mapping.');
    }

    $this->validateImportedValues($values);

    $this->setupStorage->mergeValues($values);

    $this->output()->writeln('Setup values imported from: ' . $resolved_file);
    $this->printArray($this->setupRunner->getPreview());
  }

  /**
   * Validates imported setup values before saving.
   */
  private function validateImportedValues(array $values): void {
    $errors = [];

    foreach ([
      'mode',
      'site.name',
      'site.key',
      'site.primary_domain',
      'site.frontend_url',
      'site.admin_url',
      'site.api_url',
      'contact.company_name',
      'contact.primary_email',
      'contact.country',
    ] as $path) {
      $this->validateRequiredString($values, $path, $errors);
    }

    $mode = $this->getImportValue($values, 'mode');
    if (is_string($mode) && !in_array($mode, ['single', 'multi'], TRUE)) {
      $errors[] = 'mode must be single or multi.';
    }

    foreach ([
      'site.frontend_url',
      'site.admin_url',
      'site.api_url',
    ] as $path) {
      $value = $this->getImportValue($values, $path);
      if (is_string($value) && filter_var($value, FILTER_VALIDATE_URL) === FALSE) {
        $errors[] = $path . ' must be a valid URL.';
      }
    }

    $email = $this->getImportValue($values, 'contact.primary_email');
    if (is_string($email) && filter_var($email, FILTER_VALIDATE_EMAIL) === FALSE) {
      $errors[] = 'contact.primary_email must be a valid email address.';
    }

    foreach ([
      'setup_options.create_default_pages',
      'setup_options.create_default_menus',
      'setup_options.create_default_forms',
      'setup_options.create_default_roles',
      'setup_options.create_demo_content',
    ] as $path) {
      $value = $this->getImportValue($values, $path);
      if (!is_bool($value)) {
        $errors[] = $path . ' must be true or false.';
      }
    }

    if (array_key_exists('analytics', $values) && !is_array($values['analytics'])) {
      $errors[] = 'analytics must be a mapping.';
    }
    elseif (is_array($values['analytics'] ?? NULL)) {
      $enabled = $this->getImportValue($values, 'analytics.enabled');
      if ($enabled !== NULL && !is_bool($enabled)) {
        $errors[] = 'analytics.enabled must be true or false.';
      }

      $measurement_id = $this->getImportValue($values, 'analytics.measurement_id');
      if ($measurement_id !== NULL && !is_string($measurement_id)) {
        $errors[] = 'analytics.measurement_id must be a string.';
      }
    }

    if (array_key_exists('extra_sites', $values) && !is_array($values['extra_sites'])) {
      $errors[] = 'extra_sites must be a list.';
    }
    elseif (is_array($values['extra_sites'] ?? NULL)) {
      if (!array_is_list($values['extra_sites'])) {
        $errors[] = 'extra_sites must be a list.';
      }

      $seen_keys = [];
      $seen_domains = [];

      $primary_key = $this->getImportValue($values, 'site.key');
      if (is_string($primary_key) && trim($primary_key) !== '') {
        $seen_keys[] = $primary_key;
      }

      $primary_domain = $this->getImportValue($values, 'site.primary_domain');
      if (is_string($primary_domain) && trim($primary_domain) !== '') {
        $seen_domains[] = $primary_domain;
      }

      foreach ($values['extra_sites'] as $index => $site) {
        if (!is_array($site)) {
          $errors[] = 'extra_sites.' . $index . ' must be a mapping.';
          continue;
        }

        foreach (['name', 'key', 'primary_domain'] as $field) {
          if (!array_key_exists($field, $site) || !is_string($site[$field]) || trim($site[$field]) === '') {
            $errors[] = 'extra_sites.' . $index . '.' . $field . ' is required and must be a non-empty string.';
          }
        }

        foreach (['frontend_url', 'admin_url', 'api_url'] as $field) {
          if (
            array_key_exists($field, $site)
            && is_string($site[$field])
            && trim($site[$field]) !== ''
            && filter_var($site[$field], FILTER_VALIDATE_URL) === FALSE
          ) {
            $errors[] = 'extra_sites.' . $index . '.' . $field . ' must be a valid URL.';
          }
        }

        if (isset($site['key']) && is_string($site['key']) && trim($site['key']) !== '') {
          if (in_array($site['key'], $seen_keys, TRUE)) {
            $errors[] = 'extra_sites.' . $index . '.key must be unique.';
          }
          $seen_keys[] = $site['key'];
        }

        if (isset($site['primary_domain']) && is_string($site['primary_domain']) && trim($site['primary_domain']) !== '') {
          if (in_array($site['primary_domain'], $seen_domains, TRUE)) {
            $errors[] = 'extra_sites.' . $index . '.primary_domain must be unique.';
          }
          $seen_domains[] = $site['primary_domain'];
        }
      }
    }

    if ($errors !== []) {
      throw new \InvalidArgumentException(
        "Invalid setup YAML:\n- " . implode("\n- ", $errors)
      );
    }
  }

  /**
   * Validates a required string field.
   */
  private function validateRequiredString(array $values, string $path, array &$errors): void {
    $value = $this->getImportValue($values, $path);

    if (!is_string($value) || trim($value) === '') {
      $errors[] = $path . ' is required and must be a non-empty string.';
    }
  }

  /**
   * Gets a nested import value.
   */
  private function getImportValue(array $values, string $path): mixed {
    $current = $values;

    foreach (explode('.', $path) as $part) {
      if (!is_array($current) || !array_key_exists($part, $current)) {
        return NULL;
      }

      $current = $current[$part];
    }

    return $current;
  }

  /**
   * Resolves setup YAML file path.
   */
  private function resolveSetupFilePath(string $file): ?string {
    $candidates = [
      $file,
      getcwd() . '/' . $file,
      dirname(DRUPAL_ROOT) . '/' . $file,
      dirname(DRUPAL_ROOT, 2) . '/' . $file,
    ];

    foreach ($candidates as $candidate) {
      if (is_file($candidate)) {
        return $candidate;
      }
    }

    return NULL;
  }

  /**
   * Runs the safe setup runner.
   *
   * @command site-platform:setup-run
   * @aliases sp-setup-run
   */
  public function runSetup(): void {
    $this->printArray($this->setupRunner->run());
  }

  /**
   * Completes and locks setup.
   *
   * @command site-platform:setup-complete
   * @aliases sp-setup-complete
   */
  public function complete(): void {
    $this->printArray($this->setupRunner->completeAndLock());
  }

  /**
   * Unlocks setup.
   *
   * @command site-platform:setup-unlock
   * @aliases sp-setup-unlock
   */
  public function unlock(): void {
    $this->setupStorage->unlockSetup();
    $this->output()->writeln('Setup unlocked.');
  }

  /**
   * Resets setup status only.
   *
   * @command site-platform:setup-reset-status
   * @aliases sp-setup-reset-status
   */
  public function resetStatus(): void {
    $this->setupStorage->resetStatus();
    $this->output()->writeln('Setup status reset. Existing data was not deleted.');
  }

  /**
   * Prints an array.
   */
  private function printArray(array $data): void {
    $this->output()->writeln(print_r($data, TRUE));
  }

}
