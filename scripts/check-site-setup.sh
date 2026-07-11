#!/usr/bin/env bash

set -euo pipefail

echo "Checking site setup foundation..."

ddev drush route | grep -q "site_platform_admin.site_setup"
ddev drush route | grep -q "site_platform_admin.site_setup_wizard"
ddev drush route | grep -q "site_platform_admin.site_setup_run"
ddev drush route | grep -q "site_platform_admin.site_setup_complete"

ddev drush php:eval '
foreach (["site_platform_admin.setup_storage", "site_platform_admin.setup_runner"] as $service_id) {
  if (!\Drupal::hasService($service_id)) {
    throw new \RuntimeException("Missing service: " . $service_id);
  }
}

$storage = \Drupal::service("site_platform_admin.setup_storage");
$status = $storage->getStatus();
$manifest = $storage->getManifest();
$values = $storage->getValues();

foreach (["installed", "completed", "locked", "current_step", "mode", "steps"] as $key) {
  if (!array_key_exists($key, $status)) {
    throw new \RuntimeException("Missing setup status key: " . $key);
  }
}

foreach (["setup_id", "nodes", "webforms", "roles", "config", "files"] as $key) {
  if (!array_key_exists($key, $manifest)) {
    throw new \RuntimeException("Missing setup manifest key: " . $key);
  }
}

foreach (["mode", "site", "contact", "branding", "setup_options", "analytics", "extra_sites"] as $key) {
  if (!array_key_exists($key, $values)) {
    throw new \RuntimeException("Missing setup values key: " . $key);
  }
}

$runner = \Drupal::service("site_platform_admin.setup_runner");
$preview = $runner->getPreview();
foreach (["mode", "site_name", "site_key", "create_default_roles", "create_default_pages", "create_default_menus", "create_default_forms"] as $key) {
  if (!array_key_exists($key, $preview)) {
    throw new \RuntimeException("Missing setup runner preview key: " . $key);
  }
}

$readiness = $runner->getCompletionReadiness();
if (!is_array($readiness) || !array_key_exists("ready", $readiness)) {
  throw new \RuntimeException("Setup completion readiness failed.");
}

echo "Site setup foundation verified." . PHP_EOL;
'
