#!/usr/bin/env bash
set -euo pipefail

echo "Checking API docs..."

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
  "/api/v1/admin/dashboard"
)

for path in "${required_paths[@]}"; do
  if ! grep -Fq "$path" docs/api/openapi.yml; then
    echo "OpenAPI missing path: $path" >&2
    exit 1
  fi
done

if ! grep -Fq "api.<domain>" docs/api/README.md; then
  echo "API README missing api.<domain> convention" >&2
  exit 1
fi

if ! grep -Fq "admin.<domain>" docs/api/README.md; then
  echo "API README missing admin.<domain> convention" >&2
  exit 1
fi

echo "API docs check passed."
