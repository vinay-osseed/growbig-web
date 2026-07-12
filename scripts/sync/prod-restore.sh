#!/usr/bin/env bash
set -Eeuo pipefail

DEPLOY_DIR="${DEPLOY_DIR:-/var/www/growbig-deploy}"
ENV_FILE="${ENV_FILE:-$DEPLOY_DIR/.env.prod}"
COMPOSE_FILE="${COMPOSE_FILE:-$DEPLOY_DIR/docker-compose.yml}"
COMPOSE="${COMPOSE:-docker-compose}"

DRY_RUN=0
CONFIRM=0
DB_ONLY=0
FILES_ONLY=0
BACKUP_DIR=""

usage() {
  cat <<'EOF'
Usage:
  prod-restore.sh --backup-dir /path/to/backup --confirm-prod-write [--dry-run] [--db-only] [--files-only]

Safely restores a backup to production.
Before restore it creates a pre-restore backup unless --dry-run is used.

Required:
  --confirm-prod-write

Backup files:
  database.sql.gz
  public-files.tgz
  private-files.tgz
EOF
}

while [ $# -gt 0 ]; do
  case "$1" in
    --backup-dir) BACKUP_DIR="$2"; shift ;;
    --confirm-prod-write) CONFIRM=1 ;;
    --dry-run) DRY_RUN=1 ;;
    --db-only) DB_ONLY=1 ;;
    --files-only) FILES_ONLY=1 ;;
    -h|--help) usage; exit 0 ;;
    *) echo "Unknown option: $1"; usage; exit 1 ;;
  esac
  shift
done

if [ -z "$BACKUP_DIR" ]; then
  echo "Missing --backup-dir"
  usage
  exit 1
fi

if [ "$CONFIRM" != "1" ]; then
  echo "Refusing to restore production without --confirm-prod-write"
  exit 1
fi

if [ ! -d "$BACKUP_DIR" ]; then
  echo "Backup directory not found: $BACKUP_DIR"
  exit 1
fi

compose() {
  "$COMPOSE" --env-file "$ENV_FILE" -f "$COMPOSE_FILE" "$@"
}

run() {
  echo "+ $*"
  if [ "$DRY_RUN" = "0" ]; then
    "$@"
  fi
}

echo "Restore source: $BACKUP_DIR"

if [ "$DRY_RUN" = "0" ]; then
  echo "Creating pre-restore backup..."
  DEPLOY_DIR="$DEPLOY_DIR" ENV_FILE="$ENV_FILE" COMPOSE="$COMPOSE" "$DEPLOY_DIR/scripts/prod-backup.sh"
fi

if [ "$FILES_ONLY" = "0" ]; then
  if [ ! -f "$BACKUP_DIR/database.sql.gz" ]; then
    echo "Missing database.sql.gz"
    exit 1
  fi

  echo "Restoring database..."
  if [ "$DRY_RUN" = "0" ]; then
    compose exec -T db sh -lc '
set -eu
DB="${MARIADB_DATABASE:-${DRUPAL_DATABASE_NAME:-growbig}}"
ROOT_PASS="${MARIADB_ROOT_PASSWORD:-}"
USER="${MARIADB_USER:-${DRUPAL_DATABASE_USER:-growbig}}"
PASS="${MARIADB_PASSWORD:-${DRUPAL_DATABASE_PASSWORD:-}}"

mariadb -uroot -p"$ROOT_PASS" -e "DROP DATABASE IF EXISTS \`$DB\`; CREATE DATABASE \`$DB\` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci; GRANT ALL PRIVILEGES ON \`$DB\`.* TO '\''$USER'\''@'\''%'\'' IDENTIFIED BY '\''$PASS'\''; FLUSH PRIVILEGES;"
'
    gzip -dc "$BACKUP_DIR/database.sql.gz" | compose exec -T db sh -lc '
set -eu
DB="${MARIADB_DATABASE:-${DRUPAL_DATABASE_NAME:-growbig}}"
USER="${MARIADB_USER:-${DRUPAL_DATABASE_USER:-growbig}}"
PASS="${MARIADB_PASSWORD:-${DRUPAL_DATABASE_PASSWORD:-}}"
mariadb -u"$USER" -p"$PASS" "$DB"
'
  fi
fi

if [ "$DB_ONLY" = "0" ]; then
  echo "Restoring files..."
  if [ "$DRY_RUN" = "0" ]; then
    if [ -f "$BACKUP_DIR/public-files.tgz" ]; then
      cat "$BACKUP_DIR/public-files.tgz" | compose exec -T site-platform sh -lc '
set -eu
mkdir -p /var/www/html/web/sites/default
tar xzf - -C /var/www/html/web/sites/default
chown -R www-data:www-data /var/www/html/web/sites/default/files
'
    fi

    if [ -f "$BACKUP_DIR/private-files.tgz" ]; then
      cat "$BACKUP_DIR/private-files.tgz" | compose exec -T site-platform sh -lc '
set -eu
mkdir -p /var/www/html
tar xzf - -C /var/www/html
chown -R www-data:www-data /var/www/html/private
'
    fi
  fi
fi

if [ "$DRY_RUN" = "0" ]; then
  compose exec -T site-platform bash -lc 'cd /var/www/html && vendor/bin/drush cr || true && vendor/bin/drush status || true'
fi

echo "Restore complete."
