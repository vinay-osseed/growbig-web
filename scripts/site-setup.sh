#!/usr/bin/env bash

set -euo pipefail

COMMAND="${1:-help}"

usage() {
  cat <<'USAGE'
Site Setup helper

Usage:
  ./scripts/site-setup.sh status
  ./scripts/site-setup.sh preview
  ./scripts/site-setup.sh run
  ./scripts/site-setup.sh complete
  ./scripts/site-setup.sh unlock
  ./scripts/site-setup.sh reset-status
  ./scripts/site-setup.sh check

Notes:
  - This helper uses Drupal State API through existing setup services.
  - It does not delete pages, forms, roles, content, files, or submissions.
  - Do not run drush cex after entering environment-specific setup values unless intentional.
USAGE
}

case "$COMMAND" in
  status)
    ddev drush php:eval '
      $storage = \Drupal::service("site_platform_admin.setup_storage");
      print_r($storage->getStatus());
    '
    ;;

  preview)
    ddev drush php:eval '
      $runner = \Drupal::service("site_platform_admin.setup_runner");
      print_r($runner->getPreview());
      print_r($runner->getCompletionReadiness());
    '
    ;;

  run)
    ddev drush php:eval '
      $runner = \Drupal::service("site_platform_admin.setup_runner");
      print_r($runner->run());
    '
    ;;

  complete)
    ddev drush php:eval '
      $runner = \Drupal::service("site_platform_admin.setup_runner");
      print_r($runner->completeAndLock());
    '
    ;;

  unlock)
    ddev drush php:eval '
      \Drupal::service("site_platform_admin.setup_storage")->unlockSetup();
      echo "Setup unlocked." . PHP_EOL;
    '
    ;;

  reset-status)
    ddev drush php:eval '
      \Drupal::service("site_platform_admin.setup_storage")->resetStatus();
      echo "Setup status reset. Existing data was not deleted." . PHP_EOL;
    '
    ;;

  check)
    ./scripts/check-site-setup.sh
    ./scripts/verify-backend-mvp.sh
    ./scripts/check-code.sh
    ;;

  help|--help|-h)
    usage
    ;;

  *)
    echo "Unknown command: $COMMAND" >&2
    usage
    exit 1
    ;;
esac
