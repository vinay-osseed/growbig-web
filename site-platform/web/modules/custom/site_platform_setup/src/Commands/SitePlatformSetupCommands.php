<?php

declare(strict_types=1);

namespace Drupal\site_platform_setup\Commands;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\node\NodeInterface;
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
    $this->output()->writeln('- import setup YAML files');
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
   * Imports a setup YAML file.
   *
   * @command site-platform:setup-import
   * @aliases sp-import
   * @param string $file Path to setup YAML file.
   * @option dry-run Parse and preview the import without saving records.
   */
  public function importFile(string $file, array $options = ['dry-run' => FALSE]): void {
    $dry_run = (bool) ($options['dry-run'] ?? FALSE);
    $path = $this->resolveSetupPath($file);

    if ($path === '' || !is_file($path)) {
      throw new \InvalidArgumentException('Setup file not found: ' . $file);
    }

    $data = Yaml::decode((string) file_get_contents($path));
    if (!is_array($data)) {
      throw new \InvalidArgumentException('Setup file must contain a YAML mapping.');
    }

    $site_data = $data['site'] ?? NULL;
    if (!is_array($site_data) || empty($site_data['key'])) {
      throw new \InvalidArgumentException('Setup file must include site.key.');
    }

    $summary = [
      'siteProfile' => 0,
      'pages' => 0,
      'contentBlocks' => 0,
    ];

    $site = $this->importSiteProfile($site_data, $dry_run);
    $summary['siteProfile'] = 1;

    if (!$dry_run && !$site instanceof NodeInterface) {
      throw new \RuntimeException('Site Profile import failed.');
    }

    foreach (($data['pages'] ?? []) as $page_data) {
      if (!is_array($page_data)) {
        continue;
      }
      $this->importSitePage($site_data, $site, $page_data, $dry_run);
      $summary['pages']++;
    }

    foreach (($data['content'] ?? []) as $content_data) {
      if (!is_array($content_data)) {
        continue;
      }
      $this->importContentBlock($site_data, $site, $content_data, $dry_run);
      $summary['contentBlocks']++;
    }

    if (!$dry_run) {
      $this->state->set('site_platform_setup.last_import', [
        'file' => $path,
        'siteKey' => (string) $site_data['key'],
        'ranAt' => gmdate('c'),
        'summary' => $summary,
      ]);
    }

    $this->output()->writeln($dry_run ? 'Setup YAML dry-run completed.' : 'Setup YAML import completed.');
    $this->output()->writeln('File: ' . $path);
    $this->output()->writeln('Site key: ' . (string) $site_data['key']);
    $this->output()->writeln('Site Profile: ' . $summary['siteProfile']);
    $this->output()->writeln('Site Pages: ' . $summary['pages']);
    $this->output()->writeln('Site Content Blocks: ' . $summary['contentBlocks']);
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
   * Imports or previews a Site Profile.
   *
   * @param array<string, mixed> $data
   *   Site data.
   */
  private function importSiteProfile(array $data, bool $dryRun): ?NodeInterface {
    if ($dryRun) {
      return NULL;
    }

    $storage = $this->entityTypeManager->getStorage('node');
    $site_key = (string) $data['key'];

    $site = $this->loadNode('site_profile', [
      'field_site_key' => $site_key,
    ]);

    if (!$site instanceof NodeInterface) {
      $site = $storage->create([
        'type' => 'site_profile',
        'title' => (string) ($data['name'] ?? $site_key),
        'status' => 1,
        'uid' => 1,
      ]);
    }

    $site->setTitle((string) ($data['name'] ?? $site_key));
    $this->setIfFieldExists($site, 'field_site_key', $site_key);
    $this->setIfFieldExists($site, 'field_is_active', (bool) ($data['active'] ?? TRUE));
    $this->setIfFieldExists($site, 'field_is_default', (bool) ($data['isDefault'] ?? FALSE));
    $this->setIfFieldExists($site, 'field_default_language', (string) ($data['defaultLanguage'] ?? 'en'));
    $this->setListIfFieldExists($site, 'field_enabled_languages', $data['enabledLanguages'] ?? ['en']);
    $this->setIfFieldExists($site, 'field_timezone', (string) ($data['timezone'] ?? 'UTC'));

    $domains = is_array($data['domains'] ?? NULL) ? $data['domains'] : [];
    $this->setListIfFieldExists($site, 'field_frontend_domains', $domains['frontend'] ?? []);
    $this->setListIfFieldExists($site, 'field_api_domains', $domains['api'] ?? []);
    $this->setListIfFieldExists($site, 'field_admin_domains', $domains['admin'] ?? []);

    $site->save();

    return $site;
  }

  /**
   * Imports or previews a Site Page.
   *
   * @param array<string, mixed> $siteData
   *   Site data.
   * @param array<string, mixed> $data
   *   Page data.
   */
  private function importSitePage(array $siteData, ?NodeInterface $site, array $data, bool $dryRun): void {
    if ($dryRun) {
      return;
    }

    if (!$site instanceof NodeInterface) {
      throw new \RuntimeException('Site Page import requires a saved Site Profile.');
    }

    $storage = $this->entityTypeManager->getStorage('node');
    $page_key = (string) ($data['key'] ?? '');
    if ($page_key === '') {
      throw new \InvalidArgumentException('Every page entry must include key.');
    }

    $page = $this->loadNode('site_page', [
      'field_site_profile.target_id' => (int) $site->id(),
      'field_page_key' => $page_key,
    ]);

    if (!$page instanceof NodeInterface) {
      $page = $storage->create([
        'type' => 'site_page',
        'title' => (string) ($data['title'] ?? $page_key),
        'status' => 1,
        'uid' => 1,
      ]);
    }

    $page->setTitle((string) ($data['title'] ?? $page_key));
    $this->setIfFieldExists($page, 'field_site_profile', ['target_id' => $site->id()]);
    $this->setIfFieldExists($page, 'field_page_key', $page_key);
    $this->setIfFieldExists($page, 'field_page_slug', (string) ($data['slug'] ?? $page_key));
    $this->setIfFieldExists($page, 'field_page_path', (string) ($data['path'] ?? '/' . $page_key));
    $this->setIfFieldExists($page, 'field_page_template', (string) ($data['template'] ?? 'default'));
    $this->setIfFieldExists($page, 'field_seo_title', (string) ($data['seoTitle'] ?? ($data['title'] ?? $page_key)));
    $this->setIfFieldExists($page, 'field_seo_description', (string) ($data['seoDescription'] ?? ''));
    $this->setIfFieldExists($page, 'field_page_weight', (int) ($data['weight'] ?? 0));
    $this->setIfFieldExists($page, 'field_is_demo', (bool) ($data['demo'] ?? TRUE));
    $this->setIfFieldExists($page, 'field_demo_source', 'setup_import:' . (string) $siteData['key']);

    $page->save();
  }

  /**
   * Imports or previews a Site Content Block.
   *
   * @param array<string, mixed> $siteData
   *   Site data.
   * @param array<string, mixed> $data
   *   Content block data.
   */
  private function importContentBlock(array $siteData, ?NodeInterface $site, array $data, bool $dryRun): void {
    if ($dryRun) {
      return;
    }

    if (!$site instanceof NodeInterface) {
      throw new \RuntimeException('Content import requires a saved Site Profile.');
    }

    $storage = $this->entityTypeManager->getStorage('node');
    $source = (string) ($data['source'] ?? 'global');
    $key = (string) ($data['key'] ?? '');
    if ($key === '') {
      throw new \InvalidArgumentException('Every content entry must include key.');
    }

    $content = $this->loadNode('site_content_block', [
      'field_site_profile.target_id' => (int) $site->id(),
      'field_content_source' => $source,
      'field_content_key' => $key,
    ]);

    if (!$content instanceof NodeInterface) {
      $content = $storage->create([
        'type' => 'site_content_block',
        'title' => (string) ($data['title'] ?? $key),
        'status' => 1,
        'uid' => 1,
      ]);
    }

    $content->setTitle((string) ($data['title'] ?? $key));
    $this->setIfFieldExists($content, 'field_site_profile', ['target_id' => $site->id()]);
    $this->setIfFieldExists($content, 'field_content_source', $source);
    $this->setIfFieldExists($content, 'field_content_key', $key);
    $this->setIfFieldExists($content, 'field_content_label', (string) ($data['label'] ?? $data['title'] ?? $key));
    $this->setIfFieldExists($content, 'field_content_variant', (string) ($data['variant'] ?? 'default'));
    $this->setIfFieldExists($content, 'field_content_summary', (string) ($data['summary'] ?? ''));
    $this->setIfFieldExists($content, 'field_content_body', (string) ($data['body'] ?? ''));
    $this->setIfFieldExists($content, 'field_content_weight', (int) ($data['weight'] ?? 0));
    $this->setIfFieldExists($content, 'field_content_is_active', TRUE);
    $this->setIfFieldExists($content, 'field_is_demo', (bool) ($data['demo'] ?? TRUE));
    $this->setIfFieldExists($content, 'field_demo_source', 'setup_import:' . (string) $siteData['key']);

    $content->save();
  }

  /**
   * Loads one node by conditions.
   *
   * @param array<string, mixed> $conditions
   *   Query conditions.
   */
  private function loadNode(string $type, array $conditions): ?NodeInterface {
    $storage = $this->entityTypeManager->getStorage('node');
    $query = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', $type)
      ->range(0, 1);

    foreach ($conditions as $field => $value) {
      $query->condition($field, $value);
    }

    $ids = $query->execute();
    if (!$ids) {
      return NULL;
    }

    $node = $storage->load(reset($ids));

    return $node instanceof NodeInterface ? $node : NULL;
  }

  /**
   * Sets a field value when the field exists.
   */
  private function setIfFieldExists(NodeInterface $node, string $field, mixed $value): void {
    if ($node->hasField($field)) {
      $node->set($field, $value);
    }
  }

  /**
   * Sets a multi-value string field when the field exists.
   *
   * @param mixed $values
   *   Scalar or array values.
   */
  private function setListIfFieldExists(NodeInterface $node, string $field, mixed $values): void {
    if (!$node->hasField($field)) {
      return;
    }

    if (!is_array($values)) {
      $values = [$values];
    }

    $items = [];
    foreach ($values as $value) {
      if ($value === NULL || $value === '') {
        continue;
      }
      $items[] = ['value' => (string) $value];
    }

    $node->set($field, $items);
  }

  /**
   * Resolves a setup file path.
   */
  private function resolveSetupPath(string $file): string {
    if ($file === '') {
      return '';
    }

    if (str_starts_with($file, '/') && file_exists($file)) {
      return $file;
    }

    $candidates = [
      getcwd() . '/' . $file,
      DRUPAL_ROOT . '/../' . $file,
      DRUPAL_ROOT . '/../../' . $file,
    ];

    foreach ($candidates as $candidate) {
      if (file_exists($candidate)) {
        return realpath($candidate) ?: $candidate;
      }
    }

    return $file;
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
