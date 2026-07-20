#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

./scripts/setup/verify-release-readiness-checkpoint.sh
./scripts/setup/verify-quality-ci-readiness.sh
./scripts/setup/verify-config-release-readiness.sh
./scripts/setup/verify-api-contract-handoff.sh

if [ ! -f docs/release/backend-deadline-status.md ]; then
  echo "Missing: docs/release/backend-deadline-status.md"
  exit 1
fi

if [ ! -f docs/implementation/phase-26-backend-deadline-closeout.md ]; then
  echo "Missing: docs/implementation/phase-26-backend-deadline-closeout.md"
  exit 1
fi

echo "Backend deadline closeout verification passed."
