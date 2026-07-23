#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

say() { printf '%s\n' "$*"; }

say "Rebuilding Drupal cache..."
ddev drush cr

say "Verifying Site Platform API index endpoints..."
./scripts/setup/verify-site-platform-api-index.sh

say "Site Platform API index update finished."
