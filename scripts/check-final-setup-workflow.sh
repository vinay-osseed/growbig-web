#!/usr/bin/env bash

set -euo pipefail

echo "Checking final setup workflow..."

bash -n scripts/site-setup.sh

./scripts/site-setup.sh status >/dev/null
./scripts/site-setup.sh preview >/dev/null
./scripts/site-setup.sh sites >/dev/null

./scripts/check-site-setup.sh
./scripts/check-site-api-setup-profile.sh
./scripts/check-code.sh

echo "Final setup workflow verified."
