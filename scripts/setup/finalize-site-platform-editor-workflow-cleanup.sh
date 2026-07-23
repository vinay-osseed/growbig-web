#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

say() { printf '%s\n' "$*"; }

say "Patching Site Platform source workflow..."
python3 scripts/setup/site_platform_patch_editor_workflow_source.py

say "Applying active editor workflow cleanup..."
ddev drush scr scripts/setup/site_platform_cleanup_editor_workflow.php

say "Rebuilding Drupal cache..."
ddev drush cr

say "Verifying editor workflow cleanup and API hotfix..."
./scripts/setup/verify-site-platform-editor-workflow-cleanup.sh

say "Site Platform editor workflow cleanup finished."
