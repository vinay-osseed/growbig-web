<?php

declare(strict_types=1);

namespace Drupal\site_platform_setup\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;
use Drush\Commands\DrushCommands;

/**
 * Drush commands for Site Platform setup lifecycle.
 */
final class SitePlatformSetupCommands extends DrushCommands {

  /**
   * Node types managed by the current setup foundation.
   *
   * @var array<string, string>
   */
  private const MANAGED_NODE_TYPES = [
    'site_profile' => 'Site Profile',
    'site_page' => 'Site Page',
    'site_menu' => 'Site Menu',
    'site_menu_item' => 'Site Menu Item',
    'site_form' => 'Site Form',
    'site_form_field' => 'Site Form Field',
    'site_form_submission' => 'Site Form Submission',
    'site_content_block' => 'Site Content Block',
  ];

  /**
   * Constructs setup commands.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly StateInterface $state,
  ) {
    parent::__construct();
  }

  /**
   * Shows the setup runner plan and current entity counts.
   *
   * @command site-platform:setup-preview
   * @aliases sp-preview
   */
  public function preview(): void {
    $this->output()->writeln('Setup runner preview');
    $this->output()->writeln('====================');
    $this->output()->writeln('');
    $this->output()->writeln('Planned setup lifecycle:');
    $this->output()->writeln('- resolve Site Profile records');
    $this->output()->writeln('- manage Site Page records');
    $this->output()->writeln('- manage Site Menu and Site Menu Item records');
    $this->output()->writeln('- manage Site Form and Site Form Field records');
    $this->output()->writeln('- manage reusable Site Content Block records');
    $this->output()->writeln('- track demo records for safe reset');
    $this->output()->writeln('');
    $this->printCounts();
  }

  /**
   * Records a setup runner foundation run.
   *
   * @command site-platform:setup-run
   * @aliases sp-run
   */
  public function run(): void {
    $summary = $this->buildCounts();

    $this->state->set('site_platform_setup.last_run', [
      'status' => 'foundation_ready',
      'ranAt' => gmdate('c'),
      'summary' => $summary,
    ]);

    $this->output()->writeln('Setup runner foundation recorded successfully.');
    $this->output()->writeln('');
    $this->printCounts($summary);
  }

  /**
   * Shows setup runner status.
   *
   * @command site-platform:setup-status
   * @aliases sp-status
   */
  public function status(): void {
    $last_run = $this->state->get('site_platform_setup.last_run');

    $this->output()->writeln('Setup runner status');
    $this->output()->writeln('===================');
    $this->output()->writeln('');

    if (is_array($last_run)) {
      $this->output()->writeln('Status: ' . (string) ($last_run['status'] ?? 'unknown'));
      $this->output()->writeln('Last run: ' . (string) ($last_run['ranAt'] ?? 'unknown'));
    }
    else {
      $this->output()->writeln('Status: not_run');
      $this->output()->writeln('Last run: never');
    }

    $this->output()->writeln('');
    $this->printCounts();
  }

  /**
   * Previews or deletes demo content.
   *
   * @command site-platform:setup-reset-demo
   * @aliases sp-reset-demo
   * @option execute Actually delete demo content. Without this option the command is dry-run.
   */
  public function resetDemo(array $options = ['execute' => FALSE]): void {
    $execute = (bool) ($options['execute'] ?? FALSE);
    $demo_ids = $this->findDemoNodeIds();

    $this->output()->writeln($execute ? 'Demo reset execute mode' : 'Demo reset dry-run');
    $this->output()->writeln($execute ? '=======================' : '==================');
    $this->output()->writeln('');

    if ($demo_ids === []) {
      $this->output()->writeln('No demo records found.');
      return;
    }

    foreach ($demo_ids as $type => $ids) {
      $label = self::MANAGED_NODE_TYPES[$type] ?? $type;
      $this->output()->writeln(sprintf('%s: %d', $label, count($ids)));
    }

    if (!$execute) {
      $this->output()->writeln('');
      $this->output()->writeln('No records were deleted. Add --execute to delete demo content.');
      return;
    }

    $storage = $this->entityTypeManager->getStorage('node');
    $deleted = 0;

    foreach ($demo_ids as $ids) {
      $nodes = $storage->loadMultiple($ids);
      $deleted += count($nodes);
      $storage->delete($nodes);
    }

    $this->output()->writeln('');
    $this->output()->writeln(sprintf('Deleted %d demo records.', $deleted));
  }

  /**
   * Prints entity counts.
   *
   * @param array<string, int>|null $counts
   *   Optional prebuilt counts.
   */
  private function printCounts(?array $counts = NULL): void {
    $counts ??= $this->buildCounts();

    $this->output()->writeln('Current entity counts:');

    foreach (self::MANAGED_NODE_TYPES as $type => $label) {
      $this->output()->writeln(sprintf('- %s: %d', $label, $counts[$type] ?? 0));
    }
  }

  /**
   * Builds counts for managed node types.
   *
   * @return array<string, int>
   *   Counts keyed by node type.
   */
  private function buildCounts(): array {
    $storage = $this->entityTypeManager->getStorage('node');
    $counts = [];

    foreach (self::MANAGED_NODE_TYPES as $type => $label) {
      unset($label);
      if (!$this->nodeTypeExists($type)) {
        $counts[$type] = 0;
        continue;
      }

      $ids = $storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('type', $type)
        ->execute();

      $counts[$type] = count($ids);
    }

    return $counts;
  }

  /**
   * Finds demo node IDs for known setup-managed node types.
   *
   * @return array<string, array<int, int>>
   *   Node IDs grouped by node type.
   */
  private function findDemoNodeIds(): array {
    $storage = $this->entityTypeManager->getStorage('node');
    $demo_ids = [];

    foreach (array_keys(self::MANAGED_NODE_TYPES) as $type) {
      if (!$this->nodeTypeExists($type) || !$this->fieldExists($type, 'field_is_demo')) {
        continue;
      }

      $ids = $storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('type', $type)
        ->condition('field_is_demo', 1)
        ->execute();

      if ($ids) {
        $demo_ids[$type] = array_map('intval', array_values($ids));
      }
    }

    return $demo_ids;
  }

  /**
   * Returns TRUE when a node type exists.
   */
  private function nodeTypeExists(string $type): bool {
    try {
      return (bool) $this->entityTypeManager
        ->getStorage('node_type')
        ->load($type);
    }
    catch (\Throwable) {
      return FALSE;
    }
  }

  /**
   * Returns TRUE when a field exists on a node type.
   */
  private function fieldExists(string $type, string $field): bool {
    try {
      return (bool) $this->entityTypeManager
        ->getStorage('field_config')
        ->load('node.' . $type . '.' . $field);
    }
    catch (\Throwable) {
      return FALSE;
    }
  }

}
