#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "site-platform/web/modules/custom/site_platform_setup/src/Commands/SitePlatformSetupCommands.php"
  "setup/examples/sample.site.yml"
  "docs/implementation/phase-13-yaml-menu-form-import.md"
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

dry_run_output="$(ddev drush site-platform:setup-import /var/www/html/setup/examples/sample.site.yml --dry-run)"
printf '%s' "$dry_run_output" | grep -q 'Site Menus: 1'
printf '%s' "$dry_run_output" | grep -q 'Site Menu Items: 3'
printf '%s' "$dry_run_output" | grep -q 'Site Forms: 1'
printf '%s' "$dry_run_output" | grep -q 'Site Form Fields: 3'

import_output="$(ddev drush site-platform:setup-import /var/www/html/setup/examples/sample.site.yml)"
printf '%s' "$import_output" | grep -q 'Setup YAML import completed'
printf '%s' "$import_output" | grep -q 'Site Menus: 1'
printf '%s' "$import_output" | grep -q 'Site Forms: 1'

ddev drush cr

menu_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/menus/main?site=yamltest')"
form_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/forms/contact?site=yamltest')"
submit_json="$(curl -fsS -X POST 'https://site-platform.ddev.site/api/v1/forms/contact/submit?site=yamltest' -H 'Content-Type: application/json' --data '{"name":"YAML User","email":"yaml@example.com","message":"Hello YAML"}')"
invalid_json="$(curl -s -X POST 'https://site-platform.ddev.site/api/v1/forms/contact/submit?site=yamltest' -H 'Content-Type: application/json' --data '{"name":"","email":"bad-email","message":""}')"

printf '%s' "$menu_json" | grep -q '"title":"Home"'
printf '%s' "$menu_json" | grep -q '"title":"About"'
printf '%s' "$menu_json" | grep -q '"isButton":true'
printf '%s' "$form_json" | grep -q '"label":"Contact YAML Test"'
printf '%s' "$form_json" | grep -q '"key":"email"'
printf '%s' "$submit_json" | grep -q '"submissionId"'
printf '%s' "$invalid_json" | grep -q '"code":"validation_failed"'

echo "YAML menu and form import verification passed."
