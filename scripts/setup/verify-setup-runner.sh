#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "site-platform/web/modules/custom/site_platform_setup/site_platform_setup.info.yml"
  "site-platform/web/modules/custom/site_platform_setup/drush.services.yml"
  "site-platform/web/modules/custom/site_platform_setup/src/Commands/SitePlatformSetupCommands.php"
  "site-platform/web/modules/custom/site_platform_setup/README.md"
  "docs/implementation/phase-11-setup-runner-foundation.md"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "Missing: $file"
    exit 1
  fi
done

php -l site-platform/web/modules/custom/site_platform_setup/src/Commands/SitePlatformSetupCommands.php >/dev/null

ddev drush en site_platform_core site_platform_site site_platform_page site_platform_menu site_platform_component site_platform_form site_platform_content site_platform_setup site_platform_api -y
ddev drush cr

ddev drush list | grep -q 'site-platform:setup-preview'
ddev drush list | grep -q 'site-platform:setup-run'
ddev drush list | grep -q 'site-platform:setup-status'
ddev drush list | grep -q 'site-platform:setup-reset-demo'

preview_output="$(ddev drush site-platform:setup-preview)"
run_output="$(ddev drush site-platform:setup-run)"
status_output="$(ddev drush site-platform:setup-status)"
reset_output="$(ddev drush site-platform:setup-reset-demo)"

printf '%s' "$preview_output" | grep -q 'Setup runner preview'
printf '%s' "$run_output" | grep -q 'Setup runner foundation recorded successfully'
printf '%s' "$status_output" | grep -q 'Status: foundation_ready'
printf '%s' "$reset_output" | grep -q 'Demo reset dry-run'
printf '%s' "$reset_output" | grep -q 'No records were deleted'

echo "Setup runner verification passed."
