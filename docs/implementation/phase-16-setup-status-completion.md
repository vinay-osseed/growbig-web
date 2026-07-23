# Phase 16: Setup Status and Completion Tracking

## Goal

Add setup completion and lock-state tracking commands.

## New Commands

    site-platform:setup-complete
    site-platform:setup-unlock
    site-platform:setup-reset-status

## Updated Command

    site-platform:setup-status

The status command now shows:

- last setup runner status
- last YAML import
- last site reset
- completion status
- setup locked state
- current setup-managed entity counts

## Complete

This command marks setup as complete and locked:

    ddev drush site-platform:setup-complete

If setup is already locked, run with force only when intentionally completing again:

    ddev drush site-platform:setup-complete --force

## Unlock

This command unlocks setup tracking only:

    ddev drush site-platform:setup-unlock

It does not delete records.

## Reset Status

This command resets setup tracking state only:

    ddev drush site-platform:setup-reset-status

It does not delete records.

## Safety

No command in this phase deletes site data.

Data deletion remains limited to explicit reset commands:

    ddev drush site-platform:setup-reset-demo --execute
    ddev drush site-platform:setup-reset-site SITE_KEY --execute
