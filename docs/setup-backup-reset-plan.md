# Setup Backup and Reset Plan

## Purpose

This document defines how setup reset and backup-aware recovery should work.

Current setup reset is intentionally safe and non-destructive.

Current reset action:

- Reset setup status only

It does not delete:

- pages
- menus
- forms
- roles
- files
- submissions
- content
- setup values
- setup manifest

## Why Backup-aware Reset Is Needed

Future setup phases may add destructive cleanup, such as deleting setup-created pages, forms, or demo content.

Before any destructive cleanup exists, the workflow must support backup checks and strong confirmation.

## Reset Levels

### Level 1: Reset Status Only

Current implemented behavior.

Use when:

- setup failed
- setup needs to be restarted
- setup should be re-run without removing created data

This only resets runtime setup status.

It does not delete created data.

### Level 2: Unlock Setup

Current implemented behavior.

Use when:

- setup was completed and locked
- admin needs to adjust setup values
- setup runner needs to be re-run

Unlocking does not delete created data.

### Level 3: Delete Setup-created Data

Future behavior.

This should delete only records tracked in the setup manifest.

Before deleting, the UI should show:

- setup ID
- node IDs
- webform IDs
- role IDs
- file IDs or paths
- config names
- confirmation checkbox

This should not delete user-created data that is not tracked in the setup manifest.

### Level 4: Restore From Backup

Future behavior.

Use when:

- setup changed too much data
- setup-created data cleanup is not enough
- production safety requires full rollback

The system should require a backup confirmation before destructive setup actions.

## Production Backup Rule

Before production setup run, confirm:

- database backup exists
- config backup exists
- files backup exists if setup handles files
- rollback process is known

## Future Backup Check Ideas

Future implementation may include:

- manual backup confirmation checkbox
- last backup timestamp field
- backup command documentation
- environment-specific backup provider notes
- backup required before destructive reset

## Current Safe Recommendation

For now, use only:

- `/admin/site-setup/unlock`
- `/admin/site-setup/reset-status`

Do not implement destructive cleanup until backup checks and manifest review are added.
