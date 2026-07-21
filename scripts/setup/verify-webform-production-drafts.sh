#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "docs/implementation/phase-33-webform-production-prep.md"
  "docs/forms/webform-production-drafts.md"
  "setup/forms/growbig.webforms.yml"
  "setup/forms/osseed.webforms.yml"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "Missing: $file"
    exit 1
  fi
done

for file in setup/forms/growbig.webforms.yml setup/forms/osseed.webforms.yml; do
  grep -Fq 'Planning only' "$file"
  grep -Fq 'Do not store secrets' "$file"
  grep -Fq 'storage: webform' "$file"
  grep -Fq 'status: draft' "$file"
  grep -Fq 'recipient: TODO_' "$file"
  grep -Fq 'spam_protection:' "$file"
  grep -Fq 'retention:' "$file"
  grep -Fq 'uploads:' "$file"
done

grep -Fq 'site_key: growbig' setup/forms/growbig.webforms.yml
grep -Fq 'webform_id: growbig_contact' setup/forms/growbig.webforms.yml
grep -Fq 'site_key: osseed' setup/forms/osseed.webforms.yml
grep -Fq 'webform_id: osseed_contact' setup/forms/osseed.webforms.yml
grep -Fq 'Do not import them into stage or production' docs/forms/webform-production-drafts.md
grep -Fq 'Webform production draft verification passed.' docs/implementation/phase-33-webform-production-prep.md

echo "Webform production draft verification passed."
