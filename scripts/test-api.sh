#!/usr/bin/env bash

set -euo pipefail

BASE_URL="${1:-https://growbig-web.ddev.site}"
export BASE_URL

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

echo "Validating dynamic content lists..."
python3 - <<'INNERPY'
import json
import os
import subprocess

base_url = os.environ["BASE_URL"]

def fetch(path):
    result = subprocess.run(
        ["curl", "-ks", f"{base_url}{path}"],
        check=True,
        capture_output=True,
        text=True,
    )
    return json.loads(result.stdout)

home = fetch("/api/v1/pages/home")
about = fetch("/api/v1/pages/about")

home_content_lists = [section for section in home["sections"] if section["type"] == "contentList"]
about_content_lists = [section for section in about["sections"] if section["type"] == "contentList"]

assert home_content_lists, "Home contentList section missing"
assert about_content_lists, "About contentList section missing"

partners = home_content_lists[0].get("items", [])
team = about_content_lists[0].get("items", [])

assert len(partners) == 4, f"Expected 4 partners, got {len(partners)}"
assert len(team) == 3, f"Expected 3 team members, got {len(team)}"

print("Dynamic content list checks passed.")
INNERPY

echo "API smoke tests passed."
