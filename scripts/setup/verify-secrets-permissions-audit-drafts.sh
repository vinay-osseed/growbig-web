#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "docs/implementation/phase-34-secrets-permissions-audit-drafts.md"
  "docs/security/production-secrets-permissions-audit.md"
  "setup/security/production-secrets.audit.yml"
  "setup/security/production-permissions.audit.yml"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "Missing: $file"
    exit 1
  fi
done

grep -Fq 'Do not commit real secret values' docs/security/production-secrets-permissions-audit.md
grep -Fq 'These files are planning drafts only' docs/security/production-secrets-permissions-audit.md
grep -Fq 'stage deployment' docs/implementation/phase-34-secrets-permissions-audit-drafts.md
grep -Fq 'production deployment' docs/implementation/phase-34-secrets-permissions-audit-drafts.md

for file in setup/security/production-secrets.audit.yml setup/security/production-permissions.audit.yml; do
  grep -Fq 'Planning only' "$file"
  grep -Fq 'status: draft' "$file"
  grep -Fq 'stage_deployment: skipped' "$file"
  grep -Fq 'production_deployment: not_started' "$file"
done

grep -Fq 'no_real_secret_values: true' setup/security/production-secrets.audit.yml
grep -Fq 'database_credentials' setup/security/production-secrets.audit.yml
grep -Fq 'drupal_hash_salt' setup/security/production-secrets.audit.yml
grep -Fq 'smtp_credentials' setup/security/production-secrets.audit.yml
grep -Fq 'historical_sync_credentials' setup/security/production-secrets.audit.yml

grep -Fq 'no_permission_changes: true' setup/security/production-permissions.audit.yml
grep -Fq 'site_platform_administrator' setup/security/production-permissions.audit.yml
grep -Fq 'site_platform_editor' setup/security/production-permissions.audit.yml
grep -Fq 'webform_manager' setup/security/production-permissions.audit.yml
grep -Fq 'anonymous' setup/security/production-permissions.audit.yml
grep -Fq 'POST /api/v1/forms/{form}/submit' setup/security/production-permissions.audit.yml

echo "Secrets and permissions audit draft verification passed."
