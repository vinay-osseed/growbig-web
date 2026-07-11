# Phase 12L: Setup CLI Helper and Backup-aware Reset Planning

## Completed Scope

This phase adds developer-facing setup CLI documentation and backup-aware reset planning.

## Added

- safe setup CLI helper script
- setup CLI guide
- backup/reset planning guide

## CLI Helper

Script:

- `scripts/site-setup.sh`

Supported commands:

- status
- preview
- run
- complete
- unlock
- reset-status
- check

## Safety

The CLI helper is non-destructive.

It does not delete setup-created data.

## Backup Planning

Added planning for future reset levels:

- reset status only
- unlock setup
- delete setup-created data
- restore from backup

## Next Phase

Phase 12M should add native Drush command planning or implement a first Drush command for setup status.
