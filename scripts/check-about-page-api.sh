#!/usr/bin/env bash

set -euo pipefail

BASE_URL="${BASE_URL:-https://growbig-web.ddev.site}"

echo "Checking About page API content at ${BASE_URL}..."

BASE_URL="$BASE_URL" python3 - <<'PY'
import json
import os
import subprocess

base_url = os.environ["BASE_URL"].rstrip("/")
result = subprocess.run(
    ["curl", "-sk", f"{base_url}/api/v1/pages/about"],
    check=True,
    capture_output=True,
    text=True,
)

data = json.loads(result.stdout)
payload = json.dumps(data)

required = [
    "We Are GrowBig",
    "Mission",
    "Vision",
    "Core Values",
    "Leadership",
]

missing = [item for item in required if item not in payload]
if missing:
    raise SystemExit("Missing About page API content: " + ", ".join(missing))

sections = data.get("sections", [])
if not isinstance(sections, list) or len(sections) < 4:
    raise SystemExit(f"About page should have multiple sections. Found: {len(sections)}")

print("About page API content verified.")
PY
