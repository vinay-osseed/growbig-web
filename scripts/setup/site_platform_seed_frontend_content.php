<?php

declare(strict_types=1);

use Drupal\Component\Serialization\Yaml;
use Drupal\webform\Entity\Webform;

$path = $extra[0] ?? (__DIR__ . '/data/frontend-content.growbig.json');

if (!is_file($path)) {
  throw new RuntimeException("Frontend content data file not found: {$path}");
}

$payload = json_decode((string) file_get_contents($path), TRUE);

if (!is_array($payload)) {
  throw new RuntimeException("Invalid frontend content JSON: {$path}");
}

foreach (($payload['forms'] ?? []) as $form) {
  $webform_id = $form['webformId'] ?? '';
  $title = $form['title'] ?? $webform_id;

  if (!$webform_id || empty($form['webformElements']) || !is_array($form['webformElements'])) {
    continue;
  }

  $webform = Webform::load($webform_id);

  if (!$webform) {
    $webform = Webform::create([
      'id' => $webform_id,
      'title' => $title,
      'status' => 'open',
    ]);
  }

  $webform->set('title', $title);
  $webform->set('description', $form['description'] ?? '');
  $webform->set('status', 'open');
  $webform->set('elements', Yaml::encode($form['webformElements']));
  $webform->set('settings', [
    'confirmation_type' => 'message',
    'confirmation_message' => $form['successMessage'] ?? 'Submission received.',
  ]);
  $webform->save();

  print "Saved webform: {$webform_id}\n";
}

\Drupal::state()->set('site_platform.frontend_payload', $payload);

\Drupal::configFactory()->getEditable('system.site')
  ->set('name', $payload['site']['name'] ?? 'Drupal Decoupled Backend')
  ->set('mail', $payload['site']['email'] ?? '')
  ->save(TRUE);

\Drupal::configFactory()->getEditable('site_platform.runtime')
  ->set('mode', 'single_backend')
  ->set('frontend_count', 1)
  ->set('multisite_supported', FALSE)
  ->set('domain_required', FALSE)
  ->save(TRUE);

drupal_flush_all_caches();

print "Seeded generic frontend payload from: {$path}\n";
print "Project key: " . ($payload['template']['projectKey'] ?? 'unknown') . "\n";
print "Images are placeholder/remote references until Drupal Media is updated.\n";
