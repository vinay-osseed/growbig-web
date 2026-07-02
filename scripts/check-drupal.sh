#!/usr/bin/env bash
set -euo pipefail

echo "Checking Drupal platform..."

ddev composer validate --strict

ddev composer install

ddev drush status

ddev drush updatedb:status

ddev drush config:status

ddev drush cr

echo "Drupal platform check complete."
