<?php

declare(strict_types=1);

use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\Core\File\FileExists;

$path = $extra[0] ?? (__DIR__ . '/data/frontend-content.growbig.json');

if (!is_file($path)) {
  throw new RuntimeException("Frontend content data file not found: {$path}");
}

$payload = json_decode((string) file_get_contents($path), TRUE);

if (!is_array($payload)) {
  throw new RuntimeException("Invalid frontend content JSON: {$path}");
}

$project_key = preg_replace('/[^a-z0-9_-]+/i', '-', (string) ($payload['template']['projectKey'] ?? 'frontend'));
$project_key = strtolower(trim($project_key, '-')) ?: 'frontend';

$media_storage = \Drupal::entityTypeManager()->getStorage('media');
$file_storage = \Drupal::entityTypeManager()->getStorage('file');
$file_system = \Drupal::service('file_system');
$file_url_generator = \Drupal::service('file_url_generator');
$http_client = \Drupal::httpClient();

$media_type_storage = \Drupal::entityTypeManager()->getStorage('media_type');

if (!$media_type_storage->load('image')) {
  throw new RuntimeException('Missing Drupal Media image bundle. Enable Media/Media Library and create the standard image media type first.');
}

$directory = "public://frontend-assets/{$project_key}";
$file_system->prepareDirectory($directory, \Drupal\Core\File\FileSystemInterface::CREATE_DIRECTORY | \Drupal\Core\File\FileSystemInterface::MODIFY_PERMISSIONS);

function sp_frontend_is_supported_raster_image(string $url): bool {
  $path = parse_url($url, PHP_URL_PATH) ?: '';
  $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

  return in_array($extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'], TRUE);
}

function sp_frontend_guess_extension(string $url): string {
  $path = parse_url($url, PHP_URL_PATH) ?: '';
  $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

  return $extension ?: 'jpg';
}

function sp_frontend_safe_name(string $value): string {
  $value = preg_replace('/[^a-z0-9_-]+/i', '-', $value);
  return strtolower(trim((string) $value, '-')) ?: 'asset';
}

function sp_frontend_import_media_asset(array $asset, string $asset_key, string $directory, string $project_key, $http_client, $file_system, $file_url_generator, $media_storage, $file_storage): array {
  $url = $asset['auditSourceUrl'] ?? $asset['url'] ?? '';

  if (!$url) {
    return $asset;
  }

  if (!sp_frontend_is_supported_raster_image($url)) {
    $asset['status'] = $asset['status'] ?? 'external';
    $asset['url'] = $asset['url'] ?? $url;
    $asset['note'] = $asset['note'] ?? 'Kept as external/final hosted asset because this is not a supported raster image for the default Drupal image media type.';
    return $asset;
  }

  $alt = (string) ($asset['alt'] ?? $asset_key);
  $extension = sp_frontend_guess_extension($url);
  $filename = sp_frontend_safe_name($asset_key) . '.' . $extension;
  $destination = "{$directory}/{$filename}";

  print "Downloading media: {$asset_key} <= {$url}\n";

  $response = $http_client->get($url, [
    'timeout' => 45,
    'headers' => [
      'User-Agent' => 'Drupal Frontend Media Importer',
    ],
  ]);

  $data = (string) $response->getBody();

  if ($data === '') {
    throw new RuntimeException("Downloaded empty file for {$asset_key}: {$url}");
  }

  $uri = $file_system->saveData($data, $destination, FileExists::Replace);

  $existing_files = $file_storage->loadByProperties(['uri' => $uri]);
  $file = $existing_files ? reset($existing_files) : NULL;

  if (!$file) {
    $file = File::create([
      'uri' => $uri,
      'uid' => 1,
      'status' => 1,
    ]);
  }

  $file->setPermanent();
  $file->save();

  $media_name = "{$project_key}: {$asset_key}";
  $existing_media = $media_storage->loadByProperties([
    'bundle' => 'image',
    'name' => $media_name,
  ]);
  $media = $existing_media ? reset($existing_media) : NULL;

  if (!$media) {
    $media = Media::create([
      'bundle' => 'image',
      'name' => $media_name,
      'status' => 1,
    ]);
  }

  $media->set('field_media_image', [
    'target_id' => $file->id(),
    'alt' => $alt,
  ]);
  $media->save();

  $asset['status'] = 'drupal_media';
  $asset['mediaId'] = (int) $media->id();
  $asset['mediaUuid'] = $media->uuid();
  $asset['fileId'] = (int) $file->id();
  $asset['fileUri'] = $uri;
  $asset['url'] = $file_url_generator->generateAbsoluteString($uri);
  $asset['alt'] = $alt;

  return $asset;
}

function sp_frontend_walk_import_media(&$value, array $path_parts, string $directory, string $project_key, $http_client, $file_system, $file_url_generator, $media_storage, $file_storage): void {
  if (!is_array($value)) {
    return;
  }

  if (!empty($value['auditSourceUrl'])) {
    $asset_key = implode('-', array_filter($path_parts));
    $asset_key = sp_frontend_safe_name($asset_key);
    $value = sp_frontend_import_media_asset($value, $asset_key, $directory, $project_key, $http_client, $file_system, $file_url_generator, $media_storage, $file_storage);
    return;
  }

  foreach ($value as $key => &$child) {
    sp_frontend_walk_import_media($child, array_merge($path_parts, [(string) $key]), $directory, $project_key, $http_client, $file_system, $file_url_generator, $media_storage, $file_storage);
  }
}

sp_frontend_walk_import_media($payload, [], $directory, $project_key, $http_client, $file_system, $file_url_generator, $media_storage, $file_storage);

\Drupal::state()->set('site_platform.frontend_payload', $payload);

file_put_contents($path, json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL);

drupal_flush_all_caches();

print "Frontend media import completed.\n";
print "Updated JSON payload: {$path}\n";
print "Drupal public directory: {$directory}\n";
