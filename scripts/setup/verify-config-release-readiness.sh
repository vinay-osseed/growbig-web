#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "docs/implementation/phase-24-config-release-readiness.md"
  "site-platform/composer.json"
  "site-platform/web/sites/default/settings.php"
  "site-platform/web/sites/default/settings.platform.php"
  "site-platform/web/sites/default/settings.local.example.php"
  "site-platform/web/sites/default/settings.prod.example.php"
  "site-platform/config/sync"
  "site-platform/config/splits/dev"
  "site-platform/config/splits/prod"
  "setup/examples/sample.site.yml"
  "site-platform/web/modules/custom/site_platform_core/site_platform_core.info.yml"
  "site-platform/web/modules/custom/site_platform_site/site_platform_site.info.yml"
  "site-platform/web/modules/custom/site_platform_page/site_platform_page.info.yml"
  "site-platform/web/modules/custom/site_platform_menu/site_platform_menu.info.yml"
  "site-platform/web/modules/custom/site_platform_component/site_platform_component.info.yml"
  "site-platform/web/modules/custom/site_platform_form/site_platform_form.info.yml"
  "site-platform/web/modules/custom/site_platform_content/site_platform_content.info.yml"
  "site-platform/web/modules/custom/site_platform_media/site_platform_media.info.yml"
  "site-platform/web/modules/custom/site_platform_api/site_platform_api.info.yml"
  "site-platform/web/modules/custom/site_platform_setup/site_platform_setup.info.yml"
  "site-platform/web/modules/custom/site_platform_admin/site_platform_admin.info.yml"
)

for path in "${required[@]}"; do
  if [ ! -e "$path" ]; then
    echo "Missing: $path"
    exit 1
  fi
done

php -l site-platform/web/sites/default/settings.php >/dev/null
php -l site-platform/web/sites/default/settings.platform.php >/dev/null
php -l site-platform/web/sites/default/settings.local.example.php >/dev/null
php -l site-platform/web/sites/default/settings.prod.example.php >/dev/null

if ! ddev composer show drupal/webform >/dev/null 2>&1; then
  echo "Missing Composer package in site-platform project: drupal/webform"
  echo "Run: ddev composer require drupal/webform -W"
  exit 1
fi

grep -q 'media_assets:' setup/examples/sample.site.yml
grep -q 'storage: webform' setup/examples/sample.site.yml
grep -q 'components:' setup/examples/sample.site.yml

echo "Config and release readiness verification passed."
