#!/usr/bin/env bash

set -euo pipefail

BASE_URL="${BASE_URL:-https://growbig-web.ddev.site}"

echo "Checking Site API setup profile at ${BASE_URL}..."

BASE_URL="$BASE_URL" python3 - <<'PY'
import json
import os
import subprocess

base_url = os.environ["BASE_URL"].rstrip("/")
result = subprocess.run(
    ["curl", "-sk", f"{base_url}/api/v1/site"],
    check=True,
    capture_output=True,
    text=True,
)

data = json.loads(result.stdout)

profile = data.get("setupProfile")
if not isinstance(profile, dict):
    raise SystemExit("Missing setupProfile in Site API response.")

expected = {
    "siteKey": "growbig",
    "primaryDomain": "growbig-web.ddev.site",
    "frontendUrl": "https://growbig-web.ddev.site",
    "apiUrl": "https://api.growbig-web.ddev.site",
}

for key, value in expected.items():
    if profile.get(key) != value:
        raise SystemExit(f"Unexpected setupProfile.{key}: {profile.get(key)!r}")

print("Site API setup profile verified.")
PY
