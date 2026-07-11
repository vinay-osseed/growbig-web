<?php

declare(strict_types=1);

namespace Drupal\site_platform_admin\Commands;

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
