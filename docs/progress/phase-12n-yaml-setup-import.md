# Phase 12N: YAML Setup Import

## Completed Scope

This phase adds YAML-based setup value import.

## Added

- `setup/site.example.yml`
- `site-platform:setup-import`
- `sp-setup-import`
- `./scripts/site-setup.sh import setup/site.yml`
- setup storage merge support
- setup CLI documentation

## Behavior

The import command reads a YAML setup file and stores values in Drupal State API.

This keeps local, stage, and production values environment-specific.

## Safety

The import command does not create or delete content by itself.

It only saves setup runtime values.

Run setup separately with:

- `./scripts/site-setup.sh run`
- or `ddev drush site-platform:setup-run`

## Next Phase

Phase 12O should add multi-site setup row planning or setup import validation.
