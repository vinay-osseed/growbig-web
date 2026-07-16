#!/usr/bin/env bash
set -Eeuo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/../.." && pwd)"
cd "$ROOT"

required=(
  "site-platform/web/modules/custom/site_platform_form/site_platform_form.info.yml"
  "site-platform/web/modules/custom/site_platform_form/config/install/node.type.site_form.yml"
  "site-platform/web/modules/custom/site_platform_form/config/install/node.type.site_form_field.yml"
  "site-platform/web/modules/custom/site_platform_form/config/install/node.type.site_form_submission.yml"
  "site-platform/web/modules/custom/site_platform_api/src/Controller/FormApiController.php"
  "scripts/setup/verify-form-api.php"
  "docs/implementation/phase-09-form-model-api.md"
)

for file in "${required[@]}"; do
  if [ ! -f "$file" ]; then
    echo "Missing: $file"
    exit 1
  fi
done

ddev drush en site_platform_core site_platform_site site_platform_page site_platform_menu site_platform_component site_platform_form site_platform_api -y
ddev drush cr

ddev drush config:get node.type.site_form >/dev/null
ddev drush config:get node.type.site_form_field >/dev/null
ddev drush config:get node.type.site_form_submission >/dev/null
ddev drush config:get field.field.node.site_form.field_form_key >/dev/null
ddev drush config:get field.field.node.site_form_field.field_form_field_key >/dev/null
ddev drush config:get field.field.node.site_form_submission.field_submission_payload >/dev/null

ddev drush php:script /var/www/html/scripts/setup/verify-basic-site-page-api.php
ddev drush php:script /var/www/html/scripts/setup/verify-form-api.php

form_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/forms/contact?site=growbig')"
osseed_json="$(curl -fsS 'https://site-platform.ddev.site/api/v1/forms/contact?site=osseed')"
submit_json="$(curl -fsS -X POST 'https://site-platform.ddev.site/api/v1/forms/contact/submit?site=growbig' -H 'Content-Type: application/json' --data '{"name":"Test User","email":"test@example.com","message":"Hello"}')"
invalid_json="$(curl -s -X POST 'https://site-platform.ddev.site/api/v1/forms/contact/submit?site=growbig' -H 'Content-Type: application/json' --data '{"name":"","email":"bad-email","message":""}')"
missing_json="$(curl -s 'https://site-platform.ddev.site/api/v1/forms/missing?site=growbig')"

printf '%s' "$form_json" | grep -q '"key":"contact"'
printf '%s' "$form_json" | grep -q '"type":"email"'
printf '%s' "$form_json" | grep -q '"key":"message"'
printf '%s' "$osseed_json" | grep -q '"Contact OSSeed"'
printf '%s' "$submit_json" | grep -q '"submissionId"'
printf '%s' "$submit_json" | grep -q '"status":"received"'
printf '%s' "$invalid_json" | grep -q '"code":"validation_failed"'
printf '%s' "$missing_json" | grep -q '"code":"form_not_found"'

echo "Form model and API verification passed."
