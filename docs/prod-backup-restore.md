# Production Backup and Restore

Production deploy path:

    /var/www/growbig-deploy

Backup path:

    /var/www/growbig-deploy/backups

The newest backup is always:

    /var/www/growbig-deploy/backups/latest

## List backups

On production:

    cd /var/www/growbig-deploy
    ./scripts/prod-backup-list.sh

Rank 1 is the newest backup. The `LATEST` column shows the backup used by `backups/latest`.

## Backup contents

Each timestamped backup contains:

    database.sql.gz
    public-files.tgz
    private-files.tgz
    deploy-files.tgz
    manifest.txt
    SHA256SUMS.txt

`database.sql.gz` excludes cache/session/watchdog data but keeps schema.

`public-files.tgz` excludes generated public file caches:

    files/css
    files/js
    files/styles
    files/php

`deploy-files.tgz` contains:

    docker-compose.yml
    .env.prod
    settings.prod.php
    setup/site.prod.yml

## Manual backup

On production:

    cd /var/www/growbig-deploy
    ./scripts/prod-backup.sh

Dry run:

    ./scripts/prod-backup.sh --dry-run

Keep latest 7 backups:

    ./scripts/prod-backup.sh --retention 7

## Restore latest backup

On production:

    cd /var/www/growbig-deploy
    ./scripts/prod-restore.sh --backup-dir backups/latest --confirm-prod-write

## Restore a specific backup

Example:

    cd /var/www/growbig-deploy
    ./scripts/prod-restore.sh --backup-dir backups/20260712-193717 --confirm-prod-write

## Restore only database

    cd /var/www/growbig-deploy
    ./scripts/prod-restore.sh --backup-dir backups/latest --confirm-prod-write --db-only

## Restore only files

    cd /var/www/growbig-deploy
    ./scripts/prod-restore.sh --backup-dir backups/latest --confirm-prod-write --files-only

## Dry-run restore

    cd /var/www/growbig-deploy
    ./scripts/prod-restore.sh --backup-dir backups/latest --confirm-prod-write --dry-run

## Verify after restore

    cd /var/www/growbig-deploy
    docker-compose --env-file .env.prod ps
    docker-compose --env-file .env.prod exec -T site-platform bash -lc 'cd /var/www/html && vendor/bin/drush cr && vendor/bin/drush status'
    curl -I https://admin.growbigllp.com/user/login
    curl -s https://api.growbigllp.com/api/v1/site

## Pull production to local DDEV

From local Mac repo:

    ./scripts/sync/prod-to-local.sh

Dry run:

    ./scripts/sync/prod-to-local.sh --dry-run

## Push local DDEV to production

Dangerous. Use only after review:

    ./scripts/sync/local-to-prod.sh --confirm-prod-write

Dry run:

    ./scripts/sync/local-to-prod.sh --confirm-prod-write --dry-run

## Safety

- Do not commit `prod-debug/`.
- Do not commit `.env.prod`.
- Restore requires `--confirm-prod-write`.
- Cron keeps latest 7 timestamped backups.
