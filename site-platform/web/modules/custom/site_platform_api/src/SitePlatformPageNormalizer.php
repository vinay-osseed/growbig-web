<?php

declare(strict_types=1);

namespace Drupal\site_platform_api;

use Drupal\node\NodeInterface;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Normalizes Site Page content into frontend-ready arrays.
 */
final class SitePlatformPageNormalizer {

  /**
   * Constructs a SitePlatformPageNormalizer object.
   */
  public function __construct(
    private readonly SitePlatformMediaNormalizer $mediaNormalizer,
    private readonly SitePlatformContentListNormalizer $contentListNormalizer,
  ) {}

  /**
   * Normalizes a Site Page node.
   */
  public function normalize(NodeInterface $page): array {
    return [
      'id' => (int) $page->id(),
      'uuid' => $page->uuid(),
      'type' => 'page',
      'title' => $page->label(),
      'slug' => $this->getFieldValue($page, 'field_page_key'),
      'pageType' => $this->getFieldValue($page, 'field_page_type'),
      'summary' => $this->getFieldValue($page, 'field_summary'),
      'sites' => $this->normalizeSites($page),
      'seo' => [
        'title' => $this->getFieldValue($page, 'field_meta_title') ?: $page->label(),
        'description' => $this->getFieldValue($page, 'field_meta_description'),
        'keywords' => $this->getFieldValue($page, 'field_meta_keywords'),
        'canonicalUrl' => $this->getLinkUri($page, 'field_canonical_url'),
        'openGraph' => [
          'title' => $this->getFieldValue($page, 'field_og_title') ?: $this->getFieldValue($page, 'field_meta_title'),
          'description' => $this->getFieldValue($page, 'field_og_description') ?: $this->getFieldValue($page, 'field_meta_description'),
          'image' => $this->normalizeMediaField($page, 'field_og_image'),
        ],
        'robots' => $this->getFieldValue($page, 'field_robots'),
      ],
      'sections' => $this->normalizeSections($page),
      'meta' => [
        'status' => (bool) $page->isPublished(),
        'created' => (int) $page->getCreatedTime(),
        'changed' => (int) $page->getChangedTime(),
      ],
    ];
  }

  /**
   * Normalizes Site Profile references.
   */
  private function normalizeSites(NodeInterface $page): array {
    if (!$page->hasField('field_sites') || $page->get('field_sites')->isEmpty()) {
      return [];
    }

    $sites = [];

    foreach ($page->get('field_sites')->referencedEntities() as $site) {
      if (!$site instanceof NodeInterface) {
        continue;
      }

      $sites[] = [
        'id' => (int) $site->id(),
        'key' => $this->getFieldValue($site, 'field_site_key'),
        'name' => $site->label(),
      ];
    }

    return $sites;
  }

  /**
   * Normalizes page sections.
   */
  private function normalizeSections(NodeInterface $page): array {
    if (!$page->hasField('field_sections') || $page->get('field_sections')->isEmpty()) {
      return [];
    }

    $sections = [];

    foreach ($page->get('field_sections')->referencedEntities() as $paragraph) {
      if (!$paragraph instanceof ParagraphInterface) {
        continue;
      }

      $sections[] = $this->normalizeParagraph($paragraph);
    }

    return $sections;
  }

  /**
   * Normalizes a paragraph by bundle.
   */
  private function normalizeParagraph(ParagraphInterface $paragraph): array {
    return match ($paragraph->bundle()) {
      'hero_section' => $this->normalizeHeroSection($paragraph),
      'stats_section' => $this->normalizeStatsSection($paragraph),
      'stats_item' => $this->normalizeStatsItem($paragraph),
      'card_grid_section' => $this->normalizeCardGridSection($paragraph),
      'card_item' => $this->normalizeCardItem($paragraph),
      'cta_section' => $this->normalizeCtaSection($paragraph),
      'image_text_section' => $this->normalizeImageTextSection($paragraph),
      'content_list_section' => $this->normalizeContentListSection($paragraph),
      default => [
        'id' => (int) $paragraph->id(),
        'type' => $paragraph->bundle(),
      ],
    };
  }

  /**
   * Normalizes Hero Section.
   */
  private function normalizeHeroSection(ParagraphInterface $paragraph): array {
    return [
      'id' => (int) $paragraph->id(),
      'type' => 'hero',
      'badge' => $this->getParagraphFieldValue($paragraph, 'field_badge'),
      'heading' => $this->getParagraphFieldValue($paragraph, 'field_heading'),
      'highlightText' => $this->getParagraphFieldValue($paragraph, 'field_highlight_text'),
      'description' => $this->getParagraphFieldValue($paragraph, 'field_description'),
      'primaryButton' => $this->normalizeButton($paragraph, 'field_primary_button_text', 'field_primary_button_link'),
      'secondaryButton' => $this->normalizeButton($paragraph, 'field_secondary_button_text', 'field_secondary_button_link'),
      'image' => $this->normalizeParagraphMediaField($paragraph, 'field_image'),
      'imageAltText' => $this->getParagraphFieldValue($paragraph, 'field_image_alt_text'),
      'layoutVariant' => $this->getParagraphFieldValue($paragraph, 'field_layout_variant'),
      'backgroundStyle' => $this->getParagraphFieldValue($paragraph, 'field_background_style'),
    ];
  }

  /**
   * Normalizes Stats Section.
   */
  private function normalizeStatsSection(ParagraphInterface $paragraph): array {
    return [
      'id' => (int) $paragraph->id(),
      'type' => 'stats',
      'items' => $this->normalizeReferencedParagraphs($paragraph, 'field_stats_items'),
    ];
  }

  /**
   * Normalizes Stats Item.
   */
  private function normalizeStatsItem(ParagraphInterface $paragraph): array {
    return [
      'id' => (int) $paragraph->id(),
      'type' => 'stat',
      'title' => $this->getParagraphFieldValue($paragraph, 'field_title'),
      'count' => $this->getParagraphIntValue($paragraph, 'field_count'),
      'prefix' => $this->getParagraphFieldValue($paragraph, 'field_prefix'),
      'suffix' => $this->getParagraphFieldValue($paragraph, 'field_suffix'),
      'icon' => $this->getParagraphFieldValue($paragraph, 'field_icon'),
      'order' => $this->getParagraphIntValue($paragraph, 'field_display_order'),
    ];
  }

  /**
   * Normalizes Card Grid Section.
   */
  private function normalizeCardGridSection(ParagraphInterface $paragraph): array {
    return [
      'id' => (int) $paragraph->id(),
      'type' => 'cardGrid',
      'badge' => $this->getParagraphFieldValue($paragraph, 'field_badge'),
      'heading' => $this->getParagraphFieldValue($paragraph, 'field_heading'),
      'description' => $this->getParagraphFieldValue($paragraph, 'field_description'),
      'cards' => $this->normalizeReferencedParagraphs($paragraph, 'field_cards'),
      'primaryButton' => $this->normalizeButton($paragraph, 'field_primary_button_text', 'field_primary_button_link'),
      'secondaryButton' => $this->normalizeButton($paragraph, 'field_secondary_button_text', 'field_secondary_button_link'),
      'layoutVariant' => $this->getParagraphFieldValue($paragraph, 'field_layout_variant'),
    ];
  }

  /**
   * Normalizes Card Item.
   */
  private function normalizeCardItem(ParagraphInterface $paragraph): array {
    return [
      'id' => (int) $paragraph->id(),
      'type' => 'card',
      'title' => $this->getParagraphFieldValue($paragraph, 'field_title'),
      'description' => $this->getParagraphFieldValue($paragraph, 'field_description'),
      'icon' => $this->getParagraphFieldValue($paragraph, 'field_icon'),
      'image' => $this->normalizeParagraphMediaField($paragraph, 'field_image'),
      'accentColor' => $this->getParagraphFieldValue($paragraph, 'field_accent_color'),
      'link' => $this->normalizeButton($paragraph, 'field_link_text', 'field_link_url'),
      'order' => $this->getParagraphIntValue($paragraph, 'field_display_order'),
      'isActive' => $this->getParagraphBoolValue($paragraph, 'field_is_active'),
    ];
  }

  /**
   * Normalizes CTA Section.
   */
  private function normalizeCtaSection(ParagraphInterface $paragraph): array {
    return [
      'id' => (int) $paragraph->id(),
      'type' => 'cta',
      'badge' => $this->getParagraphFieldValue($paragraph, 'field_badge'),
      'heading' => $this->getParagraphFieldValue($paragraph, 'field_heading'),
      'description' => $this->getParagraphFieldValue($paragraph, 'field_description'),
      'primaryButton' => $this->normalizeButton($paragraph, 'field_primary_button_text', 'field_primary_button_link'),
      'secondaryButton' => $this->normalizeButton($paragraph, 'field_secondary_button_text', 'field_secondary_button_link'),
      'layoutVariant' => $this->getParagraphFieldValue($paragraph, 'field_layout_variant'),
      'backgroundStyle' => $this->getParagraphFieldValue($paragraph, 'field_background_style'),
    ];
  }

  /**
   * Normalizes Image Text Section.
   */
  private function normalizeImageTextSection(ParagraphInterface $paragraph): array {
    return [
      'id' => (int) $paragraph->id(),
      'type' => 'imageText',
      'badge' => $this->getParagraphFieldValue($paragraph, 'field_badge'),
      'heading' => $this->getParagraphFieldValue($paragraph, 'field_heading'),
      'description' => $this->getParagraphFieldValue($paragraph, 'field_description'),
      'image' => $this->normalizeParagraphMediaField($paragraph, 'field_image'),
      'imagePosition' => $this->getParagraphFieldValue($paragraph, 'field_image_position'),
      'primaryButton' => $this->normalizeButton($paragraph, 'field_primary_button_text', 'field_primary_button_link'),
      'secondaryButton' => $this->normalizeButton($paragraph, 'field_secondary_button_text', 'field_secondary_button_link'),
      'layoutVariant' => $this->getParagraphFieldValue($paragraph, 'field_layout_variant'),
    ];
  }

  /**
   * Normalizes Content List Section.
   */
  private function normalizeContentListSection(ParagraphInterface $paragraph): array {
    $source = $this->getParagraphFieldValue($paragraph, 'field_content_source');
    $limit = $this->getParagraphIntValue($paragraph, 'field_limit');
    $featured_only = $this->getParagraphBoolValue($paragraph, 'field_featured_only');

    return [
      'id' => (int) $paragraph->id(),
      'type' => 'contentList',
      'badge' => $this->getParagraphFieldValue($paragraph, 'field_badge'),
      'heading' => $this->getParagraphFieldValue($paragraph, 'field_heading'),
      'description' => $this->getParagraphFieldValue($paragraph, 'field_description'),
      'source' => $source,
      'limit' => $limit,
      'featuredOnly' => $featured_only,
      'items' => $this->contentListNormalizer->loadItems($source, $limit, $featured_only),
      'primaryButton' => $this->normalizeButton($paragraph, 'field_primary_button_text', 'field_primary_button_link'),
      'secondaryButton' => $this->normalizeButton($paragraph, 'field_secondary_button_text', 'field_secondary_button_link'),
      'layoutVariant' => $this->getParagraphFieldValue($paragraph, 'field_layout_variant'),
    ];
  }

  /**
   * Normalizes referenced paragraphs.
   */
  private function normalizeReferencedParagraphs(ParagraphInterface $paragraph, string $field_name): array {
    if (!$paragraph->hasField($field_name) || $paragraph->get($field_name)->isEmpty()) {
      return [];
    }

    $items = [];

    foreach ($paragraph->get($field_name)->referencedEntities() as $referenced) {
      if ($referenced instanceof ParagraphInterface) {
        $items[] = $this->normalizeParagraph($referenced);
      }
    }

    usort($items, static fn(array $a, array $b): int => ($a['order'] ?? 0) <=> ($b['order'] ?? 0));

    return $items;
  }

  /**
   * Normalizes a button/link pair.
   */
  private function normalizeButton(ParagraphInterface $paragraph, string $text_field, string $link_field): array {
    return [
      'text' => $this->getParagraphFieldValue($paragraph, $text_field),
      'url' => $this->getParagraphLinkUri($paragraph, $link_field),
    ];
  }

  /**
   * Gets a node field value.
   */
  private function getFieldValue(NodeInterface $node, string $field_name): string {
    if (!$node->hasField($field_name) || $node->get($field_name)->isEmpty()) {
      return '';
    }

    return (string) $node->get($field_name)->value;
  }

  /**
   * Gets link URI from node field.
   */
  private function getLinkUri(NodeInterface $node, string $field_name): string {
    if (!$node->hasField($field_name) || $node->get($field_name)->isEmpty()) {
      return '';
    }

    return (string) $node->get($field_name)->uri;
  }

  /**
   * Gets paragraph field value.
   */
  private function getParagraphFieldValue(ParagraphInterface $paragraph, string $field_name): string {
    if (!$paragraph->hasField($field_name) || $paragraph->get($field_name)->isEmpty()) {
      return '';
    }

    return (string) $paragraph->get($field_name)->value;
  }

  /**
   * Gets paragraph integer value.
   */
  private function getParagraphIntValue(ParagraphInterface $paragraph, string $field_name): int {
    if (!$paragraph->hasField($field_name) || $paragraph->get($field_name)->isEmpty()) {
      return 0;
    }

    return (int) $paragraph->get($field_name)->value;
  }

  /**
   * Gets paragraph boolean value.
   */
  private function getParagraphBoolValue(ParagraphInterface $paragraph, string $field_name): bool {
    if (!$paragraph->hasField($field_name) || $paragraph->get($field_name)->isEmpty()) {
      return FALSE;
    }

    return (bool) $paragraph->get($field_name)->value;
  }

  /**
   * Gets paragraph link URI.
   */
  private function getParagraphLinkUri(ParagraphInterface $paragraph, string $field_name): string {
    if (!$paragraph->hasField($field_name) || $paragraph->get($field_name)->isEmpty()) {
      return '';
    }

    return (string) $paragraph->get($field_name)->uri;
  }

  /**
   * Normalizes media from a node field.
   */
  private function normalizeMediaField(NodeInterface $node, string $field_name): ?array {
    if (!$node->hasField($field_name) || $node->get($field_name)->isEmpty()) {
      return NULL;
    }

    $media = $node->get($field_name)->entity;

    if (!$media) {
      return NULL;
    }

    return $this->mediaNormalizer->normalize($media);
  }

  /**
   * Normalizes media from a paragraph field.
   */
  private function normalizeParagraphMediaField(ParagraphInterface $paragraph, string $field_name): ?array {
    if (!$paragraph->hasField($field_name) || $paragraph->get($field_name)->isEmpty()) {
      return NULL;
    }

    $media = $paragraph->get($field_name)->entity;

    if (!$media) {
      return NULL;
    }

    return $this->mediaNormalizer->normalize($media);
  }

}
