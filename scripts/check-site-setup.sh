#!/usr/bin/env bash

set -euo pipefail

echo "Checking site setup foundation..."

ddev drush route | grep -q "site_platform_admin.site_setup"
ddev drush route | grep -q "site_platform_admin.site_setup_wizard"
ddev drush route | grep -q "site_platform_admin.site_setup_run"

ddev drush php:eval '
$status = \Drupal::config("site_platform_admin.setup_status");
$manifest = \Drupal::config("site_platform_admin.setup_manifest");
$values = \Drupal::config("site_platform_admin.setup_values");

foreach (["installed", "completed", "locked", "current_step", "mode", "steps"] as $key) {
  if ($status->get($key) === NULL) {
    throw new \RuntimeException("Missing setup status config key: " . $key);
  }
}

foreach (["setup_id", "nodes", "webforms", "roles", "config", "files"] as $key) {
  if ($manifest->get($key) === NULL) {
    throw new \RuntimeException("Missing setup manifest config key: " . $key);
  }
}

foreach (["mode", "site", "contact", "branding", "setup_options", "analytics", "extra_sites"] as $key) {
  if ($values->get($key) === NULL) {
    throw new \RuntimeException("Missing setup values config key: " . $key);
  }
}

if (!\Drupal::hasService("site_platform_admin.setup_runner")) {
  throw new \RuntimeException("Missing setup runner service.");
}

$preview = \Drupal::service("site_platform_admin.setup_runner")->getPreview();
foreach (["mode", "site_name", "site_key", "create_default_roles"] as $key) {
  if (!array_key_exists($key, $preview)) {
    throw new \RuntimeException("Missing setup runner preview key: " . $key);
  }
}

echo "Site setup foundation verified." . PHP_EOL;
'
