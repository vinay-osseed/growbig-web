#!/usr/bin/env bash

set -euo pipefail

echo "Checking site setup foundation..."

ddev drush route | grep -q "site_platform_admin.site_setup"

ddev drush php:eval '
$status = \Drupal::config("site_platform_admin.setup_status");
$manifest = \Drupal::config("site_platform_admin.setup_manifest");

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

echo "Site setup foundation verified." . PHP_EOL;
'
