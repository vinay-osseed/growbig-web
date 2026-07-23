#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

./scripts/setup/verify-webform-backed-form-api.sh
./scripts/setup/verify-media-api.sh
./scripts/setup/verify-yaml-component-import.sh
./scripts/setup/verify-seo-analytics-search-api.sh
./scripts/setup/verify-setup-status-completion.sh

echo "Final backend deadline checkpoint verification passed."
