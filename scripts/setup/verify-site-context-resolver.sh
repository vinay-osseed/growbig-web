#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "site-platform/web/modules/custom/site_platform_core/src/Context/SiteContext.php"
  "site-platform/web/modules/custom/site_platform_core/src/Context/SiteContextResolver.php"
  "site-platform/web/modules/custom/site_platform_core/src/Context/SiteContextResolverInterface.php"
  "site-platform/web/modules/custom/site_platform_core/site_platform_core.services.yml"
  "scripts/setup/verify-site-context-resolver.php"
  "docs/implementation/phase-03-entity-site-context-resolver.md"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "Missing: $file"
    exit 1
  fi
done

ddev drush en site_platform_core site_platform_site -y
ddev drush cr

ddev drush php:script /var/www/html/scripts/setup/verify-site-context-resolver.php

echo "Site context resolver shell verification passed."
