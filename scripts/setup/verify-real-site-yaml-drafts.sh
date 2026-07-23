#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "docs/implementation/phase-32-real-site-yaml-drafts.md"
  "docs/setup/real-site-yaml-drafts.md"
  "setup/sites/growbig.site.yml"
  "setup/sites/osseed.site.yml"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "Missing: $file"
    exit 1
  fi
done

for file in setup/sites/growbig.site.yml setup/sites/osseed.site.yml; do
  grep -Fq 'Do not store secrets' "$file"
  grep -Fq 'site:' "$file"
  grep -Fq 'pages:' "$file"
  grep -Fq 'menus:' "$file"
  grep -Fq 'forms:' "$file"
  grep -Fq 'content_blocks:' "$file"
  grep -Fq 'media_assets:' "$file"
  grep -Fq 'seo:' "$file"
  grep -Fq 'analytics:' "$file"
  grep -Fq 'storage: webform' "$file"
  grep -Fq 'enabled: false' "$file"
done

grep -Fq 'key: growbig' setup/sites/growbig.site.yml
grep -Fq 'key: osseed' setup/sites/osseed.site.yml
grep -Fq 'TODO_GROWBIG_FRONTEND_DOMAIN' setup/sites/growbig.site.yml
grep -Fq 'TODO_OSSEED_FRONTEND_DOMAIN' setup/sites/osseed.site.yml
grep -Fq 'Webform as the primary storage' docs/setup/real-site-yaml-drafts.md
grep -Fq 'Do not import them into stage or production' docs/setup/real-site-yaml-drafts.md

echo "Real site YAML draft verification passed."
