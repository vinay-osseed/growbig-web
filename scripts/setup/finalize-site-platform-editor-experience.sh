#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

say() { printf '%s\n' "$*"; }

say "Applying professional editor experience..."
ddev drush scr scripts/setup/site_platform_apply_editor_experience.php

say "Rebuilding Drupal cache..."
ddev drush cr

say "Verifying professional editor experience..."
./scripts/setup/verify-site-platform-editor-experience.sh

say "Site Platform professional editor experience finished."
