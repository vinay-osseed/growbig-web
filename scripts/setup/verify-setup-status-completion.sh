#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "site-platform/web/modules/custom/site_platform_setup/src/Commands/SitePlatformSetupCommands.php"
  "docs/implementation/phase-16-setup-status-completion.md"
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

ddev drush list | grep -q 'site-platform:setup-complete'
ddev drush list | grep -q 'site-platform:setup-unlock'
ddev drush list | grep -q 'site-platform:setup-reset-status'

ddev drush site-platform:setup-reset-status >/dev/null
ddev drush site-platform:setup-run >/dev/null

status_run="$(ddev drush site-platform:setup-status)"
printf '%s' "$status_run" | grep -q 'Status: foundation_ready'
printf '%s' "$status_run" | grep -q 'Completion status: not_completed'
printf '%s' "$status_run" | grep -q 'Setup locked: no'

complete_output="$(ddev drush site-platform:setup-complete)"
printf '%s' "$complete_output" | grep -q 'Setup marked complete and locked'

status_complete="$(ddev drush site-platform:setup-status)"
printf '%s' "$status_complete" | grep -q 'Completion status: complete'
printf '%s' "$status_complete" | grep -q 'Setup locked: yes'

locked_output="$(ddev drush site-platform:setup-complete)"
printf '%s' "$locked_output" | grep -q 'Setup is already locked'

unlock_output="$(ddev drush site-platform:setup-unlock)"
printf '%s' "$unlock_output" | grep -q 'Setup unlocked'

status_unlocked="$(ddev drush site-platform:setup-status)"
printf '%s' "$status_unlocked" | grep -q 'Completion status: unlocked'
printf '%s' "$status_unlocked" | grep -q 'Setup locked: no'

force_output="$(ddev drush site-platform:setup-complete --force)"
printf '%s' "$force_output" | grep -q 'Setup marked complete and locked'

reset_output="$(ddev drush site-platform:setup-reset-status)"
printf '%s' "$reset_output" | grep -q 'Setup tracking status reset'

status_reset="$(ddev drush site-platform:setup-status)"
printf '%s' "$status_reset" | grep -q 'Status: not_run'
printf '%s' "$status_reset" | grep -q 'Completion status: not_completed'
printf '%s' "$status_reset" | grep -q 'Setup locked: no'

echo "Setup status and completion verification passed."
