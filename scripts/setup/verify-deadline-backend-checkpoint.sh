#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

./scripts/setup/verify-webform-backed-form-api.sh
./scripts/setup/verify-yaml-menu-form-import.sh
./scripts/setup/verify-media-api.sh
./scripts/setup/verify-basic-site-page-api.sh
./scripts/setup/verify-route-resolver-api.sh
./scripts/setup/verify-menu-api.sh
./scripts/setup/verify-component-model-api.sh
./scripts/setup/verify-content-api.sh
./scripts/setup/verify-yaml-component-import.sh
./scripts/setup/verify-site-reset-commands.sh
./scripts/setup/verify-setup-status-completion.sh

echo "Deadline backend checkpoint verification passed."
