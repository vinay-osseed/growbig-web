#!/usr/bin/env bash
set -Eeuo pipefail

mkdir -p /var/www/html/web/sites/default/files /var/www/html/private /var/www/html/tmp
chown -R www-data:www-data /var/www/html/web/sites/default/files /var/www/html/private /var/www/html/tmp 2>/dev/null || true

exec "$@"
