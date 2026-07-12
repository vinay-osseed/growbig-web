#!/usr/bin/env bash
set -euo pipefail

mkdir -p /var/www/html/web/sites/default/files /var/www/html/private
chown -R www-data:www-data /var/www/html/web/sites/default/files /var/www/html/private

if [ -n "${DRUPAL_DATABASE_HOST:-}" ]; then
  echo "Waiting for database ${DRUPAL_DATABASE_HOST}:${DRUPAL_DATABASE_PORT:-3306}..."
  for i in $(seq 1 60); do
    if mysqladmin ping \
      -h"${DRUPAL_DATABASE_HOST}" \
      -P"${DRUPAL_DATABASE_PORT:-3306}" \
      -u"${DRUPAL_DATABASE_USER:-drupal}" \
      -p"${DRUPAL_DATABASE_PASSWORD:-drupal}" \
      --silent; then
      break
    fi

    if [ "$i" = "60" ]; then
      echo "Database is not ready." >&2
      exit 1
    fi

    sleep 2
  done
fi

exec "$@"
