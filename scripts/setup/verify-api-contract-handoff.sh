#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "docs/implementation/phase-25-api-contract-handoff.md"
  "docs/api/site-platform-v2-api-contract.md"
  "site-platform/web/modules/custom/site_platform_api/site_platform_api.routing.yml"
  "site-platform/web/modules/custom/site_platform_api/src/Controller/SiteApiController.php"
  "site-platform/web/modules/custom/site_platform_api/src/Controller/PageApiController.php"
  "site-platform/web/modules/custom/site_platform_api/src/Controller/RouteApiController.php"
  "site-platform/web/modules/custom/site_platform_api/src/Controller/MenuApiController.php"
  "site-platform/web/modules/custom/site_platform_api/src/Controller/FormApiController.php"
  "site-platform/web/modules/custom/site_platform_api/src/Controller/ContentApiController.php"
  "site-platform/web/modules/custom/site_platform_api/src/Controller/MediaApiController.php"
  "site-platform/web/modules/custom/site_platform_api/src/Controller/SeoApiController.php"
  "site-platform/web/modules/custom/site_platform_api/src/Controller/AnalyticsApiController.php"
  "site-platform/web/modules/custom/site_platform_api/src/Controller/SearchApiController.php"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "Missing: $file"
    exit 1
  fi
done

routes="site-platform/web/modules/custom/site_platform_api/site_platform_api.routing.yml"
for route in \
  "/api/v1/site" \
  "/api/v1/pages" \
  "/api/v1/routes" \
  "/api/v1/menus" \
  "/api/v1/forms/{form}" \
  "/api/v1/content" \
  "/api/v1/media" \
  "/api/v1/seo" \
  "/api/v1/analytics" \
  "/api/v1/search"; do
  grep -q "$route" "$routes"
done

for endpoint in \
  "GET /api/v1/site" \
  "GET /api/v1/pages" \
  "POST /api/v1/forms/{form}/submit" \
  "GET /api/v1/media" \
  "GET /api/v1/seo" \
  "GET /api/v1/analytics" \
  "GET /api/v1/search?q=term"; do
  grep -q "$endpoint" docs/api/site-platform-v2-api-contract.md
done

echo "API contract handoff verification passed."
