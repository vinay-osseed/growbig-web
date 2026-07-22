#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

say() {
  printf '%s\n' "$*"
}

say "Applying active backend editor UX config..."
ddev drush scr scripts/setup/site_platform_apply_admin_ux.php

say "Deleting local demo/test Site Platform data..."
ddev drush scr scripts/setup/site_platform_delete_demo_data.php -- --execute

say "Rebuilding Drupal cache..."
ddev drush cr

say "Running final backend usability verifier..."
./scripts/setup/verify-backend-admin-usable.sh

say "Backend admin usability completion finished."
