#!/usr/bin/env bash
set -euo pipefail

BASE_URL="${1:-https://site-platform.ddev.site}"

python3 scripts/test/test-frontend-api.py "$BASE_URL"
python3 scripts/test/test-frontend-backend-final.py "$BASE_URL"
