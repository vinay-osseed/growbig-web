#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "docs/implementation/phase-31-production-prep-without-stage.md"
  "docs/release/production-prep-checklist.md"
  "docs/setup/real-site-yaml-prep.md"
  "docs/security/secrets-and-permissions-checklist.md"
  "docs/forms/webform-production-hardening.md"
  "docs/media/media-production-workflow.md"
  "docs/admin/editor-ux-cleanup-plan.md"
  "docs/deployment/production-deployment-plan-no-stage.md"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "Missing: $file"
    exit 1
  fi
done

grep -Fq 'Stage deployment is intentionally skipped' docs/release/production-prep-checklist.md
grep -Fq 'setup/sites/growbig.site.yml' docs/setup/real-site-yaml-prep.md
grep -Fq 'setup/sites/osseed.site.yml' docs/setup/real-site-yaml-prep.md
grep -Fq 'Do not commit secrets' docs/security/secrets-and-permissions-checklist.md
grep -Fq 'Webform is the primary real form system' docs/forms/webform-production-hardening.md
grep -Fq 'Choose one media file strategy' docs/media/media-production-workflow.md
grep -Fq 'default Drupal admin UI is not the final editor workflow' docs/admin/editor-ux-cleanup-plan.md
grep -Fq 'Stage deployment is skipped for now' docs/deployment/production-deployment-plan-no-stage.md

echo "Production prep readiness verification passed."
