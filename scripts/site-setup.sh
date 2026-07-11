#!/usr/bin/env bash

set -euo pipefail

COMMAND="${1:-help}"

run_drush() {
  if [ -n "${DRUSH_BIN:-}" ]; then
    "$DRUSH_BIN" "$@"
  elif command -v ddev >/dev/null 2>&1; then
    ddev drush "$@"
  else
    drush "$@"
  fi
}

usage() {
  cat <<'USAGE'
Site Setup helper

Usage:
  ./scripts/site-setup.sh status
  ./scripts/site-setup.sh preview
  ./scripts/site-setup.sh sites
  ./scripts/site-setup.sh import setup/site.yml
  ./scripts/site-setup.sh run
  ./scripts/site-setup.sh complete
  ./scripts/site-setup.sh unlock
  ./scripts/site-setup.sh reset-status
  ./scripts/site-setup.sh check

Notes:
  - This helper uses native Drush commands.
  - In DDEV it uses: ddev drush
  - Outside DDEV it uses: drush
  - You can override the Drush binary with DRUSH_BIN.
  - Example: DRUSH_BIN=/path/to/vendor/bin/drush ./scripts/site-setup.sh status
  - It does not delete pages, forms, roles, content, files, or submissions.
  - Do not run drush cex after entering environment-specific setup values unless intentional.
USAGE
}

case "$COMMAND" in
  status)
    run_drush site-platform:setup-status
    ;;

  preview)
    run_drush site-platform:setup-preview
    ;;

  sites)
    run_drush site-platform:setup-sites
    ;;

  import)
    FILE="${2:-setup/site.yml}"
    run_drush site-platform:setup-import "$FILE"
    ;;

  run)
    run_drush site-platform:setup-run
    ;;

  complete)
    run_drush site-platform:setup-complete
    ;;

  unlock)
    run_drush site-platform:setup-unlock
    ;;

  reset-status)
    run_drush site-platform:setup-reset-status
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
