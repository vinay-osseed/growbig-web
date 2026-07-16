#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "site-platform/web/modules/custom/site_platform_setup/src/Commands/SitePlatformSetupCommands.php"
  "setup/examples/sample.site.yml"
  "docs/implementation/phase-12-yaml-setup-import.md"
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

ddev drush list | grep -q 'site-platform:setup-import'

dry_run_output="$(ddev drush site-platform:setup-import /var/www/html/setup/examples/sample.site.yml --dry-run)"
printf '%s' "$dry_run_output" | grep -q 'Setup YAML dry-run completed'
printf '%s' "$dry_run_output" | grep -q 'Site key: yamltest'

import_output="$(ddev drush site-platform:setup-import /var/www/html/setup/examples/sample.site.yml)"
printf '%s' "$import_output" | grep -q 'Setup YAML import completed'
printf '%s' "$import_output" | grep -q 'Site Pages: 2'
printf '%s' "$import_output" | grep -q 'Site Content Blocks: 2'

ddev drush cr

site_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/site?site=yamltest')"
home_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/pages/home?site=yamltest')"
about_route_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/routes/about?site=yamltest')"
content_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/content/global/footer_cta?site=yamltest')"

printf '%s' "$site_json" | grep -q '"siteKey":"yamltest"'
printf '%s' "$home_json" | grep -q '"title":"YAML Test Home"'
printf '%s' "$about_route_json" | grep -q '"title":"YAML Test About"'
printf '%s' "$content_json" | grep -q '"Imported reusable footer CTA"'

echo "YAML setup import verification passed."
