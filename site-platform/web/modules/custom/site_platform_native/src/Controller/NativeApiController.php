<?php

namespace Drupal\site_platform_native\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\media\MediaInterface;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Clean, coarse-grained Site Platform APIs.
 */
final class NativeApiController extends ControllerBase {

  /**
   * Bootstrap API: site, brand, analytics, and main navigation.
   */
  public function bootstrap(Request $request): JsonResponse {
    $site = $this->resolveSite($request);
    return $this->json([
      'site' => $this->normalizeSite($site),
      'navigation' => [
        'main' => $this->menuTree('main'),
        'footer' => $this->menuTree('footer'),
      ],
      'analytics' => $this->siteAnalytics($site),
      'api' => [
        'bootstrap' => '/api/v2/bootstrap?site=' . $this->siteKey($site),
        'pages_index' => '/api/v2/pages?site=' . $this->siteKey($site),
        'page_detail' => '/api/v2/pages/{slug}?site=' . $this->siteKey($site),
        'navigation' => '/api/v2/navigation?site=' . $this->siteKey($site) . '&menu=main',
        'forms_index' => '/api/v2/forms?site=' . $this->siteKey($site),
        'form_detail' => '/api/v2/forms/{webform_id}?site=' . $this->siteKey($site),
        'datasets_index' => '/api/v2/datasets?site=' . $this->siteKey($site),
        'dataset_detail' => '/api/v2/datasets/{key}?site=' . $this->siteKey($site),
      ],
      'enabled_features' => $this->enabledFeatureSummary(),
    ]);
  }


  /**
   * Page index API: all pages for a site in one summary request.
   *
   * Query params:
   * - site: Site Profile key.
   * - include=full: include page sections in the index payload.
   */
  public function pages(Request $request): JsonResponse {
    $site = $this->resolveSite($request);
    $include = trim((string) $request->query->get('include', 'summary'));
    $pages = $this->loadPages($site);
    $items = [];
    foreach ($pages as $page) {
      if ($page instanceof NodeInterface) {
        $items[] = $this->normalizePageIndexItem($page, $include === 'full');
      }
    }
    return $this->json([
      'site' => ['key' => $this->siteKey($site), 'label' => $site?->label()],
      'count' => count($items),
      'include' => $include === 'full' ? 'full' : 'summary',
      'items' => $items,
      'hint' => 'Use /api/v2/pages/{slug}?site=' . $this->siteKey($site) . ' for a single page detail payload.',
    ]);
  }

  /**
   * Complete page API: page + SEO + sections in one request.
   */
  public function page(Request $request, string $slug): JsonResponse {
    $site = $this->resolveSite($request);
    $page = $this->loadPage($site, $slug);
    if (!$page) {
      return $this->json(['error' => 'Page not found', 'slug' => $slug], 404);
    }
    return $this->json([
      'site' => ['key' => $this->siteKey($site), 'label' => $site?->label()],
      'page' => $this->normalizePage($page),
      'seo' => $this->normalizeSeo($page),
      'sections' => $this->normalizeParagraphField($page, 'field_page_components'),
      'links' => $this->normalizePageLinks($page, $site),
    ]);
  }

  /**
   * Navigation API from Drupal core menus.
   */
  public function navigation(Request $request): JsonResponse {
    $menu = $request->query->get('menu', 'main');
    return $this->json([
      'menu' => $menu,
      'items' => $this->menuTree((string) $menu),
    ]);
  }


  /**
   * Webform index API.
   */
  public function forms(Request $request): JsonResponse {
    if (!$this->entityTypeManager()->hasDefinition('webform')) {
      return $this->json(['count' => 0, 'items' => []]);
    }
    $items = [];
    foreach ($this->entityTypeManager()->getStorage('webform')->loadMultiple() as $webform) {
      $id = method_exists($webform, 'id') ? (string) $webform->id() : '';
      if ($id === '') {
        continue;
      }
      $items[] = [
        'id' => $id,
        'title' => method_exists($webform, 'label') ? (string) $webform->label() : $id,
        'status' => method_exists($webform, 'isOpen') && $webform->isOpen() ? 'open' : 'closed',
        'api' => '/api/v2/forms/' . rawurlencode($id) . '?site=' . rawurlencode((string) $request->query->get('site', '')),
        'submit_url' => '/api/v1/forms/' . rawurlencode($id) . '/submit?site=' . rawurlencode((string) $request->query->get('site', '')),
      ];
    }
    return $this->json([
      'count' => count($items),
      'items' => $items,
    ]);
  }

  /**
   * Webform schema API.
   */
  public function form(Request $request, string $webform_id): JsonResponse {
    if (!$this->entityTypeManager()->hasDefinition('webform')) {
      return $this->json(['error' => 'Webform module is not available'], 404);
    }
    $webform = $this->entityTypeManager()->getStorage('webform')->load($webform_id);
    if (!$webform) {
      return $this->json(['error' => 'Webform not found', 'id' => $webform_id], 404);
    }
    $elements = method_exists($webform, 'getElementsInitializedAndFlattened') ? $webform->getElementsInitializedAndFlattened() : [];
    return $this->json([
      'id' => $webform->id(),
      'title' => $webform->label(),
      'status' => method_exists($webform, 'isOpen') && $webform->isOpen() ? 'open' : 'closed',
      'submit_url' => '/api/v1/forms/' . $webform->id() . '/submit?site=' . (string) $request->query->get('site', ''),
      'elements' => $this->normalizeWebformElements($elements),
    ]);
  }


  /**
   * Dataset index API.
   */
  public function datasets(Request $request): JsonResponse {
    $site = $this->resolveSite($request);
    $datasets = $this->loadDatasets($site);
    $items = [];
    foreach ($datasets as $dataset) {
      if ($dataset instanceof NodeInterface) {
        $meta = $this->normalizeDataset($dataset);
        $meta['api'] = '/api/v2/datasets/' . rawurlencode((string) $meta['key']) . '?site=' . rawurlencode($this->siteKey($site));
        $meta['items_count'] = $dataset->hasField('field_dataset_items') && !$dataset->get('field_dataset_items')->isEmpty() ? $dataset->get('field_dataset_items')->count() : 0;
        $items[] = $meta;
      }
    }
    return $this->json([
      'site' => ['key' => $this->siteKey($site), 'label' => $site?->label()],
      'count' => count($items),
      'items' => $items,
    ]);
  }

  /**
   * Flexible dataset API.
   */
  public function dataset(Request $request, string $key): JsonResponse {
    $site = $this->resolveSite($request);
    $dataset = $this->loadDataset($site, $key);
    if (!$dataset) {
      return $this->json(['error' => 'Dataset not found', 'key' => $key], 404);
    }
    return $this->json([
      'site' => ['key' => $this->siteKey($site), 'label' => $site?->label()],
      'dataset' => $this->normalizeDataset($dataset),
      'items' => $this->normalizeParagraphField($dataset, 'field_dataset_items'),
    ]);
  }

  /**
   * Careers API, backed by the careers dataset when available.
   */
  public function careers(Request $request): JsonResponse {
    $site = $this->resolveSite($request);
    $dataset = $this->loadDataset($site, 'careers');
    return $this->json([
      'site' => ['key' => $this->siteKey($site), 'label' => $site?->label()],
      'careers' => $dataset ? $this->normalizeParagraphField($dataset, 'field_dataset_items') : [],
    ]);
  }

  /**
   * Resolve site by query `site`, active Domain, or first active Site Profile.
   */
  private function resolveSite(Request $request): ?NodeInterface {
    $site_key = trim((string) $request->query->get('site', ''));
    if ($site_key !== '') {
      $site = $this->loadSiteByKey($site_key);
      if ($site) {
        return $site;
      }
    }

    $host = $request->getHost();
    $site = $this->loadSiteByDomainHost($host);
    if ($site) {
      return $site;
    }

    $storage = $this->entityTypeManager()->getStorage('node');
    $ids = $storage->getQuery()->accessCheck(FALSE)->condition('type', 'site_profile')->range(0, 1)->execute();
    $sites = $ids ? $storage->loadMultiple($ids) : [];
    $first = reset($sites);
    return $first instanceof NodeInterface ? $first : NULL;
  }

  /**
   * Load a Site Profile by key.
   */
  private function loadSiteByKey(string $site_key): ?NodeInterface {
    $storage = $this->entityTypeManager()->getStorage('node');
    $query = $storage->getQuery()->accessCheck(FALSE)->condition('type', 'site_profile');
    if ($this->fieldExists('node', 'site_profile', 'field_site_key')) {
      $query->condition('field_site_key', $site_key);
    }
    else {
      $query->condition('title', $site_key, 'CONTAINS');
    }
    $ids = $query->range(0, 1)->execute();
    $nodes = $ids ? $storage->loadMultiple($ids) : [];
    $site = reset($nodes);
    return $site instanceof NodeInterface ? $site : NULL;
  }

  /**
   * Load Site Profile mapped to a Domain host or Domain Alias host.
   */
  private function loadSiteByDomainHost(string $host): ?NodeInterface {
    if (!$this->fieldExists('node', 'site_profile', 'field_site_domain')) {
      return NULL;
    }
    if (!$this->entityTypeManager()->hasDefinition('domain')) {
      return NULL;
    }
    $domain_ids = $this->domainIdsForHost($host);
    if (!$domain_ids) {
      return NULL;
    }
    $storage = $this->entityTypeManager()->getStorage('node');
    foreach ($domain_ids as $domain_id) {
      $ids = $storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('type', 'site_profile')
        ->condition('field_site_domain.target_id', $domain_id)
        ->range(0, 1)
        ->execute();
      $nodes = $ids ? $storage->loadMultiple($ids) : [];
      $site = reset($nodes);
      if ($site instanceof NodeInterface) {
        return $site;
      }
    }
    return NULL;
  }

  /**
   * Finds Domain IDs matching a host, including Domain Alias when available.
   *
   * @return string[]
   *   Domain entity IDs.
   */
  private function domainIdsForHost(string $host): array {
    $host = strtolower(trim($host));
    $ids = [];
    try {
      $domains = $this->entityTypeManager()->getStorage('domain')->loadMultiple();
      foreach ($domains as $domain) {
        $hostname = method_exists($domain, 'getHostname') ? $domain->getHostname() : ($domain->hostname ?? '');
        if ($hostname && strtolower((string) $hostname) === $host) {
          $ids[] = (string) $domain->id();
        }
      }
    }
    catch (\Throwable) {
      return $ids;
    }

    if ($this->entityTypeManager()->hasDefinition('domain_alias')) {
      try {
        $aliases = $this->entityTypeManager()->getStorage('domain_alias')->loadMultiple();
        foreach ($aliases as $alias) {
          $pattern = '';
          if (method_exists($alias, 'getPattern')) {
            $pattern = (string) $alias->getPattern();
          }
          elseif (isset($alias->pattern)) {
            $pattern = (string) $alias->pattern;
          }
          $pattern = strtolower(trim($pattern));
          if ($pattern === '' || $pattern !== $host) {
            continue;
          }
          if (method_exists($alias, 'getDomainId')) {
            $ids[] = (string) $alias->getDomainId();
          }
          elseif (method_exists($alias, 'getDomain')) {
            $domain = $alias->getDomain();
            if ($domain) {
              $ids[] = (string) $domain->id();
            }
          }
        }
      }
      catch (\Throwable) {
        // Domain Alias is optional; ignore unexpected storage shape.
      }
    }

    return array_values(array_unique(array_filter($ids)));
  }

  /**
   * Load a page by slug or path.
   */
  private function loadPage(?NodeInterface $site, string $slug): ?NodeInterface {
    $storage = $this->entityTypeManager()->getStorage('node');
    $query = $storage->getQuery()->accessCheck(FALSE)->condition('type', 'site_page');
    if ($site && $this->fieldExists('node', 'site_page', 'field_site_profile')) {
      $query->condition('field_site_profile.target_id', $site->id());
    }
    $or = $query->orConditionGroup();
    if ($this->fieldExists('node', 'site_page', 'field_page_slug')) {
      $or->condition('field_page_slug', $slug);
    }
    if ($this->fieldExists('node', 'site_page', 'field_page_path')) {
      $or->condition('field_page_path', '/' . ltrim($slug, '/'));
      if ($slug === 'home') {
        $or->condition('field_page_path', '/');
      }
    }
    $or->condition('title', $slug, 'CONTAINS');
    $query->condition($or)->range(0, 1);
    $ids = $query->execute();
    $nodes = $ids ? $storage->loadMultiple($ids) : [];
    $page = reset($nodes);
    return $page instanceof NodeInterface ? $page : NULL;
  }

  /**
   * Load all public pages for a site.
   *
   * @return array<int, \Drupal\node\NodeInterface>
   */
  private function loadPages(?NodeInterface $site): array {
    $storage = $this->entityTypeManager()->getStorage('node');
    $query = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'site_page')
      ->condition('status', 1)
      ->sort('title');
    if ($site && $this->fieldExists('node', 'site_page', 'field_site_profile')) {
      $query->condition('field_site_profile.target_id', $site->id());
    }
    $ids = $query->execute();
    return $ids ? $storage->loadMultiple($ids) : [];
  }

  /**
   * Load a flexible dataset by key.
   */
  private function loadDataset(?NodeInterface $site, string $key): ?NodeInterface {
    if (!$this->entityTypeManager()->getStorage('node')) {
      return NULL;
    }
    $storage = $this->entityTypeManager()->getStorage('node');
    $query = $storage->getQuery()->accessCheck(FALSE)->condition('type', 'site_data_set');
    if ($site && $this->fieldExists('node', 'site_data_set', 'field_site_profile')) {
      $query->condition('field_site_profile.target_id', $site->id());
    }
    if ($this->fieldExists('node', 'site_data_set', 'field_dataset_key')) {
      $query->condition('field_dataset_key', $key);
    }
    else {
      $query->condition('title', $key, 'CONTAINS');
    }
    $ids = $query->range(0, 1)->execute();
    $nodes = $ids ? $storage->loadMultiple($ids) : [];
    $dataset = reset($nodes);
    return $dataset instanceof NodeInterface ? $dataset : NULL;
  }

  /**
   * Load all public datasets for a site.
   *
   * @return array<int, \Drupal\node\NodeInterface>
   */
  private function loadDatasets(?NodeInterface $site): array {
    $storage = $this->entityTypeManager()->getStorage('node');
    $query = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'site_data_set')
      ->condition('status', 1)
      ->sort('title');
    if ($site && $this->fieldExists('node', 'site_data_set', 'field_site_profile')) {
      $query->condition('field_site_profile.target_id', $site->id());
    }
    $ids = $query->execute();
    return $ids ? $storage->loadMultiple($ids) : [];
  }

  /**
   * Normalize a Site Profile.
   */
  private function normalizeSite(?NodeInterface $site): array {
    if (!$site) {
      return [];
    }
    return [
      'id' => (int) $site->id(),
      'key' => $this->siteKey($site),
      'label' => $site->label(),
      'domains' => $this->fieldString($site, 'field_frontend_domains'),
      'brand' => [
        'primary_color' => $this->fieldString($site, 'field_brand_primary_color'),
        'logo' => $this->fieldMedia($site, 'field_site_logo_media'),
        'favicon' => $this->fieldMedia($site, 'field_site_favicon_media'),
      ],
      'contact' => [
        'email' => $this->fieldString($site, 'field_contact_email'),
        'phone' => $this->fieldString($site, 'field_contact_phone'),
        'address' => $this->fieldString($site, 'field_contact_address'),
      ],
      'social' => $this->fieldLinks($site, 'field_social_links_items'),
      'domain' => $this->domainSummary($site),
    ];
  }

  /**
   * Normalize page metadata.
   */
  private function normalizePage(NodeInterface $page): array {
    return [
      'id' => (int) $page->id(),
      'title' => $page->label(),
      'key' => $this->fieldString($page, 'field_page_key'),
      'slug' => $this->fieldString($page, 'field_page_slug'),
      'path' => $this->fieldString($page, 'field_page_path'),
      'changed' => date(DATE_ATOM, (int) $page->getChangedTime()),
    ];
  }

  /**
   * Normalize one page for the page index endpoint.
   */
  private function normalizePageIndexItem(NodeInterface $page, bool $include_sections = FALSE): array {
    $slug = $this->fieldString($page, 'field_page_slug') ?: (string) $page->id();
    $item = [
      'id' => (int) $page->id(),
      'title' => $page->label(),
      'key' => $this->fieldString($page, 'field_page_key'),
      'slug' => $slug,
      'path' => $this->fieldString($page, 'field_page_path'),
      'seo' => $this->normalizeSeo($page),
      'sections_count' => $page->hasField('field_page_components') && !$page->get('field_page_components')->isEmpty() ? $page->get('field_page_components')->count() : 0,
      'changed' => date(DATE_ATOM, (int) $page->getChangedTime()),
      'api' => '/api/v2/pages/' . rawurlencode($slug),
    ];
    if ($include_sections) {
      $item['sections'] = $this->normalizeParagraphField($page, 'field_page_components');
    }
    return $item;
  }

  /**
   * Detail links for a page payload.
   */
  private function normalizePageLinks(NodeInterface $page, ?NodeInterface $site): array {
    $slug = $this->fieldString($page, 'field_page_slug') ?: (string) $page->id();
    $site_key = $this->siteKey($site);
    return [
      'self' => '/api/v2/pages/' . rawurlencode($slug) . '?site=' . rawurlencode($site_key),
      'index' => '/api/v2/pages?site=' . rawurlencode($site_key),
      'navigation' => '/api/v2/navigation?site=' . rawurlencode($site_key) . '&menu=main',
    ];
  }

  /**
   * Normalize SEO fields.
   */
  private function normalizeSeo(NodeInterface $page): array {
    return [
      'title' => $this->fieldString($page, 'field_seo_title') ?: $page->label(),
      'description' => $this->fieldString($page, 'field_seo_description'),
      'canonical' => $this->fieldString($page, 'field_page_path'),
    ];
  }

  /**
   * Normalize dataset metadata.
   */
  private function normalizeDataset(NodeInterface $dataset): array {
    return [
      'id' => (int) $dataset->id(),
      'key' => $this->fieldString($dataset, 'field_dataset_key'),
      'label' => $this->fieldString($dataset, 'field_dataset_label') ?: $dataset->label(),
      'type' => $this->fieldString($dataset, 'field_dataset_type'),
    ];
  }

  /**
   * Normalize paragraph references.
   */
  private function normalizeParagraphField($entity, string $field_name): array {
    if (!method_exists($entity, 'hasField') || !$entity->hasField($field_name) || $entity->get($field_name)->isEmpty()) {
      return [];
    }
    $items = [];
    foreach ($entity->get($field_name)->referencedEntities() as $paragraph) {
      if ($paragraph instanceof ParagraphInterface) {
        $items[] = $this->normalizeParagraph($paragraph);
      }
    }
    return $items;
  }

  /**
   * Normalize one paragraph generically.
   */
  private function normalizeParagraph(ParagraphInterface $paragraph): array {
    $out = [
      'type' => $paragraph->bundle(),
      'id' => (int) $paragraph->id(),
      'key' => $this->fieldString($paragraph, 'field_component_key'),
      'admin_label' => $this->fieldString($paragraph, 'field_component_admin_label'),
    ];
    foreach ($paragraph->getFields() as $name => $field) {
      if (!str_starts_with($name, 'field_') || $field->isEmpty()) {
        continue;
      }
      if ($name === 'field_component_items') {
        $out['items'] = $this->normalizeParagraphField($paragraph, $name);
      }
      elseif (str_contains($name, 'media')) {
        $out[$name] = $this->fieldMedia($paragraph, $name);
      }
      elseif ($field->getFieldDefinition()->getType() === 'entity_reference') {
        $entities = $field->referencedEntities();
        $ref = reset($entities);
        $out[$name] = $ref ? ['id' => $ref->id(), 'label' => $ref->label(), 'type' => $ref->getEntityTypeId()] : NULL;
      }
      else {
        $out[$name] = $field->getString();
      }
    }
    return $out;
  }

  /**
   * Normalize menu tree.
   */
  private function menuTree(string $menu_name): array {
    try {
      $tree = \Drupal::menuTree();
      $parameters = $tree->getCurrentRouteMenuTreeParameters($menu_name);
      $parameters->onlyEnabledLinks();
      $items = $tree->load($menu_name, $parameters);
      return $this->normalizeMenuItems($items);
    }
    catch (\Throwable) {
      return [];
    }
  }

  /**
   * Normalize menu items recursively.
   */
  private function normalizeMenuItems(array $items): array {
    $out = [];
    foreach ($items as $item) {
      $link = $item->link;
      try {
        $url = $link->getUrlObject()->toString();
      }
      catch (\Throwable) {
        $url = '';
      }
      $out[] = [
        'title' => $link->getTitle(),
        'url' => $url,
        'children' => $this->normalizeMenuItems($item->subtree ?? []),
      ];
    }
    return $out;
  }

  /**
   * Normalize Webform elements.
   */
  private function normalizeWebformElements(array $elements): array {
    $out = [];
    foreach ($elements as $key => $element) {
      if (!is_array($element) || str_starts_with((string) $key, '#')) {
        continue;
      }
      $out[] = [
        'key' => (string) $key,
        'type' => (string) ($element['#type'] ?? 'textfield'),
        'title' => (string) ($element['#title'] ?? $key),
        'required' => !empty($element['#required']),
        'options' => $element['#options'] ?? [],
      ];
    }
    return $out;
  }

  /**
   * Get site key.
   */
  private function siteKey(?NodeInterface $site): string {
    return $site ? ($this->fieldString($site, 'field_site_key') ?: strtolower(preg_replace('/[^a-z0-9]+/', '-', $site->label()))) : '';
  }

  /**
   * Site analytics values.
   */
  private function siteAnalytics(?NodeInterface $site): array {
    return [
      'ga_measurement_id' => $site ? $this->fieldString($site, 'field_ga_measurement_id') : '',
      'gtm_container_id' => $site ? $this->fieldString($site, 'field_gtm_container_id') : '',
    ];
  }

  /**
   * Simple string field.
   */
  private function fieldString($entity, string $field_name): string {
    if (!method_exists($entity, 'hasField') || !$entity->hasField($field_name) || $entity->get($field_name)->isEmpty()) {
      return '';
    }
    return trim((string) $entity->get($field_name)->getString());
  }

  /**
   * Link field values.
   */
  private function fieldLinks($entity, string $field_name): array {
    if (!method_exists($entity, 'hasField') || !$entity->hasField($field_name) || $entity->get($field_name)->isEmpty()) {
      return [];
    }
    $links = [];
    foreach ($entity->get($field_name)->getValue() as $value) {
      $links[] = [
        'title' => (string) ($value['title'] ?? ''),
        'url' => (string) ($value['uri'] ?? ''),
      ];
    }
    return $links;
  }

  /**
   * Media field value.
   */
  private function fieldMedia($entity, string $field_name): ?array {
    if (!method_exists($entity, 'hasField') || !$entity->hasField($field_name) || $entity->get($field_name)->isEmpty()) {
      return NULL;
    }
    $media = $entity->get($field_name)->entity;
    if (!$media instanceof MediaInterface) {
      return NULL;
    }
    $url = '';
    foreach ($media->getFields() as $field) {
      if ($field->getFieldDefinition()->getType() === 'image' && !$field->isEmpty()) {
        $file = $field->entity;
        if ($file) {
          $url = \Drupal::service('file_url_generator')->generateAbsoluteString($file->getFileUri());
        }
        break;
      }
    }
    return [
      'id' => (int) $media->id(),
      'label' => $media->label(),
      'bundle' => $media->bundle(),
      'url' => $url,
    ];
  }

  /**
   * Enabled native/contrib feature summary for API consumers.
   */
  private function enabledFeatureSummary(): array {
    $modules = ['domain', 'domain_access', 'domain_alias', 'domain_config', 'domain_content', 'domain_source', 'webform', 'webform_node', 'webform_schema', 'metatag', 'pathauto', 'redirect', 'simple_sitemap', 'jsonapi', 'rest', 'basic_auth'];
    $out = [];
    foreach ($modules as $module) {
      $out[$module] = \Drupal::moduleHandler()->moduleExists($module);
    }
    return $out;
  }

  /**
   * Domain mapping summary for a Site Profile.
   */
  private function domainSummary(?NodeInterface $site): array {
    if (!$site || !$site->hasField('field_site_domain') || $site->get('field_site_domain')->isEmpty()) {
      return [];
    }
    $domain = $site->get('field_site_domain')->entity;
    if (!$domain) {
      return [];
    }
    $hostname = method_exists($domain, 'getHostname') ? $domain->getHostname() : '';
    return [
      'id' => (string) $domain->id(),
      'label' => method_exists($domain, 'label') ? (string) $domain->label() : (string) $domain->id(),
      'hostname' => (string) $hostname,
    ];
  }

  /**
   * Field exists check.
   */
  private function fieldExists(string $entity_type, string $bundle, string $field_name): bool {
    return (bool) \Drupal\field\Entity\FieldConfig::loadByName($entity_type, $bundle, $field_name);
  }

}
