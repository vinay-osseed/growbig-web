#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "site-platform/web/modules/custom/site_platform_setup/src/Commands/SitePlatformSetupCommands.php"
  "docs/implementation/phase-15-site-reset-commands.md"
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

ddev drush list | grep -q 'site-platform:setup-reset-site'

ddev drush site-platform:setup-import /var/www/html/setup/examples/sample.site.yml >/dev/null

dry_run_output="$(ddev drush site-platform:setup-reset-site yamltest)"
printf '%s' "$dry_run_output" | grep -q 'Site reset dry-run'
printf '%s' "$dry_run_output" | grep -q 'Site key: yamltest'
printf '%s' "$dry_run_output" | grep -q 'Site Profile: 1'
printf '%s' "$dry_run_output" | grep -q 'Site Page: 2'
printf '%s' "$dry_run_output" | grep -q 'Page Components: 4'
printf '%s' "$dry_run_output" | grep -q 'No records were deleted'

execute_output="$(ddev drush site-platform:setup-reset-site yamltest --execute)"
printf '%s' "$execute_output" | grep -q 'Site reset execute mode'
printf '%s' "$execute_output" | grep -q 'Deleted'

site_count="$(ddev drush php:eval '$ids = \Drupal::entityTypeManager()->getStorage("node")->getQuery()->accessCheck(FALSE)->condition("type", "site_profile")->condition("field_site_key", "yamltest")->execute(); echo count($ids);')"
if [ "$site_count" != "0" ]; then
  echo "Expected yamltest site to be removed, found: $site_count"
  exit 1
fi

growbig_count="$(ddev drush php:eval '$ids = \Drupal::entityTypeManager()->getStorage("node")->getQuery()->accessCheck(FALSE)->condition("type", "site_profile")->condition("field_site_key", "growbig")->execute(); echo count($ids);')"
if [ "$growbig_count" != "1" ]; then
  echo "Expected growbig site to remain, found: $growbig_count"
  exit 1
fi

ddev drush site-platform:setup-import /var/www/html/setup/examples/sample.site.yml >/dev/null
ddev drush cr

site_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/site?site=yamltest')"
printf '%s' "$site_json" | grep -q '"siteKey":"yamltest"'

echo "Site reset command verification passed."
