#!/usr/bin/env bash
set -euo pipefail

echo "Checking frontend API docs..."

required_paths=(
  "/api/v1/site"
  "/api/v1/pages"
  "/api/v1/pages/{slug}"
  "/api/v1/menus"
  "/api/v1/menus/{menu}"
  "/api/v1/content/{source}"
  "/api/v1/content/{source}/{key}"
  "/api/v1/forms/{form}"
  "/api/v1/forms/{form}/submit"
  "/api/v1/analytics/config"
)

for path in "${required_paths[@]}"; do
  if ! grep -Fq "$path" docs/api/openapi.yml; then
    echo "OpenAPI missing frontend path: $path" >&2
    exit 1
  fi
done

if grep -Fq "/api/v1/admin/dashboard" docs/api/openapi.yml; then
  echo "Public OpenAPI must not document admin dashboard API" >&2
  exit 1
fi

if ! grep -Fq "api.<domain>" docs/api/README.md; then
  echo "API README missing api.<domain> convention" >&2
  exit 1
fi

if ! grep -Fq "OSSeed Technologies LLP" docs/api/README.md; then
  echo "API README missing multi-site OSSeed example" >&2
  exit 1
fi

echo "Frontend API docs check passed."
