#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "docs/implementation/phase-25-api-contract-handoff.md"
  "docs/api/site-platform-v2-api-contract.md"
  "site-platform/web/modules/custom/site_platform_api/site_platform_api.routing.yml"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "Missing: $file"
    exit 1
  fi
done

php <<'PHP'
<?php
$routes = 'site-platform/web/modules/custom/site_platform_api/site_platform_api.routing.yml';
$text = file_get_contents($routes);
if ($text === false) {
  fwrite(STDERR, "Unable to read: $routes\n");
  exit(1);
}

$pattern = '/_controller:\s*[\'\"]?\\\\Drupal\\\\site_platform_api\\\\Controller\\\\([A-Za-z0-9_]+)::/';
preg_match_all($pattern, $text, $matches);
$classes = array_values(array_unique($matches[1] ?? []));

if ($classes === []) {
  fwrite(STDERR, "No site_platform_api controllers found in routing file.\n");
  exit(1);
}

foreach ($classes as $class) {
  $file = "site-platform/web/modules/custom/site_platform_api/src/Controller/$class.php";
  if (!is_file($file)) {
    fwrite(STDERR, "Missing controller file referenced by routing: $file\n");
    exit(1);
  }
}
PHP

routes="site-platform/web/modules/custom/site_platform_api/site_platform_api.routing.yml"
for route in \
  "/api/v1/site" \
  "/api/v1/pages" \
  "/api/v1/pages/{slug}" \
  "/api/v1/routes" \
  "/api/v1/routes/{path}" \
  "/api/v1/menus" \
  "/api/v1/menus/{menu}" \
  "/api/v1/forms/{form}" \
  "/api/v1/forms/{form}/submit" \
  "/api/v1/content" \
  "/api/v1/media" \
  "/api/v1/seo" \
  "/api/v1/analytics" \
  "/api/v1/search"; do
  grep -Fq "$route" "$routes"
done

for endpoint in \
  "GET /api/v1/site" \
  "GET /api/v1/pages" \
  "POST /api/v1/forms/{form}/submit" \
  "GET /api/v1/media" \
  "GET /api/v1/seo" \
  "GET /api/v1/analytics" \
  "GET /api/v1/search?q=term"; do
  grep -Fq "$endpoint" docs/api/site-platform-v2-api-contract.md
done

echo "API contract handoff verification passed."
