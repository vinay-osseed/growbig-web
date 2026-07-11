#!/usr/bin/env bash

set -euo pipefail

echo "Checking analytics config API..."

python3 - <<'PY'
import json
import subprocess

result = subprocess.run(
    ["curl", "-ks", "https://growbig-web.ddev.site/api/v1/analytics/config"],
    check=True,
    capture_output=True,
    text=True,
)

data = json.loads(result.stdout)

for key in ["enabled", "provider", "measurementId"]:
    if key not in data:
        raise SystemExit(f"Missing analytics config key: {key}")

if not isinstance(data["enabled"], bool):
    raise SystemExit("Analytics enabled must be boolean.")

if data["provider"] not in ["none", "google_analytics"]:
    raise SystemExit("Unexpected analytics provider.")

print("Analytics config API verified.")
PY
