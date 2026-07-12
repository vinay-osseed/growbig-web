#!/usr/bin/env bash
set -Eeuo pipefail

REMOTE="${REMOTE:-root@45.79.121.119}"
REMOTE_DEPLOY_DIR="${REMOTE_DEPLOY_DIR:-/var/www/growbig-deploy}"
LOCAL_BACKUP_ROOT="${LOCAL_BACKUP_ROOT:-prod-debug/local-push}"
CONFIRM=0
DRY_RUN=0
SKIP_FILES=0

usage() {
  cat <<'EOF'
Usage:
  local-to-prod.sh --confirm-prod-write [--dry-run] [--skip-files]

Pushes local DDEV DB/files to production.
This is destructive for production DB.
A production pre-restore backup is created first by prod-restore.sh.

Required:
  --confirm-prod-write
EOF
}

while [ $# -gt 0 ]; do
  case "$1" in
    --confirm-prod-write) CONFIRM=1 ;;
    --dry-run) DRY_RUN=1 ;;
    --skip-files) SKIP_FILES=1 ;;
    -h|--help) usage; exit 0 ;;
    *) echo "Unknown option: $1"; usage; exit 1 ;;
  esac
  shift
done

if [ "$CONFIRM" != "1" ]; then
  echo "Refusing to push to production without --confirm-prod-write"
  exit 1
fi

run() {
  echo "+ $*"
  if [ "$DRY_RUN" = "0" ]; then
    "$@"
  fi
}

STAMP="$(date +%Y%m%d-%H%M%S)"
LOCAL_DIR="$LOCAL_BACKUP_ROOT/$STAMP"
mkdir -p "$LOCAL_DIR"

echo "Creating local DDEV DB backup without cache table data..."

if [ "$DRY_RUN" = "0" ]; then
  ddev exec -s db sh -lc '
set -eu
DB="db"
USER="db"
PASS="db"

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
' | gzip -9 > "$LOCAL_DIR/database.sql.gz"

  if [ "$SKIP_FILES" = "0" ]; then
    if [ -d site-platform/web/sites/default/files ]; then
      tar czf "$LOCAL_DIR/public-files.tgz" \
        -C site-platform/web/sites/default \
        --exclude="files/css" \
        --exclude="files/js" \
        --exclude="files/styles" \
        --exclude="files/php" \
        files
    fi

    if [ -d site-platform/private ]; then
      tar czf "$LOCAL_DIR/private-files.tgz" -C site-platform private
    fi
  fi

  sha256sum "$LOCAL_DIR"/* > "$LOCAL_DIR/SHA256SUMS.txt" 2>/dev/null || true
fi

REMOTE_TMP="$REMOTE_DEPLOY_DIR/incoming-local-$STAMP"

run ssh "$REMOTE" "mkdir -p '$REMOTE_DEPLOY_DIR/scripts' '$REMOTE_TMP'"
run scp scripts/sync/prod-backup.sh "$REMOTE:$REMOTE_DEPLOY_DIR/scripts/prod-backup.sh"
run scp scripts/sync/prod-restore.sh "$REMOTE:$REMOTE_DEPLOY_DIR/scripts/prod-restore.sh"
run ssh "$REMOTE" "chmod +x '$REMOTE_DEPLOY_DIR/scripts/prod-backup.sh' '$REMOTE_DEPLOY_DIR/scripts/prod-restore.sh'"

if [ "$DRY_RUN" = "0" ]; then
  rsync -az --progress "$LOCAL_DIR/" "$REMOTE:$REMOTE_TMP/"
  ssh "$REMOTE" "DEPLOY_DIR='$REMOTE_DEPLOY_DIR' '$REMOTE_DEPLOY_DIR/scripts/prod-restore.sh' --backup-dir '$REMOTE_TMP' --confirm-prod-write"
else
  echo "+ rsync local backup to prod and run prod-restore.sh"
fi
