#!/usr/bin/env bash

set -euo pipefail

echo "Checking dashboard UI render array..."

ddev drush php:eval '
$controller = \Drupal::classResolver()
  ->getInstanceFromDefinition(\Drupal\site_platform_admin\Controller\SiteDashboardController::class);

$build = $controller->dashboard();

foreach (["intro", "counts", "cards", "recent"] as $key) {
  if (!isset($build[$key])) {
    throw new \RuntimeException("Missing dashboard render key: {$key}");
  }
}

echo "Dashboard UI render array verified." . PHP_EOL;
'
