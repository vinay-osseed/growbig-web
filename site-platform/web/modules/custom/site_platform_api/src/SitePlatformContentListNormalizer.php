<?php

declare(strict_types=1);

namespace Drupal\site_platform_api;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

/**
 * Loads and normalizes reusable content list items.
 */
final class SitePlatformContentListNormalizer {

  /**
   * Constructs a SitePlatformContentListNormalizer object.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly SitePlatformMediaNormalizer $mediaNormalizer,
  ) {}

  /**
   * Loads content list items by source.
   */
  public function loadItems(string $source, int $limit = 0, bool $featured_only = FALSE): array {
    return match ($source) {
      'services' => $this->loadNodes('service', 'field_service_key', $limit, $featured_only),
      'partners' => $this->loadNodes('partner', 'field_partner_key', $limit, $featured_only),
      'team' => $this->loadNodes('team_member', 'field_member_key', $limit, $featured_only),
      default => [],
    };
  }

  /**
   * Loads active nodes by type.
   */
  private function loadNodes(string $type, string $key_field, int $limit, bool $featured_only): array {
    $storage = $this->entityTypeManager->getStorage('node');

    $query = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', $type)
      ->condition('status', 1)
      ->condition('field_is_active', 1)
      ->sort('field_display_order', 'ASC')
      ->sort('title', 'ASC');

    if ($featured_only) {
      $query->condition('field_is_featured', 1);
    }

    if ($limit > 0) {
      $query->range(0, $limit);
    }

    $ids = $query->execute();

    if (!$ids) {
      return [];
    }

    $items = [];

    foreach ($storage->loadMultiple($ids) as $node) {
      if (!$node instanceof NodeInterface) {
        continue;
      }

      $items[] = match ($type) {
        'service' => $this->normalizeService($node, $key_field),
        'partner' => $this->normalizePartner($node, $key_field),
        'team_member' => $this->normalizeTeamMember($node, $key_field),
        default => [],
      };
    }

    return array_values(array_filter($items));
  }

  /**
   * Normalizes a Service node.
   */
  private function normalizeService(NodeInterface $node, string $key_field): array {
    return [
      'id' => (int) $node->id(),
      'type' => 'service',
      'key' => $this->getFieldValue($node, $key_field),
      'title' => $node->label(),
      'summary' => $this->getFieldValue($node, 'field_summary'),
      'icon' => $this->getFieldValue($node, 'field_icon'),
      'accentColor' => $this->getFieldValue($node, 'field_accent_color'),
      'image' => $this->normalizeMediaField($node, 'field_image'),
      'url' => $this->getLinkUri($node, 'field_link_url'),
      'order' => $this->getIntValue($node, 'field_display_order'),
      'isFeatured' => $this->getBoolValue($node, 'field_is_featured'),
    ];
  }

  /**
   * Normalizes a Partner node.
   */
  private function normalizePartner(NodeInterface $node, string $key_field): array {
    return [
      'id' => (int) $node->id(),
      'type' => 'partner',
      'key' => $this->getFieldValue($node, $key_field),
      'title' => $node->label(),
      'summary' => $this->getFieldValue($node, 'field_summary'),
      'logo' => $this->normalizeMediaField($node, 'field_logo'),
      'website' => $this->getLinkUri($node, 'field_website'),
      'order' => $this->getIntValue($node, 'field_display_order'),
      'isFeatured' => $this->getBoolValue($node, 'field_is_featured'),
    ];
  }

  /**
   * Normalizes a Team Member node.
   */
  private function normalizeTeamMember(NodeInterface $node, string $key_field): array {
    return [
      'id' => (int) $node->id(),
      'type' => 'teamMember',
      'key' => $this->getFieldValue($node, $key_field),
      'name' => $node->label(),
      'role' => $this->getFieldValue($node, 'field_role'),
      'summary' => $this->getFieldValue($node, 'field_summary'),
      'bio' => $this->getFieldValue($node, 'field_bio'),
      'photo' => $this->normalizeMediaField($node, 'field_photo'),
      'email' => $this->getFieldValue($node, 'field_email'),
      'linkedinUrl' => $this->getLinkUri($node, 'field_linkedin_url'),
      'order' => $this->getIntValue($node, 'field_display_order'),
      'isFeatured' => $this->getBoolValue($node, 'field_is_featured'),
    ];
  }

  /**
   * Gets a string field value.
   */
  private function getFieldValue(NodeInterface $node, string $field_name): string {
    if (!$node->hasField($field_name) || $node->get($field_name)->isEmpty()) {
      return '';
    }

    return (string) $node->get($field_name)->value;
  }

  /**
   * Gets an integer field value.
   */
  private function getIntValue(NodeInterface $node, string $field_name): int {
    if (!$node->hasField($field_name) || $node->get($field_name)->isEmpty()) {
      return 0;
    }

    return (int) $node->get($field_name)->value;
  }

  /**
   * Gets a boolean field value.
   */
  private function getBoolValue(NodeInterface $node, string $field_name): bool {
    if (!$node->hasField($field_name) || $node->get($field_name)->isEmpty()) {
      return FALSE;
    }

    return (bool) $node->get($field_name)->value;
  }

  /**
   * Gets link URI.
   */
  private function getLinkUri(NodeInterface $node, string $field_name): string {
    if (!$node->hasField($field_name) || $node->get($field_name)->isEmpty()) {
      return '';
    }

    return (string) $node->get($field_name)->uri;
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

}
