#!/usr/bin/env bash
set -Eeuo pipefail

BACKUP_ROOT="${BACKUP_ROOT:-/var/www/growbig-deploy/backups}"

if [ ! -d "$BACKUP_ROOT" ]; then
  echo "Backup root not found: $BACKUP_ROOT"
  exit 1
fi

echo "Backup root: $BACKUP_ROOT"
echo

latest_target=""
if [ -L "$BACKUP_ROOT/latest" ]; then
  latest_target="$(readlink -f "$BACKUP_ROOT/latest" || true)"
  echo "Latest backup:"
  echo "  $latest_target"
  echo
fi

printf "%-6s %-18s %-12s %-10s %s\n" "RANK" "BACKUP" "SIZE" "LATEST" "PATH"
printf "%-6s %-18s %-12s %-10s %s\n" "----" "------" "----" "------" "----"

rank=1

find "$BACKUP_ROOT" -mindepth 1 -maxdepth 1 -type d -name '20*' | sort -r | while read -r backup_dir; do
  name="$(basename "$backup_dir")"
  size="$(du -sh "$backup_dir" 2>/dev/null | awk '{print $1}')"
  latest=""

  if [ -n "$latest_target" ] && [ "$backup_dir" = "$latest_target" ]; then
    latest="YES"
  fi

  printf "%-6s %-18s %-12s %-10s %s\n" "$rank" "$name" "$size" "$latest" "$backup_dir"

  rank=$((rank + 1))
done

echo
echo "Restore latest DB + files:"
echo "  cd /var/www/growbig-deploy"
echo "  ./scripts/prod-restore.sh --backup-dir backups/latest --confirm-prod-write"
echo
echo "Restore specific backup:"
echo "  cd /var/www/growbig-deploy"
echo "  ./scripts/prod-restore.sh --backup-dir backups/YYYYMMDD-HHMMSS --confirm-prod-write"
