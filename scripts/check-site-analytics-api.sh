#!/usr/bin/env bash
set -euo pipefail

echo "Checking per-site analytics API..."

ddev drush php:eval '
$field = \Drupal\field\Entity\FieldConfig::loadByName("node", "site_profile", "field_ga_measurement_id");
if (!$field) {
  throw new RuntimeException("Site Profile GA field is missing.");
}
echo "Site Profile GA field verified.\n";
'

python3 - <<'PY'
import json
import subprocess

url = "https://growbig-web.ddev.site/api/v1/analytics/config"
payload = subprocess.check_output(["curl", "-sk", url], text=True)
data = json.loads(payload)

if "siteKey" not in data:
    raise SystemExit("analytics config missing siteKey")

if "measurementId" not in data:
    raise SystemExit("analytics config missing measurementId")

print("Per-site analytics API verified.")
PY

echo "Per-site analytics API check passed."
