#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

say() { printf '%s\n' "$*"; }

say "Installing Drupal-native contrib stack..."
./scripts/setup/site_platform_install_drupal_native_stack.sh

say "Applying Drupal-native Site Platform workflow..."
ddev drush scr scripts/setup/site_platform_apply_domain_native_workflow.php

say "Rebuilding Drupal cache..."
ddev drush cr

say "Verifying Drupal-native Site Platform workflow..."
./scripts/setup/verify-site-platform-drupal-native.sh

say "Drupal-native Site Platform workflow finished."
