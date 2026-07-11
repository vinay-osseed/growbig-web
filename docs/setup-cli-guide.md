# Site Setup CLI Guide

## Purpose

The setup CLI helper gives developers a safe way to inspect and run the setup workflow from terminal.

Script:

- `./scripts/site-setup.sh`

## Commands

Show setup runtime status:

    ./scripts/site-setup.sh status

Show setup preview and readiness:

    ./scripts/site-setup.sh preview

Run setup runner:

    ./scripts/site-setup.sh run

Complete and lock setup:

    ./scripts/site-setup.sh complete

Unlock setup:

    ./scripts/site-setup.sh unlock

Reset setup status only:

    ./scripts/site-setup.sh reset-status

Run setup checks:

    ./scripts/site-setup.sh check

## Safety

The CLI helper is non-destructive.

It does not delete:

- pages
- forms
- roles
- submissions
- files
- content

## Environment-specific Values

Setup values are stored in Drupal State API.

Local, stage, and production can each have different setup values.

Do not run `drush cex` after entering local or production setup values unless those values should become shared defaults.

## Production Use

On production, prefer the UI workflow for non-technical users.

Developers may use CLI for verification:

    ./scripts/site-setup.sh status
    ./scripts/site-setup.sh preview
    ./scripts/site-setup.sh check

Before running setup on production, confirm backup availability.

## Future CLI Work

Future work may add a native Drush command, such as:

    drush site-platform:setup-status
    drush site-platform:setup-run
    drush site-platform:setup-complete
    drush site-platform:setup-reset-status

The current script is intentionally simple and safe.
