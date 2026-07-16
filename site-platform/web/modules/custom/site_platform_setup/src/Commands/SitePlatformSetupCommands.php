<?php

declare(strict_types=1);

namespace Drupal\site_platform_setup\Commands;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;
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
    $this->output()->writeln('- manage page component records');
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
      'pageComponents' => 0,
      'menus' => 0,
      'menuItems' => 0,
      'forms' => 0,
      'formFields' => 0,
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
      $page_summary = $this->importSitePage($site_data, $site, $page_data, $dry_run);
      $summary['pages']++;
      $summary['pageComponents'] += $page_summary['components'];
    }

    foreach (($data['menus'] ?? []) as $menu_data) {
      if (!is_array($menu_data)) {
        continue;
      }
      $menu_summary = $this->importSiteMenu($site_data, $site, $menu_data, $dry_run);
      $summary['menus']++;
      $summary['menuItems'] += $menu_summary['items'];
    }

    foreach (($data['forms'] ?? []) as $form_data) {
      if (!is_array($form_data)) {
        continue;
      }
      $form_summary = $this->importSiteForm($site_data, $site, $form_data, $dry_run);
      $summary['forms']++;
      $summary['formFields'] += $form_summary['fields'];
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
    $this->output()->writeln('Page Components: ' . $summary['pageComponents']);
    $this->output()->writeln('Site Menus: ' . $summary['menus']);
    $this->output()->writeln('Site Menu Items: ' . $summary['menuItems']);
    $this->output()->writeln('Site Forms: ' . $summary['forms']);
    $this->output()->writeln('Site Form Fields: ' . $summary['formFields']);
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
   *
   * @return array<string, int>
   *   Import summary.
   */
  private function importSitePage(array $siteData, ?NodeInterface $site, array $data, bool $dryRun): array {
    $components = is_array($data['components'] ?? NULL) ? $data['components'] : [];
    if ($dryRun) {
      return ['components' => count($components)];
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

    $page_components = [];
    foreach ($components as $component_data) {
      if (!is_array($component_data)) {
        continue;
      }
      $paragraph = $this->importPageComponent($siteData, $page_key, $component_data);
      if ($paragraph instanceof ParagraphInterface) {
        $page_components[] = [
          'target_id' => $paragraph->id(),
          'target_revision_id' => $paragraph->getRevisionId(),
        ];
      }
    }

    if ($page->hasField('field_page_components')) {
      $page->set('field_page_components', $page_components);
    }

    $page->save();

    return ['components' => count($components)];
  }

  /**
   * Imports one page component.
   *
   * @param array<string, mixed> $siteData
   *   Site data.
   * @param array<string, mixed> $data
   *   Component data.
   */
  private function importPageComponent(array $siteData, string $pageKey, array $data): ?ParagraphInterface {
    $type = (string) ($data['type'] ?? '');
    $bundle = match ($type) {
      'hero' => 'site_hero',
      'rich_text' => 'site_rich_text',
      'cta' => 'site_cta',
      default => '',
    };

    if ($bundle === '') {
      throw new \InvalidArgumentException('Unsupported component type: ' . $type);
    }

    $component_key = (string) ($data['key'] ?? '');
    if ($component_key === '') {
      throw new \InvalidArgumentException('Every component entry must include key.');
    }

    $stable_key = (string) $siteData['key'] . ':' . $pageKey . ':' . $component_key;
    $paragraph = $this->loadParagraph($bundle, $stable_key);
    $storage = $this->entityTypeManager->getStorage('paragraph');

    if (!$paragraph instanceof ParagraphInterface) {
      $paragraph = $storage->create(['type' => $bundle]);
    }

    $this->setParagraphIfFieldExists($paragraph, 'field_component_key', $stable_key);
    $this->setParagraphIfFieldExists($paragraph, 'field_component_variant', (string) ($data['variant'] ?? 'default'));
    $this->setParagraphIfFieldExists($paragraph, 'field_component_admin_label', (string) ($data['adminLabel'] ?? $component_key));
    $this->setParagraphIfFieldExists($paragraph, 'field_component_title', (string) ($data['title'] ?? ''));
    $this->setParagraphIfFieldExists($paragraph, 'field_component_summary', (string) ($data['summary'] ?? ''));
    $this->setParagraphIfFieldExists($paragraph, 'field_component_body', (string) ($data['body'] ?? ''));
    $this->setParagraphIfFieldExists($paragraph, 'field_component_media_url', (string) ($data['mediaUrl'] ?? ''));
    $this->setParagraphIfFieldExists($paragraph, 'field_component_button_label', (string) ($data['buttonLabel'] ?? ''));
    $this->setParagraphIfFieldExists($paragraph, 'field_component_button_path', (string) ($data['buttonPath'] ?? ''));

    $paragraph->save();

    return $paragraph;
  }

  /**
   * Loads one paragraph by bundle and component key.
   */
  private function loadParagraph(string $bundle, string $componentKey): ?ParagraphInterface {
    $storage = $this->entityTypeManager->getStorage('paragraph');
    $ids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', $bundle)
      ->condition('field_component_key', $componentKey)
      ->range(0, 1)
      ->execute();

    if (!$ids) {
      return NULL;
    }

    $paragraph = $storage->load(reset($ids));

    return $paragraph instanceof ParagraphInterface ? $paragraph : NULL;
  }

  /**
   * Imports or previews a Site Menu.
   *
   * @param array<string, mixed> $siteData
   *   Site data.
   * @param array<string, mixed> $data
   *   Menu data.
   *
   * @return array<string, int>
   *   Import summary.
   */
  private function importSiteMenu(array $siteData, ?NodeInterface $site, array $data, bool $dryRun): array {
    $items = is_array($data['items'] ?? NULL) ? $data['items'] : [];
    if ($dryRun) {
      return ['items' => count($items)];
    }

    if (!$site instanceof NodeInterface) {
      throw new \RuntimeException('Menu import requires a saved Site Profile.');
    }

    $storage = $this->entityTypeManager->getStorage('node');
    $menu_key = (string) ($data['key'] ?? '');
    if ($menu_key === '') {
      throw new \InvalidArgumentException('Every menu entry must include key.');
    }

    $menu = $this->loadNode('site_menu', [
      'field_site_profile.target_id' => (int) $site->id(),
      'field_menu_key' => $menu_key,
    ]);

    if (!$menu instanceof NodeInterface) {
      $menu = $storage->create([
        'type' => 'site_menu',
        'title' => (string) ($data['title'] ?? $menu_key),
        'status' => 1,
        'uid' => 1,
      ]);
    }

    $menu->setTitle((string) ($data['title'] ?? $menu_key));
    $this->setIfFieldExists($menu, 'field_site_profile', ['target_id' => $site->id()]);
    $this->setIfFieldExists($menu, 'field_menu_key', $menu_key);
    $this->setIfFieldExists($menu, 'field_menu_label', (string) ($data['label'] ?? $data['title'] ?? $menu_key));
    $this->setIfFieldExists($menu, 'field_menu_is_active', (bool) ($data['active'] ?? TRUE));
    $this->setIfFieldExists($menu, 'field_menu_weight', (int) ($data['weight'] ?? 0));
    $this->setIfFieldExists($menu, 'field_is_demo', (bool) ($data['demo'] ?? TRUE));
    $this->setIfFieldExists($menu, 'field_demo_source', 'setup_import:' . (string) $siteData['key']);
    $menu->save();

    foreach ($items as $item_data) {
      if (is_array($item_data)) {
        $this->importSiteMenuItem($siteData, $site, $menu, $item_data);
      }
    }

    return ['items' => count($items)];
  }

  /**
   * Imports one Site Menu Item.
   *
   * @param array<string, mixed> $siteData
   *   Site data.
   * @param array<string, mixed> $data
   *   Menu item data.
   */
  private function importSiteMenuItem(array $siteData, NodeInterface $site, NodeInterface $menu, array $data): void {
    $storage = $this->entityTypeManager->getStorage('node');
    $item_key = (string) ($data['key'] ?? '');
    if ($item_key === '') {
      throw new \InvalidArgumentException('Every menu item entry must include key.');
    }

    $item = $this->loadNode('site_menu_item', [
      'field_site_menu.target_id' => (int) $menu->id(),
      'field_menu_item_key' => $item_key,
    ]);

    if (!$item instanceof NodeInterface) {
      $item = $storage->create([
        'type' => 'site_menu_item',
        'title' => (string) ($data['title'] ?? $item_key),
        'status' => 1,
        'uid' => 1,
      ]);
    }

    $link_type = (string) ($data['linkType'] ?? 'page');
    $page = NULL;
    if (!empty($data['page'])) {
      $page = $this->loadNode('site_page', [
        'field_site_profile.target_id' => (int) $site->id(),
        'field_page_key' => (string) $data['page'],
      ]);
    }

    $parent = NULL;
    if (!empty($data['parent'])) {
      $parent = $this->loadNode('site_menu_item', [
        'field_site_menu.target_id' => (int) $menu->id(),
        'field_menu_item_key' => (string) $data['parent'],
      ]);
    }

    $item->setTitle((string) ($data['title'] ?? $item_key));
    $this->setIfFieldExists($item, 'field_site_profile', ['target_id' => $site->id()]);
    $this->setIfFieldExists($item, 'field_site_menu', ['target_id' => $menu->id()]);
    $this->setIfFieldExists($item, 'field_menu_item_key', $item_key);
    $this->setIfFieldExists($item, 'field_menu_title', (string) ($data['title'] ?? $item_key));
    $this->setIfFieldExists($item, 'field_menu_link_type', $link_type);
    $this->setIfFieldExists($item, 'field_menu_path', (string) ($data['path'] ?? ''));
    $this->setIfFieldExists($item, 'field_menu_external_url', (string) ($data['externalUrl'] ?? ''));
    $this->setIfFieldExists($item, 'field_menu_anchor', (string) ($data['anchor'] ?? ''));
    $this->setIfFieldExists($item, 'field_menu_target', (string) ($data['target'] ?? '_self'));
    $this->setIfFieldExists($item, 'field_menu_is_enabled', (bool) ($data['enabled'] ?? TRUE));
    $this->setIfFieldExists($item, 'field_menu_is_button', (bool) ($data['isButton'] ?? FALSE));
    $this->setIfFieldExists($item, 'field_menu_weight', (int) ($data['weight'] ?? 0));
    $this->setIfFieldExists($item, 'field_is_demo', (bool) ($data['demo'] ?? TRUE));
    $this->setIfFieldExists($item, 'field_demo_source', 'setup_import:' . (string) $siteData['key']);

    if ($page instanceof NodeInterface) {
      $this->setIfFieldExists($item, 'field_menu_page', ['target_id' => $page->id()]);
    }

    if ($parent instanceof NodeInterface) {
      $this->setIfFieldExists($item, 'field_menu_parent', ['target_id' => $parent->id()]);
    }

    $item->save();
  }

  /**
   * Imports or previews a Site Form.
   *
   * @param array<string, mixed> $siteData
   *   Site data.
   * @param array<string, mixed> $data
   *   Form data.
   *
   * @return array<string, int>
   *   Import summary.
   */
  private function importSiteForm(array $siteData, ?NodeInterface $site, array $data, bool $dryRun): array {
    $fields = is_array($data['fields'] ?? NULL) ? $data['fields'] : [];
    if ($dryRun) {
      return ['fields' => count($fields)];
    }

    if (!$site instanceof NodeInterface) {
      throw new \RuntimeException('Form import requires a saved Site Profile.');
    }

    $storage = $this->entityTypeManager->getStorage('node');
    $form_key = (string) ($data['key'] ?? '');
    if ($form_key === '') {
      throw new \InvalidArgumentException('Every form entry must include key.');
    }

    $form = $this->loadNode('site_form', [
      'field_site_profile.target_id' => (int) $site->id(),
      'field_form_key' => $form_key,
    ]);

    if (!$form instanceof NodeInterface) {
      $form = $storage->create([
        'type' => 'site_form',
        'title' => (string) ($data['title'] ?? $form_key),
        'status' => 1,
        'uid' => 1,
      ]);
    }

    $form->setTitle((string) ($data['title'] ?? $form_key));
    $this->setIfFieldExists($form, 'field_site_profile', ['target_id' => $site->id()]);
    $this->setIfFieldExists($form, 'field_form_key', $form_key);
    $this->setIfFieldExists($form, 'field_form_label', (string) ($data['label'] ?? $data['title'] ?? $form_key));
    $this->setIfFieldExists($form, 'field_form_description', (string) ($data['description'] ?? ''));
    $this->setIfFieldExists($form, 'field_form_success_message', (string) ($data['successMessage'] ?? 'Thank you.'));
    $this->setIfFieldExists($form, 'field_form_is_active', (bool) ($data['active'] ?? TRUE));
    $this->setIfFieldExists($form, 'field_is_demo', (bool) ($data['demo'] ?? TRUE));
    $this->setIfFieldExists($form, 'field_demo_source', 'setup_import:' . (string) $siteData['key']);
    $form->save();

    foreach ($fields as $field_data) {
      if (is_array($field_data)) {
        $this->importSiteFormField($siteData, $site, $form, $field_data);
      }
    }

    return ['fields' => count($fields)];
  }

  /**
   * Imports one Site Form Field.
   *
   * @param array<string, mixed> $siteData
   *   Site data.
   * @param array<string, mixed> $data
   *   Form field data.
   */
  private function importSiteFormField(array $siteData, NodeInterface $site, NodeInterface $form, array $data): void {
    $storage = $this->entityTypeManager->getStorage('node');
    $field_key = (string) ($data['key'] ?? '');
    if ($field_key === '') {
      throw new \InvalidArgumentException('Every form field entry must include key.');
    }

    $field = $this->loadNode('site_form_field', [
      'field_site_form.target_id' => (int) $form->id(),
      'field_form_field_key' => $field_key,
    ]);

    if (!$field instanceof NodeInterface) {
      $field = $storage->create([
        'type' => 'site_form_field',
        'title' => (string) ($data['label'] ?? $field_key),
        'status' => 1,
        'uid' => 1,
      ]);
    }

    $field->setTitle((string) ($data['label'] ?? $field_key));
    $this->setIfFieldExists($field, 'field_site_profile', ['target_id' => $site->id()]);
    $this->setIfFieldExists($field, 'field_site_form', ['target_id' => $form->id()]);
    $this->setIfFieldExists($field, 'field_form_field_key', $field_key);
    $this->setIfFieldExists($field, 'field_form_field_type', (string) ($data['type'] ?? 'text'));
    $this->setIfFieldExists($field, 'field_form_field_label', (string) ($data['label'] ?? $field_key));
    $this->setIfFieldExists($field, 'field_form_field_required', (bool) ($data['required'] ?? FALSE));
    $this->setIfFieldExists($field, 'field_form_field_placeholder', (string) ($data['placeholder'] ?? ''));
    $this->setIfFieldExists($field, 'field_form_field_help', (string) ($data['help'] ?? ''));
    $this->setIfFieldExists($field, 'field_form_field_options', (string) ($data['options'] ?? ''));
    $this->setIfFieldExists($field, 'field_form_field_weight', (int) ($data['weight'] ?? 0));
    $this->setIfFieldExists($field, 'field_is_demo', (bool) ($data['demo'] ?? TRUE));
    $this->setIfFieldExists($field, 'field_demo_source', 'setup_import:' . (string) $siteData['key']);
    $field->save();
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
   * Sets a paragraph field value when the field exists.
   */
  private function setParagraphIfFieldExists(ParagraphInterface $paragraph, string $field, mixed $value): void {
    if ($paragraph->hasField($field)) {
      $paragraph->set($field, $value);
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
