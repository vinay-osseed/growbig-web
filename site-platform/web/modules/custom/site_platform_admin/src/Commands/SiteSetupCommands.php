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

    $this->setupStorage->mergeValues($values);

    $this->output()->writeln('Setup values imported from: ' . $resolved_file);
    $this->printArray($this->setupRunner->getPreview());
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
