<?php

declare(strict_types=1);

namespace Drupal\site_platform_api;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\file\FileInterface;
use Drupal\image\ImageStyleInterface;
use Drupal\media\MediaInterface;

/**
 * Normalizes Drupal media entities for frontend API responses.
 */
final class SitePlatformMediaNormalizer {

  /**
   * Frontend image style map.
   */
  private const IMAGE_STYLES = [
    'thumbnail' => 'api_thumbnail',
    'card' => 'api_card',
    'heroMobile' => 'api_hero_mobile',
    'heroDesktop' => 'api_hero_desktop',
    'social' => 'api_social',
    'logo' => 'api_logo',
    'wide' => 'wide',
    'large' => 'large',
    'medium' => 'medium',
  ];

  /**
   * Image style storage.
   */
  private readonly EntityStorageInterface $imageStyleStorage;

  /**
   * Constructs a SitePlatformMediaNormalizer object.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    private readonly FileUrlGeneratorInterface $fileUrlGenerator,
  ) {
    $this->imageStyleStorage = $entityTypeManager->getStorage('image_style');
  }

  /**
   * Normalizes a media entity.
   */
  public function normalize(MediaInterface $media): array {
    if ($media->bundle() === 'image') {
      return $this->normalizeImage($media);
    }

    return [
      'id' => $media->uuid(),
      'type' => $media->bundle(),
      'title' => $media->label(),
    ];
  }

  /**
   * Normalizes an image media entity.
   */
  private function normalizeImage(MediaInterface $media): array {
    if (!$media->hasField('field_media_image') || $media->get('field_media_image')->isEmpty()) {
      return [
        'id' => $media->uuid(),
        'type' => 'image',
        'title' => $media->label(),
        'alt' => '',
        'url' => '',
        'mime' => '',
        'width' => NULL,
        'height' => NULL,
        'styles' => [],
      ];
    }

    $item = $media->get('field_media_image')->first();
    $values = $item->getValue();
    $file = $item->entity;

    if (!$file instanceof FileInterface) {
      return [
        'id' => $media->uuid(),
        'type' => 'image',
        'title' => $media->label(),
        'alt' => $values['alt'] ?? '',
        'url' => '',
        'mime' => '',
        'width' => $values['width'] ?? NULL,
        'height' => $values['height'] ?? NULL,
        'styles' => [],
      ];
    }

    $uri = $file->getFileUri();

    return [
      'id' => $media->uuid(),
      'type' => 'image',
      'title' => $media->label(),
      'alt' => $values['alt'] ?? '',
      'url' => $this->fileUrlGenerator->generateAbsoluteString($uri),
      'mime' => $file->getMimeType(),
      'width' => $values['width'] ?? NULL,
      'height' => $values['height'] ?? NULL,
      'styles' => $this->buildImageStyleUrls($uri),
    ];
  }

  /**
   * Builds image style URLs for a file URI.
   */
  private function buildImageStyleUrls(string $uri): array {
    $urls = [];

    foreach (self::IMAGE_STYLES as $key => $style_name) {
      $style = $this->imageStyleStorage->load($style_name);

      if ($style instanceof ImageStyleInterface) {
        $urls[$key] = $style->buildUrl($uri);
      }
    }

    return $urls;
  }

}
