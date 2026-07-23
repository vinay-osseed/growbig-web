#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

say() { printf '%s\n' "$*"; }

say "Applying full Site Platform page builder model..."
ddev drush scr scripts/setup/site_platform_apply_full_page_builder.php

say "Rebuilding Drupal cache..."
ddev drush cr

say "Verifying full Site Platform page builder model..."
./scripts/setup/verify-site-platform-full-page-builder.sh

say "Site Platform full page builder completion finished."
