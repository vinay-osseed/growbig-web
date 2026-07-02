#!/usr/bin/env bash
set -euo pipefail

echo "Starting Drupal deployment..."

composer install --working-dir=site-platform --no-dev --optimize-autoloader

cd site-platform

vendor/bin/drush updb -y
vendor/bin/drush cim -y
vendor/bin/drush cr

vendor/bin/drush status

echo "Drupal deployment complete."
