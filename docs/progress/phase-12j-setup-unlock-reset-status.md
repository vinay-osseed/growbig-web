# Phase 12J: Setup Unlock and Reset Status

## Completed Scope

This phase adds controlled setup unlock and safe status reset actions.

## Added

- `/admin/site-setup/unlock`
- `/admin/site-setup/reset-status`
- setup unlock form
- setup reset status form
- setup storage unlock/reset methods
- setup overview action buttons
- extended setup check script

## Unlock Behavior

Unlocking setup updates runtime setup state:

- completed: false
- locked: false
- current_step: setup_unlocked
- verification: pending
- completed_at: 0

Unlocking does not delete data.

## Reset Status Only Behavior

Reset Status Only resets runtime setup status back to the default starting state.

It does not delete:

- setup values
- setup manifest
- pages
- menus
- forms
- roles
- files
- submissions
- content

## Safety

This phase is intentionally non-destructive.

Content deletion and setup-created data cleanup should be designed separately with stronger confirmation and backups.

## Next Phase

Phase 12K should add setup documentation and developer/non-technical admin usage notes for the full setup workflow.
