#!/usr/bin/env bash
set -euo pipefail

echo "Checking custom Drupal code..."

if [ ! -d "site-platform/web/modules/custom" ] && [ ! -d "site-platform/web/themes/custom" ]; then
  echo "No custom Drupal code directories found yet. Skipping PHPCS."
  exit 0
fi

ddev exec -d /var/www/html/site-platform vendor/bin/phpcs --standard=phpcs.xml.dist

echo "Custom Drupal code check complete."
