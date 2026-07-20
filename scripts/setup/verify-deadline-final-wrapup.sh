#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

./scripts/setup/verify-quality-ci-readiness.sh
./scripts/setup/verify-config-release-readiness.sh
./scripts/setup/verify-api-contract-handoff.sh
./scripts/setup/verify-editor-ux-readiness.sh
./scripts/setup/verify-production-handoff.sh
./scripts/setup/verify-runtime-api-smoke.sh

if [ ! -f docs/implementation/phase-30-final-deadline-wrapup.md ]; then
  echo "Missing: docs/implementation/phase-30-final-deadline-wrapup.md"
  exit 1
fi

grep -q 'Final backend deadline wrapup verification passed.' docs/implementation/phase-30-final-deadline-wrapup.md

echo "Final backend deadline wrapup verification passed."
