#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

./scripts/setup/verify-final-backend-checkpoint.sh
./scripts/setup/verify-admin-permission-readiness.sh

echo "Release readiness backend checkpoint passed."
