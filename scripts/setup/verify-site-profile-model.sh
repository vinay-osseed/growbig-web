#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "site-platform/web/modules/custom/site_platform_site/site_platform_site.info.yml"
  "site-platform/web/modules/custom/site_platform_site/README.md"
  "site-platform/web/modules/custom/site_platform_site/config/install/node.type.site_profile.yml"
  "site-platform/web/modules/custom/site_platform_site/config/install/field.storage.node.field_site_key.yml"
  "site-platform/web/modules/custom/site_platform_site/config/install/field.field.node.site_profile.field_site_key.yml"
  "docs/implementation/phase-02-site-profile-model.md"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "Missing: $file"
    exit 1
  fi
done

if ! ddev drush pm:list --type=module --status=enabled --no-core | grep -q site_platform_core; then
  echo "site_platform_core must be enabled first."
  exit 1
fi

ddev drush en site_platform_site -y
ddev drush cr

ddev drush config:get node.type.site_profile >/dev/null
ddev drush config:get field.field.node.site_profile.field_site_key >/dev/null
ddev drush config:get field.field.node.site_profile.field_api_domains >/dev/null
ddev drush config:get field.field.node.site_profile.field_admin_domains >/dev/null
ddev drush config:get field.field.node.site_profile.field_frontend_domains >/dev/null

echo "Site Profile model verification passed."
