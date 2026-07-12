#!/usr/bin/env bash
set -Eeuo pipefail

REMOTE="${REMOTE:-root@45.79.121.119}"
REMOTE_DEPLOY_DIR="${REMOTE_DEPLOY_DIR:-/var/www/growbig-deploy}"
LOCAL_BACKUP_ROOT="${LOCAL_BACKUP_ROOT:-prod-debug}"
DRY_RUN=0
SKIP_FILES=0

usage() {
  cat <<'EOF'
Usage:
  prod-to-local.sh [--dry-run] [--skip-files]

Pulls production into local DDEV safely:
  1. Copies prod-backup.sh to server
  2. Creates a fresh prod backup
  3. Downloads DB/files/deploy backup
  4. Imports DB into DDEV
  5. Restores files locally
  6. Runs drush cr/status

Environment:
  REMOTE=root@45.79.121.119
  REMOTE_DEPLOY_DIR=/var/www/growbig-deploy
EOF
}

while [ $# -gt 0 ]; do
  case "$1" in
    --dry-run) DRY_RUN=1 ;;
    --skip-files) SKIP_FILES=1 ;;
    -h|--help) usage; exit 0 ;;
    *) echo "Unknown option: $1"; usage; exit 1 ;;
  esac
  shift
done

run() {
  echo "+ $*"
  if [ "$DRY_RUN" = "0" ]; then
    "$@"
  fi
}

mkdir -p "$LOCAL_BACKUP_ROOT"

run ssh "$REMOTE" "mkdir -p '$REMOTE_DEPLOY_DIR/scripts' '$REMOTE_DEPLOY_DIR/backups'"
run scp scripts/sync/prod-backup.sh "$REMOTE:$REMOTE_DEPLOY_DIR/scripts/prod-backup.sh"
run ssh "$REMOTE" "chmod +x '$REMOTE_DEPLOY_DIR/scripts/prod-backup.sh'"

if [ "$DRY_RUN" = "0" ]; then
  ssh "$REMOTE" "DEPLOY_DIR='$REMOTE_DEPLOY_DIR' '$REMOTE_DEPLOY_DIR/scripts/prod-backup.sh'"
  REMOTE_BACKUP_DIR="$(ssh "$REMOTE" "readlink -f '$REMOTE_DEPLOY_DIR/backups/latest'")"
  LOCAL_DIR="$LOCAL_BACKUP_ROOT/$(basename "$REMOTE_BACKUP_DIR")"
  mkdir -p "$LOCAL_DIR"
  rsync -az --progress "$REMOTE:$REMOTE_BACKUP_DIR/" "$LOCAL_DIR/"
  ln -sfn "$(basename "$LOCAL_DIR")" "$LOCAL_BACKUP_ROOT/latest"

  ddev start
  ddev import-db --file="$LOCAL_DIR/database.sql.gz"

  if [ "$SKIP_FILES" = "0" ]; then
    if [ -f "$LOCAL_DIR/public-files.tgz" ]; then
      mkdir -p site-platform/web/sites/default
      tar xzf "$LOCAL_DIR/public-files.tgz" -C site-platform/web/sites/default
    fi

    if [ -f "$LOCAL_DIR/private-files.tgz" ]; then
      mkdir -p site-platform
      tar xzf "$LOCAL_DIR/private-files.tgz" -C site-platform
    fi
  fi

  ddev drush cr
  ddev drush status

  echo "Local sync complete from: $LOCAL_DIR"
else
  echo "+ create prod backup, rsync latest backup, ddev import-db, restore files"
fi
