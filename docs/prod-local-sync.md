# Production and Local DDEV Sync

Production is image-only. It should not clone or build the full source repo.

## Backup policy

- Daily cron backup at 02:15 server time.
- Keep latest 7 timestamped backups.
- Require at least 5 GB free space before creating a backup.
- Exclude cache table data from DB backup.
- Exclude generated public file caches from file backup.
- Keep deploy files backup separately.

## Scripts

- `scripts/sync/prod-backup.sh`
  - Runs on production.
  - Creates timestamped backup in `/var/www/growbig-deploy/backups`.

- `scripts/sync/prod-backup-cron.sh`
  - Runs from cron on production.
  - Writes logs to `/var/log/growbig-backup.log`.

- `scripts/sync/prod-restore.sh`
  - Runs on production.
  - Requires `--confirm-prod-write`.

- `scripts/sync/prod-to-local.sh`
  - Runs from local Mac repo.
  - Pulls production DB/files into DDEV.

- `scripts/sync/local-to-prod.sh`
  - Runs from local Mac repo.
  - Pushes local DDEV DB/files to production.
  - Requires `--confirm-prod-write`.

## Pull production to local

    ./scripts/sync/prod-to-local.sh

Dry run:

    ./scripts/sync/prod-to-local.sh --dry-run

## Push local to production

    ./scripts/sync/local-to-prod.sh --confirm-prod-write

Dry run:

    ./scripts/sync/local-to-prod.sh --confirm-prod-write --dry-run

## Production backup

    cd /var/www/growbig-deploy
    ./scripts/prod-backup.sh

## Production restore

    cd /var/www/growbig-deploy
    ./scripts/prod-restore.sh --backup-dir /var/www/growbig-deploy/backups/YYYYMMDD-HHMMSS --confirm-prod-write
