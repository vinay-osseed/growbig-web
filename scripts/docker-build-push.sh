#!/usr/bin/env bash
set -euo pipefail

ENV_FILE="${1:-.env.prod}"

if [ ! -f "$ENV_FILE" ]; then
  echo "Missing env file: $ENV_FILE" >&2
  echo "Copy .env.prod.example to .env.prod and update values first." >&2
  exit 1
fi

docker compose --env-file "$ENV_FILE" -f docker-compose.prod.yml build site-platform site-frontend
docker compose --env-file "$ENV_FILE" -f docker-compose.prod.yml push site-platform site-frontend
