#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "docs/implementation/phase-23-quality-ci-readiness.md"
  ".github/workflows/site-platform-v2-backend-checks.yml"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "Missing: $file"
    exit 1
  fi
done

while IFS= read -r -d '' file; do
  php -l "$file" >/dev/null
done < <(find site-platform/web/modules/custom -type f -name '*.php' -print0)

while IFS= read -r -d '' file; do
  bash -n "$file"
done < <(find scripts/setup -type f -name '*.sh' -print0)

grep -q 'Site Platform v2 Backend Checks' .github/workflows/site-platform-v2-backend-checks.yml
grep -q 'php -l' .github/workflows/site-platform-v2-backend-checks.yml
grep -q 'bash -n' .github/workflows/site-platform-v2-backend-checks.yml

echo "Quality and CI readiness verification passed."
