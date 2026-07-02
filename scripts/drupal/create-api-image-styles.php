<?php

declare(strict_types=1);

use Drupal\image\Entity\ImageStyle;

/**
 * Creates reusable image styles for frontend API responses.
 */

$styles = [
  'api_thumbnail' => [
    'label' => 'API Thumbnail',
    'effect' => 'image_scale',
    'data' => [
      'width' => 320,
      'height' => 320,
      'upscale' => FALSE,
    ],
  ],
  'api_card' => [
    'label' => 'API Card',
    'effect' => 'image_scale',
    'data' => [
      'width' => 640,
      'height' => 640,
      'upscale' => FALSE,
    ],
  ],
  'api_hero_mobile' => [
    'label' => 'API Hero Mobile',
    'effect' => 'image_scale',
    'data' => [
      'width' => 768,
      'height' => 768,
      'upscale' => FALSE,
    ],
  ],
  'api_hero_desktop' => [
    'label' => 'API Hero Desktop',
    'effect' => 'image_scale',
    'data' => [
      'width' => 1440,
      'height' => 1440,
      'upscale' => FALSE,
    ],
  ],
  'api_social' => [
    'label' => 'API Social',
    'effect' => 'image_scale_and_crop',
    'data' => [
      'width' => 1200,
      'height' => 630,
      'anchor' => 'center-center',
    ],
  ],
  'api_logo' => [
    'label' => 'API Logo',
    'effect' => 'image_scale',
    'data' => [
      'width' => 300,
      'height' => 300,
      'upscale' => FALSE,
    ],
  ],
];

foreach ($styles as $name => $definition) {
  if (ImageStyle::load($name)) {
    print "Image style already exists: {$name}\n";
    continue;
  }

  $style = ImageStyle::create([
    'name' => $name,
    'label' => $definition['label'],
  ]);

  $style->addImageEffect([
    'id' => $definition['effect'],
    'data' => $definition['data'],
    'weight' => 1,
  ]);

  $style->save();

  print "Created image style: {$name}\n";
}

print "API image styles setup complete.\n";
