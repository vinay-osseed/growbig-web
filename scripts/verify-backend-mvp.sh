#!/usr/bin/env bash

set -euo pipefail

BASE_URL="${BASE_URL:-https://growbig-web.ddev.site}"
export BASE_URL

echo "Verifying backend MVP at ${BASE_URL}..."

echo "Checking Git working tree..."
git status --short

echo "Checking Drupal custom code..."
./scripts/check-code.sh

echo "Checking Drupal platform..."
./scripts/check-drupal.sh

echo "Checking API smoke tests..."
./scripts/test-api.sh

echo "Checking required API endpoints..."
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

site = fetch("/api/v1/site")
pages = fetch("/api/v1/pages")
home = fetch("/api/v1/pages/home")
about = fetch("/api/v1/pages/about")
services = fetch("/api/v1/content/services")
partners = fetch("/api/v1/content/partners")
team = fetch("/api/v1/content/team")
service_detail = fetch("/api/v1/content/services/website-development")

assert site["type"] == "siteProfile"
assert pages["contractVersion"] == "1.0"
assert pages["count"] >= 2
assert home["slug"] == "home"
assert about["slug"] == "about"
assert services["count"] >= 1
assert partners["count"] >= 1
assert team["count"] >= 1
assert service_detail["item"]["key"] == "website-development"

print("Required backend MVP endpoints verified.")
INNERPY

echo "Backend MVP verification passed."
