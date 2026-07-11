#!/usr/bin/env bash

set -euo pipefail

COMMAND="${1:-help}"

usage() {
  cat <<'USAGE'
Site Setup helper

Usage:
  ./scripts/site-setup.sh status
  ./scripts/site-setup.sh preview
  ./scripts/site-setup.sh import setup/site.yml
  ./scripts/site-setup.sh run
  ./scripts/site-setup.sh complete
  ./scripts/site-setup.sh unlock
  ./scripts/site-setup.sh reset-status
  ./scripts/site-setup.sh check

Notes:
  - This helper uses native Drush commands.
  - It does not delete pages, forms, roles, content, files, or submissions.
  - Do not run drush cex after entering environment-specific setup values unless intentional.
USAGE
}

case "$COMMAND" in
  status)
    ddev drush site-platform:setup-status
    ;;

  preview)
    ddev drush site-platform:setup-preview
    ;;

  import)
    FILE="${2:-setup/site.yml}"
    ddev drush site-platform:setup-import "$FILE"
    ;;

  run)
    ddev drush site-platform:setup-run
    ;;

  complete)
    ddev drush site-platform:setup-complete
    ;;

  unlock)
    ddev drush site-platform:setup-unlock
    ;;

  reset-status)
    ddev drush site-platform:setup-reset-status
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
