#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "site-platform/web/modules/custom/site_platform_setup/src/Commands/SitePlatformSetupCommands.php"
  "setup/examples/sample.site.yml"
  "docs/implementation/phase-14-yaml-component-import.md"
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
printf '%s' "$dry_run_output" | grep -q 'Page Components: 4'

import_output="$(ddev drush site-platform:setup-import /var/www/html/setup/examples/sample.site.yml)"
printf '%s' "$import_output" | grep -q 'Setup YAML import completed'
printf '%s' "$import_output" | grep -q 'Page Components: 4'

ddev drush cr

home_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/pages/home?site=yamltest')"
about_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/pages/about?site=yamltest')"
route_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/routes/about?site=yamltest')"

printf '%s' "$home_json" | grep -q '"components"'
printf '%s' "$home_json" | grep -q '"type":"hero"'
printf '%s' "$home_json" | grep -q '"type":"rich_text"'
printf '%s' "$home_json" | grep -q '"type":"cta"'
printf '%s' "$home_json" | grep -q '"buttonLabel":"Contact us"'
printf '%s' "$home_json" | grep -q '"YAML-driven setup"'
printf '%s' "$about_json" | grep -q '"About YAML Test"'
printf '%s' "$route_json" | grep -q '"components"'

echo "YAML component import verification passed."
