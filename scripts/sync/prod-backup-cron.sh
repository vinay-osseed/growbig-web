#!/usr/bin/env bash
set -Eeuo pipefail

DEPLOY_DIR="${DEPLOY_DIR:-/var/www/growbig-deploy}"
LOG_FILE="${LOG_FILE:-/var/log/growbig-backup.log}"

{
  echo
  echo "============================================================"
  echo "GrowBig backup started: $(date -Is)"
  echo "============================================================"

  cd "$DEPLOY_DIR"

  DEPLOY_DIR="$DEPLOY_DIR" \
  RETENTION=7 \
  MIN_FREE_MB=5120 \
  nice -n 10 ionice -c2 -n7 "$DEPLOY_DIR/scripts/prod-backup.sh"

  echo "GrowBig backup finished: $(date -Is)"
} >> "$LOG_FILE" 2>&1
