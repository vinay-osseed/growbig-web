#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "site-platform/web/sites/default/settings.php"
  "site-platform/web/sites/default/settings.platform.php"
  "site-platform/web/sites/default/settings.local.example.php"
  "site-platform/web/sites/default/settings.prod.example.php"
  "site-platform/web/sites/default/services.prod.yml"
  "site-platform/web/sites/default/.gitignore"
  "site-platform/config/sync/.gitkeep"
  "site-platform/config/splits/dev/README.md"
  "site-platform/config/splits/prod/README.md"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "Missing: $file"
    exit 1
  fi
done

if grep -R "\$databases\['default'\]\['default'\].*password.*db" -n \
  site-platform/web/sites/default/settings.php \
  site-platform/web/sites/default/settings.platform.php 2>/dev/null; then
  echo "Committed DDEV database password found in shared settings."
  exit 1
fi

php -l site-platform/web/sites/default/settings.php
php -l site-platform/web/sites/default/settings.platform.php
php -l site-platform/web/sites/default/settings.local.example.php
php -l site-platform/web/sites/default/settings.prod.example.php

echo "Settings base verification passed."
