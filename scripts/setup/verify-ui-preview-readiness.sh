#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "docs/implementation/phase-35-ui-preview-and-admin-view-review.md"
  "docs/ui/current-actual-ui-and-view-report.md"
  "docs/ui/local-ui-review-checklist.md"
  "site-platform/web/modules/custom/site_platform_admin/site_platform_admin.routing.yml"
  "site-platform/web/modules/custom/site_platform_admin/src/Controller/SitePlatformAdminController.php"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "Missing: $file"
    exit 1
  fi
done

grep -Fq '/admin/site-platform' site-platform/web/modules/custom/site_platform_admin/site_platform_admin.routing.yml
grep -Fq 'SitePlatformAdminController' site-platform/web/modules/custom/site_platform_admin/src/Controller/SitePlatformAdminController.php
grep -Fq 'backend/API-first' docs/ui/current-actual-ui-and-view-report.md
grep -Fq 'not finished in this branch' docs/ui/current-actual-ui-and-view-report.md
grep -Fq 'https://site-platform.ddev.site/admin/site-platform' docs/ui/current-actual-ui-and-view-report.md
grep -Fq 'https://site-platform.ddev.site/admin/content' docs/ui/current-actual-ui-and-view-report.md
grep -Fq 'https://site-platform.ddev.site/admin/structure/webform' docs/ui/current-actual-ui-and-view-report.md
grep -Fq 'public frontend rendering' docs/ui/local-ui-review-checklist.md

echo "UI preview readiness verification passed."
