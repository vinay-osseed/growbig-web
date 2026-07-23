#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "docs/implementation/phase-28-editor-ux-readiness.md"
  "site-platform/web/modules/custom/site_platform_admin/site_platform_admin.info.yml"
  "site-platform/web/modules/custom/site_platform_admin/site_platform_admin.permissions.yml"
  "site-platform/web/modules/custom/site_platform_admin/site_platform_admin.routing.yml"
  "site-platform/web/modules/custom/site_platform_admin/src/Controller/SitePlatformAdminController.php"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "Missing: $file"
    exit 1
  fi
done

php -l site-platform/web/modules/custom/site_platform_admin/src/Controller/SitePlatformAdminController.php >/dev/null

grep -q 'administer site platform:' site-platform/web/modules/custom/site_platform_admin/site_platform_admin.permissions.yml
grep -q 'manage site platform content:' site-platform/web/modules/custom/site_platform_admin/site_platform_admin.permissions.yml
grep -q 'view site platform reports:' site-platform/web/modules/custom/site_platform_admin/site_platform_admin.permissions.yml
grep -q '/admin/site-platform' site-platform/web/modules/custom/site_platform_admin/site_platform_admin.routing.yml

grep -q 'final editor workflow' docs/implementation/phase-28-editor-ux-readiness.md
grep -q 'post-deadline production-hardening' docs/implementation/phase-28-editor-ux-readiness.md

echo "Editor UX readiness notes verification passed."
