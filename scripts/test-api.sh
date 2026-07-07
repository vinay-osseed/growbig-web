#!/usr/bin/env bash

set -euo pipefail

BASE_URL="${1:-https://growbig-web.ddev.site}"

echo "Testing Site API..."
curl -ks "${BASE_URL}/api/v1/site" | python3 -m json.tool >/dev/null

echo "Testing Home Page API..."
curl -ks "${BASE_URL}/api/v1/pages/home" | python3 -m json.tool >/dev/null

echo "Testing About Page API..."
curl -ks "${BASE_URL}/api/v1/pages/about" | python3 -m json.tool >/dev/null

echo "Testing missing Page API..."
STATUS_CODE="$(curl -ks -o /tmp/growbig-missing-page-api.json -w "%{http_code}" "${BASE_URL}/api/v1/pages/missing")"

if [ "${STATUS_CODE}" != "404" ]; then
  echo "Expected 404 for missing page, got ${STATUS_CODE}"
  cat /tmp/growbig-missing-page-api.json
  exit 1
fi

python3 -m json.tool /tmp/growbig-missing-page-api.json >/dev/null

echo "API smoke tests passed."
