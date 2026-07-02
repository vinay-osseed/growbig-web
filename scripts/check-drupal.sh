#!/usr/bin/env bash
set -euo pipefail

echo "Checking Drupal platform..."

ddev composer validate --working-dir=site-platform --strict

ddev composer install --working-dir=site-platform

ddev drush status

ddev drush updatedb:status

ddev drush config:status

ddev drush cr

echo "Drupal platform check complete."
