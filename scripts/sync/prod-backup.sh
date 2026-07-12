#!/usr/bin/env bash
set -Eeuo pipefail

DEPLOY_DIR="${DEPLOY_DIR:-/var/www/growbig-deploy}"
ENV_FILE="${ENV_FILE:-$DEPLOY_DIR/.env.prod}"
COMPOSE_FILE="${COMPOSE_FILE:-$DEPLOY_DIR/docker-compose.yml}"
COMPOSE="${COMPOSE:-docker-compose}"
BACKUP_ROOT="${BACKUP_ROOT:-$DEPLOY_DIR/backups}"
RETENTION="${RETENTION:-7}"
MIN_FREE_MB="${MIN_FREE_MB:-5120}"

DRY_RUN=0
DB_ONLY=0
FILES_ONLY=0

usage() {
  cat <<'EOF'
Usage:
  prod-backup.sh [--dry-run] [--db-only] [--files-only] [--retention N] [--min-free-mb N]

Creates a timestamped production backup:
  - database.sql.gz: full schema + non-cache table data
  - public-files.tgz: Drupal public files without generated cache folders
  - private-files.tgz: Drupal private files
  - deploy-files.tgz: docker-compose.yml, .env.prod, settings.prod.php, setup YAML
  - manifest.txt and SHA256SUMS.txt

Default retention: latest 7 timestamped backups.
EOF
}

while [ $# -gt 0 ]; do
  case "$1" in
    --dry-run) DRY_RUN=1 ;;
    --db-only) DB_ONLY=1 ;;
    --files-only) FILES_ONLY=1 ;;
    --retention) RETENTION="$2"; shift ;;
    --min-free-mb) MIN_FREE_MB="$2"; shift ;;
    -h|--help) usage; exit 0 ;;
    *) echo "Unknown option: $1"; usage; exit 1 ;;
  esac
  shift
done

compose() {
  "$COMPOSE" --env-file "$ENV_FILE" -f "$COMPOSE_FILE" "$@"
}

run() {
  echo "+ $*"
  if [ "$DRY_RUN" = "0" ]; then
    "$@"
  fi
}

check_free_space() {
  free_mb="$(df -Pm "$BACKUP_ROOT" 2>/dev/null | awk 'NR==2 {print $4}')"
  if [ -z "$free_mb" ]; then
    free_mb="$(df -Pm "$DEPLOY_DIR" | awk 'NR==2 {print $4}')"
  fi

  echo "Free disk space: ${free_mb} MB"

  if [ "$free_mb" -lt "$MIN_FREE_MB" ]; then
    echo "ERROR: Free disk space is below ${MIN_FREE_MB} MB. Refusing backup."
    exit 1
  fi
}

prune_backups() {
  echo "Pruning backups, keeping latest $RETENTION timestamped backups."

  if [ ! -d "$BACKUP_ROOT" ]; then
    return 0
  fi

  find "$BACKUP_ROOT" -mindepth 1 -maxdepth 1 -type d -name '20*' | sort -r | awk -v keep="$RETENTION" 'NR > keep' | while read -r old_backup; do
    if [ -n "$old_backup" ] && [ "$old_backup" != "/" ]; then
      echo "Removing old backup: $old_backup"
      rm -rf "$old_backup"
    fi
  done
}

if [ ! -f "$ENV_FILE" ]; then
  echo "Missing env file: $ENV_FILE"
  exit 1
fi

if [ ! -f "$COMPOSE_FILE" ]; then
  echo "Missing compose file: $COMPOSE_FILE"
  exit 1
fi

mkdir -p "$BACKUP_ROOT"
check_free_space

STAMP="$(date +%Y%m%d-%H%M%S)"
BACKUP_DIR="$BACKUP_ROOT/$STAMP"

echo "Backup directory: $BACKUP_DIR"

if [ "$DRY_RUN" = "0" ]; then
  mkdir -p "$BACKUP_DIR"
fi

if [ "$FILES_ONLY" = "0" ]; then
  echo "Creating database backup without cache table data..."

  if [ "$DRY_RUN" = "0" ]; then
    compose exec -T db sh -lc '
set -eu

DB="${MARIADB_DATABASE:-${DRUPAL_DATABASE_NAME:-growbig}}"
USER="${MARIADB_USER:-${DRUPAL_DATABASE_USER:-growbig}}"
PASS="${MARIADB_PASSWORD:-${DRUPAL_DATABASE_PASSWORD:-}}"

IGNORE_ARGS=""
TABLES="$(mariadb -u"$USER" -p"$PASS" -N -B "$DB" -e "SHOW TABLES")"

for table in $TABLES; do
  case "$table" in
    cache|cache_*|sessions|watchdog|batch|queue|flood|semaphore|history)
      IGNORE_ARGS="$IGNORE_ARGS --ignore-table=$DB.$table"
      ;;
  esac
done

mariadb-dump --single-transaction --routines --triggers --events --no-data -u"$USER" -p"$PASS" "$DB"

# shellcheck disable=SC2086
mariadb-dump --single-transaction --quick --no-create-info --routines --triggers --events -u"$USER" -p"$PASS" $IGNORE_ARGS "$DB"
' | gzip -9 > "$BACKUP_DIR/database.sql.gz"
  else
    echo "+ compose exec db mariadb-dump schema + non-cache data > database.sql.gz"
  fi
fi

if [ "$DB_ONLY" = "0" ]; then
  echo "Creating files backup without generated public cache folders..."

  if [ "$DRY_RUN" = "0" ]; then
    compose exec -T site-platform sh -lc '
set -eu
if [ -d /var/www/html/web/sites/default/files ]; then
  tar czf - \
    -C /var/www/html/web/sites/default \
    --exclude="files/css" \
    --exclude="files/js" \
    --exclude="files/styles" \
    --exclude="files/php" \
    files
else
  tar czf - --files-from /dev/null
fi
' > "$BACKUP_DIR/public-files.tgz"

    compose exec -T site-platform sh -lc '
set -eu
if [ -d /var/www/html/private ]; then
  tar czf - -C /var/www/html private
else
  tar czf - --files-from /dev/null
fi
' > "$BACKUP_DIR/private-files.tgz"

    tar czf "$BACKUP_DIR/deploy-files.tgz" \
      -C "$DEPLOY_DIR" \
      docker-compose.yml \
      .env.prod \
      settings.prod.php \
      setup/site.prod.yml 2>/dev/null || true
  else
    echo "+ backup public files, private files, deploy files"
  fi
fi

if [ "$DRY_RUN" = "0" ]; then
  {
    echo "timestamp=$STAMP"
    echo "deploy_dir=$DEPLOY_DIR"
    echo "env_file=$ENV_FILE"
    echo "compose_file=$COMPOSE_FILE"
    echo "retention=$RETENTION"
    echo "min_free_mb=$MIN_FREE_MB"
    echo
    echo "compose ps:"
    compose ps || true
    echo
    echo "images:"
    compose images || true
  } > "$BACKUP_DIR/manifest.txt"

  (cd "$BACKUP_DIR" && sha256sum * > SHA256SUMS.txt 2>/dev/null || true)

  ln -sfn "$BACKUP_DIR" "$BACKUP_ROOT/latest"

  prune_backups
fi

echo "BACKUP_DIR=$BACKUP_DIR"
