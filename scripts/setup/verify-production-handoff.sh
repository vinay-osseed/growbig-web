#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "docs/implementation/phase-29-production-handoff.md"
  "docs/release/backend-final-handoff.md"
  "docs/release/remaining-production-hardening.md"
  "docs/release/backend-deadline-status.md"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "Missing: $file"
    exit 1
  fi
done

grep -q 'deadline-ready backend checkpoint' docs/release/backend-final-handoff.md
grep -q 'Remaining Production Hardening' docs/release/remaining-production-hardening.md
grep -q 'stage deployment' docs/release/remaining-production-hardening.md
grep -q 'Webform' docs/release/backend-final-handoff.md
grep -q 'media asset' docs/release/backend-final-handoff.md

echo "Production handoff verification passed."
