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

## Native Drush Commands

The setup workflow also provides native Drush commands.

Show setup status:

    ddev drush site-platform:setup-status

Show setup preview and readiness:

    ddev drush site-platform:setup-preview

Run setup:

    ddev drush site-platform:setup-run

Complete and lock setup:

    ddev drush site-platform:setup-complete

Unlock setup:

    ddev drush site-platform:setup-unlock

Reset setup status only:

    ddev drush site-platform:setup-reset-status

Short aliases:

    ddev drush sp-setup-status
    ddev drush sp-setup-preview
    ddev drush sp-setup-run
    ddev drush sp-setup-complete
    ddev drush sp-setup-unlock
    ddev drush sp-setup-reset-status

## YAML Setup Import

Create a setup file from the example:

    cp setup/site.example.yml setup/site.yml

Edit `setup/site.yml` for the current environment.

Import values:

    ./scripts/site-setup.sh import setup/site.yml

Or with Drush:

    ddev drush site-platform:setup-import setup/site.yml

The import stores values in Drupal State API. It does not export them to config.

Do not commit `setup/site.yml` if it contains production URLs, real analytics IDs, or private deployment values.

## YAML Import Validation

The import command validates setup YAML before saving values.

It checks:

- required site fields
- required contact fields
- setup mode
- URL fields
- email field
- setup option booleans
- analytics value types
- extra site row shape

Invalid YAML fails before any setup values are saved.

## Multi-site YAML Rows

The setup YAML supports optional `extra_sites` rows.

Example:

    extra_sites:
      - name: Regional Site
        key: regional
        primary_domain: regional.example.com
        frontend_url: https://regional.example.com
        admin_url: https://admin.regional.example.com
        api_url: https://api.regional.example.com

Each extra site row must include:

- `name`
- `key`
- `primary_domain`

Optional URL fields must be valid URLs when provided.

Inspect setup site rows:

    ./scripts/site-setup.sh sites

Or with Drush:

    ddev drush site-platform:setup-sites

## Private Setup Files

Only `setup/site.example.yml` should be committed.

Do not commit environment-specific files such as:

- `setup/site.yml`
- `setup/site.local.yml`
- `setup/site.stage.yml`
- `setup/site.prod.yml`
- `setup/site.production.yml`

These files can contain production URLs, analytics IDs, or environment-specific setup values.
